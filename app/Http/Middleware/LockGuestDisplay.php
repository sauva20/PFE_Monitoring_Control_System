<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LockGuestDisplay
{
    /**
     * Handle an incoming request.
     * Restrict guest display viewers from accessing internal application routes.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Logged-in internal users (e.g. EHSS admin) are never locked
        if (Auth::check()) {
            return $next($request);
        }

        if ($request->hasSession()) {
            $token = session('guest_display_token');

            if ($token) {
                // Allowed paths for guest display viewers
                if (
                    $request->is('display-report/access/'.$token.'*') ||
                    $request->is('display-report/view/'.$token.'*') ||
                    $request->is('display-report/logout/'.$token.'*') ||
                    $request->is('locale/*') ||
                    $request->is('favicon.ico') ||
                    $request->is('up')
                ) {
                    return $next($request);
                }

                // Any other route attempt (such as /dashboard, /, /login, /users, etc.)
                // is locked and redirected back to the guest display view or PIN verification
                if (session("guest_display_auth_{$token}")) {
                    return redirect()->route('display.access.view', ['token' => $token]);
                }

                return redirect()->route('display.access', ['token' => $token]);
            }
        }

        return $next($request);
    }
}
