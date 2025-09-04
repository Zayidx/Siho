<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservations;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationsExportController extends Controller
{
    public function csv(): StreamedResponse
    {
        $filename = 'reservations_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id','guest_name','guest_email','check_in','check_out','status','rooms','created_at']);
            $q = Reservations::with(['guest','rooms'])->orderBy('id');

            if ($s = request('search')) {
                $term = '%'.$s.'%';
                $q->whereHas('guest', function ($g) use ($term) {
                    $g->where('full_name', 'like', $term)
                      ->orWhere('email', 'like', $term);
                });
            }
            if ($start = request('start')) {
                $q->whereDate('check_in_date', '>=', $start);
            }
            if ($end = request('end')) {
                $q->whereDate('check_out_date', '<=', $end);
            }
            if ($status = request('status')) {
                $q->where('status', $status);
            }

            $safe = static function ($v) {
                if (is_null($v)) return '';
                $s = (string) $v;
                $s = str_replace(["\r","\n"], ' ', $s);
                if ($s !== '' && in_array($s[0], ['=', '+', '-', '@'])) {
                    return "'".$s;
                }
                return $s;
            };

            $q->chunk(300, function ($chunk) use ($handle, $safe) {
                foreach ($chunk as $r) {
                    $roomList = $r->rooms->pluck('room_number')->map($safe)->join(' | ');
                    fputcsv($handle, [
                        $r->id,
                        $safe(optional($r->guest)->full_name ?? ''),
                        $safe(optional($r->guest)->email ?? ''),
                        $r->check_in_date,
                        $r->check_out_date,
                        $safe($r->status),
                        $roomList,
                        $r->created_at->toDateTimeString(),
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
