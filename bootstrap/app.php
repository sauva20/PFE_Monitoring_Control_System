<?php

use App\Http\Middleware\LockGuestDisplay;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RestrictPinLogin;
use App\Http\Middleware\SetLocale;
use App\Models\Apar;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            LockGuestDisplay::class,
            PreventBackHistory::class,
        ]);
        $middleware->alias([
            'prevent-back-history' => PreventBackHistory::class,
            'restrict-pin-login' => RestrictPinLogin::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->hasSession() && ($token = session('guest_display_token'))) {
                if (session("guest_display_auth_{$token}")) {
                    return route('display.access.view', ['token' => $token]);
                }

                return route('display.access', ['token' => $token]);
            }

            if ($request->is('inspeksi/*')) {
                session()->flash('error', 'Sesi inspeksi Anda telah berakhir atau tidak valid. Silakan scan ulang QR Code atau masukkan PIN kembali.');

                $aparId = $request->segment(2);
                if ($aparId && is_numeric($aparId)) {
                    $apar = Apar::find($aparId);
                    if ($apar) {
                        return route('scan.apar', $apar->kode);
                    }
                }

                return url()->previous() ?? '/';
            }

            session()->flash('auth_error', 'Anda harus login untuk mengakses sistem ini.');

            return route('login');
        });
        $middleware->validateCsrfTokens(except: [
            'logout',
            'display-report/access/*/verify',
            'display-report/logout/*',
        ]);
        $middleware->redirectUsersTo(function (Request $request) {
            return '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $handleExpired = function (Request $request) {
            if ($request->is('logout') || $request->routeIs('logout')) {
                Auth::logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return redirect('/')->with('info', __('Anda telah berhasil keluar dari sistem.'));
            }
            if ($request->is('login') || $request->is('/') || $request->routeIs('login')) {
                if ($request->hasSession()) {
                    $request->session()->regenerateToken();
                }

                return redirect('/')->with('error', __('Sesi form login Anda telah disegarkan. Silakan masukkan kembali akun Anda.'));
            }
            if ($request->is('scan/*/verify') || $request->routeIs('scan.verify')) {
                return redirect()->back()->with('error', __('Sesi verifikasi telah kedaluwarsa. Silakan masukkan PIN kembali.'));
            }
            if ($request->is('display-report/access/*/verify') || $request->routeIs('display.access.verify')) {
                $token = $request->route('token') ?? $request->segment(3);
                if ($token) {
                    return redirect()->route('display.access', $token)->with('error', __('Sesi verifikasi PIN telah disegarkan. Silakan masukkan PIN kembali.'));
                }
            }
            if ($request->is('inspeksi/store/*') || $request->routeIs('inspeksi.store')) {
                return redirect()->back()->with('error', __('Sesi form telah kedaluwarsa. Silakan simpan ulang.'));
            }

            return redirect()->back()->with('error', __('Sesi Anda telah kedaluwarsa. Silakan coba kembali.'));
        };

        $exceptions->render(function (HttpException $e, Request $request) use ($handleExpired) {
            if ($e->getStatusCode() === 419) {
                return $handleExpired($request);
            }
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) use ($handleExpired) {
            return $handleExpired($request);
        });
    })->create();
