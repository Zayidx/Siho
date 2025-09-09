<?php

namespace App\Actions\Fnb;

use App\Models\FnbOrder;
use App\Models\FnbOrderItem;
use Illuminate\Support\Facades\DB;

class CreateFnbOrder
{
    /**
     * @param  array  $cart  array of [id,name,price,qty]
     * @param  array  $meta  ['service_type','notes','room_number']
     */
    public function handle(int $userId, array $cart, array $meta = []): FnbOrder
    {
        return DB::transaction(function () use ($userId, $cart, $meta) {
            // Always compute total and unit prices from DB for integrity
            $ids = collect($cart)->pluck('id')->map(fn ($v) => (int) $v)->unique()->values();
            $items = \App\Models\MenuItem::whereIn('id', $ids)->get()->keyBy('id');
            $total = 0;
            $service = $meta['service_type'] ?? FnbOrder::SERVICE_IN_ROOM;
            if (! in_array($service, FnbOrder::ALLOWED_SERVICE_TYPES, true)) {
                $service = FnbOrder::SERVICE_IN_ROOM;
            }

            $order = FnbOrder::create([
                'user_id' => $userId,
                'status' => FnbOrder::STATUS_PENDING,
                'payment_status' => FnbOrder::PAYMENT_UNPAID,
                'service_type' => $service,
                'total_amount' => $total,
                'notes' => $meta['notes'] ?? null,
                'room_number' => $meta['room_number'] ?? null,
            ]);

            foreach ($cart as $row) {
                $id = (int) ($row['id'] ?? 0);
                $qty = (int) ($row['qty'] ?? 0);
                $menu = $items->get($id);
                if (! $menu || $qty <= 0) {
                    continue; // skip invalid/missing items or non-positive qty
                }
                $unit = (int) $menu->price;
                $line = $qty * $unit;
                $total += $line;
                FnbOrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $id,
                    'qty' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $line,
                ]);
            }

            // Update total_amount with computed sum
            $order->update(['total_amount' => $total]);

            return $order;
        });
    }
}
