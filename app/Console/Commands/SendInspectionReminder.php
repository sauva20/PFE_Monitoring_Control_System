<?php

namespace App\Console\Commands;

use App\Mail\InspectionReminder;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInspectionReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email pengingat inspeksi APAR (H-3 Rutin & H-1 Khusus)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan jadwal inspeksi...');

        $today = now();
        $targetRutinDate = $today->copy()->addDays(3);
        $targetRutinDay = $targetRutinDate->day;
        $isLastDayOfMonth = $targetRutinDate->isLastOfMonth();

        // 1. Cek Jadwal Rutin (H-3)
        // Jika target H-3 jatuh pada hari terakhir bulan (misal 28 Februari atau 30 April),
        // maka staf dengan tanggal rutin >= tanggal tersebut (29, 30, 31) otomatis diikutsertakan.
        $usersRutinQuery = User::where('role', 'Staff')
            ->whereNotNull('jadwal_rutin_tanggal');

        if ($isLastDayOfMonth) {
            $usersRutinQuery->where('jadwal_rutin_tanggal', '>=', $targetRutinDay);
        } else {
            $usersRutinQuery->where('jadwal_rutin_tanggal', $targetRutinDay);
        }

        $usersRutin = $usersRutinQuery->with(['gedungs', 'lokasis.gedung'])->get();

        foreach ($usersRutin as $user) {
            $effectiveDeadlineDay = min($user->jadwal_rutin_tanggal, $targetRutinDate->daysInMonth);
            $effectiveDeadlineDate = $targetRutinDate->copy()->day($effectiveDeadlineDay)->format('Y-m-d');

            $this->info("Mengirim pengingat rutin ke: {$user->email} (Deadline: {$effectiveDeadlineDate})");
            Mail::to($user->email)->send(new InspectionReminder($user, 'Inspeksi Rutin Bulanan', $effectiveDeadlineDate));
        }

        $this->info('Selesai mengirim pengingat!');
    }
}
