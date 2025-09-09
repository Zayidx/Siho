<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function verifyNew(Request $request)
    {
        if (! URL::hasValidSignature($request)) {
            abort(403, 'Invalid or expired verification link.');
        }

        $userId = $request->query('user');
        $email = $request->query('email');
        $user = User::findOrFail($userId);

        if (! $user->pending_email || strtolower($user->pending_email) !== strtolower($email)) {
            abort(403, 'Verification data mismatch.');
        }

        $user->email = $user->pending_email;
        $user->pending_email = null;
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('user.profile')->with('success', 'Email berhasil diverifikasi.');
    }

    public function verifyCurrent(Request $request)
    {
        if (! URL::hasValidSignature($request)) {
            abort(403, 'Invalid or expired verification link.');
        }
        $user = $request->user();
        $email = $request->query('email');
        if (strtolower($user->email) !== strtolower($email)) {
            abort(403, 'Verification data mismatch.');
        }
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('user.profile')->with('success', 'Email berhasil diverifikasi.');
    }

    public function resend(Request $request)
    {
        $user = $request->user();
        if ($user->pending_email) {
            $verifyUrl = URL::temporarySignedRoute('verification.new', now()->addMinutes(60), [
                'user' => $user->id,
                'email' => $user->pending_email,
            ]);
            \Mail::to($user->pending_email)->queue(new \App\Mail\VerifyNewEmailMail($verifyUrl, $user->full_name ?? $user->username));
        } elseif (! $user->email_verified_at) {
            $verifyUrl = URL::temporarySignedRoute('verification.current', now()->addMinutes(60), [
                'email' => $user->email,
            ]);
            \Mail::to($user->email)->queue(new \App\Mail\VerifyEmailMail($verifyUrl, $user->full_name ?? $user->username));
        }

        return back()->with('success', 'Email verifikasi dikirim. Periksa inbox Anda.');
    }
}
