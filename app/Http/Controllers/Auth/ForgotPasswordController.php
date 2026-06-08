<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\MailDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class ForgotPasswordController extends Controller
{
    private const SUCCESS_MESSAGE = 'If an account exists for that email, you will receive password reset instructions shortly.';

    public function show(): View
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        if (! MailDelivery::deliversToInbox()) {
            return back()
                ->withInput()
                ->with('error', MailDelivery::configurationMessage());
        }

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'We could not send the reset email right now. Please try again in a few minutes or contact your clinic administrator.');
        }

        if ($status === Password::RESET_LINK_SENT || $status === Password::INVALID_USER) {
            return back()->with('success', self::SUCCESS_MESSAGE);
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput()
                ->withErrors(['email' => __($status)]);
        }

        return back()
            ->withInput()
            ->withErrors(['email' => __($status)]);
    }
}
