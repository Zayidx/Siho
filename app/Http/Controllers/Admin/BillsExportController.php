<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bills;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillsExportController extends Controller
{
    public function csv(): StreamedResponse
    {
        $filename = 'bills_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id','reservation_id','guest_email','total_amount','payment_method','payment_review_status','paid_at','proof_uploaded_at','created_at']);
            $q = Bills::with(['reservation.guest'])->orderBy('id');

            if ($status = request('status')) {
                $q->where('payment_review_status', $status);
            }
            if ($s = request('search')) {
                $term = '%'.$s.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('payment_method', 'like', $term)
                       ->orWhere('notes', 'like', $term);
                });
            }
            if ($start = request('start')) {
                $q->whereDate('payment_proof_uploaded_at', '>=', $start);
            }
            if ($end = request('end')) {
                $q->whereDate('payment_proof_uploaded_at', '<=', $end);
            }

            $q->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $b) {
                    fputcsv($handle, [
                        $b->id,
                        $b->reservation_id,
                        optional($b->reservation->guest)->email,
                        $b->total_amount,
                        $b->payment_method,
                        $b->payment_review_status,
                        optional($b->paid_at)->toDateTimeString(),
                        optional($b->payment_proof_uploaded_at)->toDateTimeString(),
                        $b->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

