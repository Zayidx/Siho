<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rooms;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoomsExportController extends Controller
{
    public function csv(): StreamedResponse
    {
        $filename = 'rooms_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id','room_number','room_type','status','floor','price_per_night','created_at']);
            $q = Rooms::with('roomType')->orderBy('id');
            if ($s = request('search')) {
                $term = '%'.$s.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('room_number','like',$term)
                       ->orWhere('status','like',$term)
                       ->orWhere('description','like',$term);
                });
            }
            if ($status = request('status')) {
                $q->where('status', $status);
            }
            $q->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $r) {
                    fputcsv($handle, [
                        $r->id,
                        $r->room_number,
                        optional($r->roomType)->name,
                        $r->status,
                        $r->floor,
                        $r->price_per_night,
                        $r->created_at->toDateTimeString(),
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

