<?php

use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RestrictPinLogin;
use App\Http\Middleware\SetLocale;
use App\Models\Apar;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);
        $middleware->alias([
            'prevent-back-history' => PreventBackHistory::class,
            'restrict-pin-login' => RestrictPinLogin::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(function (Request $request) {
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
        $middleware->redirectUsersTo(function (Request $request) {
            return '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->is('scan/*/verify') || $request->routeIs('scan.verify')) {
                return redirect()->back()->with('error', 'Sesi verifikasi telah kedaluwarsa. Silakan masukkan PIN kembali.');
            }
            if ($request->is('inspeksi/store/*') || $request->routeIs('inspeksi.store')) {
                return redirect()->back()->with('error', 'Sesi form telah kedaluwarsa. Silakan simpan ulang.');
            }

            return redirect()->back()->with('error', 'Sesi Anda telah kedaluwarsa. Halaman telah disegarkan, silakan coba kembali.');
        });
    })->create();
