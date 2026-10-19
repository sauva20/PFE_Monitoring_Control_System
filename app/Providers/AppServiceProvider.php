<?php

namespace App\Providers;

use App\Models\Apar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            $notifications = collect();

            if (Auth::check()) {
                $user = Auth::user();
                $targetDate = Carbon::today()->addDays(30);

                $query = Apar::with(['lokasi.gedung'])
                    ->whereDate('tgl_kedaluwarsa', '<=', $targetDate);

                if ($user->role === 'EHSS') {
                    $notifications = $query->orderBy('updated_at', 'desc')->get();
                } elseif ($user->role === 'Staff') {
                    $assignedGedungIds = $user->gedungs()->pluck('gedung.id')->toArray();

                    if (empty($assignedGedungIds)) {
                        // Sometimes pluck('gedung.id') doesn't work if table names are weird, let's use the safer route:
                        $assignedGedungIds = $user->gedungs->pluck('id')->toArray();
                    }

                    if (! empty($assignedGedungIds)) {
                        $notifications = $query->whereHas('lokasi', function ($q) use ($assignedGedungIds) {
                            $q->whereIn('gedung_id', $assignedGedungIds);
                        })->orderBy('updated_at', 'desc')->get();
                    }
                }
            }

            $view->with('importantNotifications', $notifications);
        });
    }
}
