<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bills;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function download(Bills $bill)
    {
        // Authorize: current user must own the reservation
        abort_unless($bill->reservation && $bill->reservation->guest_id === Auth::id(), 403);

        $html = view('pdf.invoice', [
            'bill' => $bill->load(['reservation.rooms', 'reservation.guest']),
        ])->render();

        $pdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $filename = 'invoice-'.$bill->id.'.pdf';
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function proof(Bills $bill)
    {
        abort_unless($bill->reservation && $bill->reservation->guest_id === Auth::id(), 403);
        if (!$bill->payment_proof_path || !Storage::disk('public')->exists($bill->payment_proof_path)) {
            abort(404);
        }
        // Stream file with correct content type
        return Storage::disk('public')->response($bill->payment_proof_path);
    }
}
