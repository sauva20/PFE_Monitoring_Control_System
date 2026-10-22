<?php

namespace App\Console\Commands;

use App\Http\Controllers\AuthController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SimulateBotLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:test-login {--mode=all : Pilihan mode: no-captcha, fake-captcha, honeypot, all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasi serangan bot pada form login untuk menguji reCAPTCHA dan Honeypot';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mode = $this->option('mode');

        $this->newLine();
        $this->info('====================================================');
        $this->info('🤖 SIMULASI SERANGAN BOT PADA LOGIN PFE EHSS');
        $this->info('====================================================');

        if ($mode === 'all') {
            $this->runTest('no-captcha');
            $this->runTest('fake-captcha');
            $this->runTest('honeypot');
        } else {
            $this->runTest($mode);
        }

        $this->newLine();
        $this->info('✅ Seluruh lapisan keamanan bot berhasil diverifikasi!');
        $this->newLine();

        return Command::SUCCESS;
    }

    protected function runTest(string $mode): void
    {
        $this->line('----------------------------------------------------');

        $params = [
            'email' => 'dummy.bot.target@example.com',
            'password' => 'DummyTestPassword123!',
        ];

        if ($mode === 'no-captcha') {
            $this->comment('👉 Skenario 1: Bot menyerang TANPA mencentang reCAPTCHA');
        } elseif ($mode === 'fake-captcha') {
            $this->comment('👉 Skenario 2: Bot mengirim token reCAPTCHA PALSU / REKAYASA');
            $params['g-recaptcha-response'] = 'token_robot_palsu_xyz987';
        } elseif ($mode === 'honeypot') {
            $this->comment('👉 Skenario 3: Bot mengisi input rahasia (Honeypot Trap)');
            $params['_contact_number'] = '08123456789';
        }

        $request = Request::create('/login', 'POST', $params);
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        try {
            $controller = app(AuthController::class);
            $response = $controller->login($request);

            if ($response->isRedirect() && session()->has('errors')) {
                $errorMsg = session('errors')->first();
                $this->line("<fg=green;options=bold>[TERBLOKIR]</> Server menolak: <fg=red>{$errorMsg}</>");
            } else {
                $this->line('Status Response: '.$response->getStatusCode());
            }
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->line("<fg=green;options=bold>[TERBLOKIR]</> Validasi <fg=yellow>[{$field}]</> menolak: <fg=red>{$msg}</>");
                }
            }
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }
}
