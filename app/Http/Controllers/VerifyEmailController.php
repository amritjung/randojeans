<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request)
    {
        // EmailVerificationRequest automatically validates the signed URL's
        // signature and expiry before this code even runs — if the link is
        // invalid, tampered with, or expired, Laravel rejects it before
        // reaching here, so we don't need to check that ourselves.
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }

        $request->fulfill(); // marks email_verified_at as now()

        return redirect()->route('account')->with('verified', true);
    }
}