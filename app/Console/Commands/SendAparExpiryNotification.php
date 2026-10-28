<?php

namespace App\Console\Commands;

use App\Mail\AparExpiryReminder;
use App\Models\Apar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAparExpiryNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-apar-expiry-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends H-30 expiration reminder for APAR to EHSS and PIC';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = Carbon::today()->addDays(30);
        $formattedDate = $targetDate->locale('id')->translatedFormat('d F Y');

        $apars = Apar::whereDate('tgl_kedaluwarsa', $targetDate)->with(['lokasi.gedung', 'jenis'])->get();

        if ($apars->isEmpty()) {
            $this->info('No APAR expiring in 30 days.');

            return;
        }

        $ehssUsers = User::where('role', 'EHSS')->get();
        $ehssEmails = $ehssUsers->pluck('email')->toArray();

        // Send one master email to all EHSS users
        if (! empty($ehssEmails)) {
            Mail::to($ehssEmails)->send(new AparExpiryReminder($apars, $formattedDate, 'Tim EHSS'));
            $this->info('Sent master expiration reminder to EHSS users.');
        }

        // Send to PIC (Staff) based on Gedung / Lokasi assignments
        $staffUsers = User::where('role', 'Staff')->with(['gedungs', 'lokasis'])->get();

        foreach ($staffUsers as $staff) {
            $assignedGedungIds = $staff->gedungs->pluck('id')->toArray();
            $assignedLokasiIds = $staff->lokasis->pluck('id')->toArray();

            if (empty($assignedGedungIds) && empty($assignedLokasiIds)) {
                continue;
            }

            // Filter APARs that belong to this staff's assigned gedungs or lokasis
            $staffApars = $apars->filter(function ($apar) use ($assignedGedungIds, $assignedLokasiIds) {
                if ($apar->lokasi_id && in_array($apar->lokasi_id, $assignedLokasiIds)) {
                    return true;
                }
                if ($apar->lokasi && $apar->lokasi->gedung_id) {
                    return in_array($apar->lokasi->gedung_id, $assignedGedungIds);
                }

                return false;
            });

            if ($staffApars->isNotEmpty() && ! in_array($staff->email, $ehssEmails)) {
                Mail::to($staff->email)->send(new AparExpiryReminder($staffApars, $formattedDate, $staff->name));
                $this->info("Sent expiration reminder to PIC: {$staff->email} for ".$staffApars->count().' APARs.');
            }
        }
    }
}
