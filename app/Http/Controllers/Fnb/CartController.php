<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $data = $request->validate([
            'menu_item_id' => 'required|integer|exists:menu_items,id',
            'qty' => 'nullable|integer|min:1',
        ], [
            'menu_item_id.required' => 'Item menu wajib dipilih.',
            'menu_item_id.integer' => 'Item menu tidak valid.',
            'menu_item_id.exists' => 'Item menu tidak ditemukan.',
            'qty.integer' => 'Jumlah harus berupa angka.',
            'qty.min' => 'Jumlah minimal 1.',
        ], [
            'menu_item_id' => 'Item menu',
            'qty' => 'Jumlah',
        ]);

        $qty = (int) ($data['qty'] ?? 1);
        $id = (int) $data['menu_item_id'];

        $cart = session()->get('fnb_cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + $qty;
        session()->put('fnb_cart', $cart);

        // Compute totals for UI badges/counters
        $totalQty = array_sum($cart);
        $itemsCount = count($cart);

        return response()->json([
            'ok' => true,
            'qty' => $totalQty,
            'items' => $itemsCount,
        ]);
    }
}
