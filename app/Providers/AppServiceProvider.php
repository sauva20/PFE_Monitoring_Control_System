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
                    $assignedGedungIds = $user->gedungs->pluck('id')->toArray();
                    $assignedLokasiIds = $user->lokasis->pluck('id')->toArray();

                    if (! empty($assignedGedungIds) || ! empty($assignedLokasiIds)) {
                        $notifications = $query->where(function ($sub) use ($assignedGedungIds, $assignedLokasiIds) {
                            if (! empty($assignedGedungIds)) {
                                $sub->whereHas('lokasi', fn ($q) => $q->whereIn('gedung_id', $assignedGedungIds));
                            }
                            if (! empty($assignedLokasiIds)) {
                                $sub->orWhereIn('lokasi_id', $assignedLokasiIds);
                            }
                        })->orderBy('updated_at', 'desc')->get();
                    }
                }
            }

            $view->with('importantNotifications', $notifications);
        });
    }
}
