<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;

class Recaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (empty($secretKey)) {
            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(7)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            if (! $response->successful() || ! $response->json('success')) {
                $fail(__('Verifikasi reCAPTCHA tidak valid. Silakan coba lagi.'));
            }
        } catch (\Throwable $e) {
            $fail(__('Gagal terhubung ke server verifikasi reCAPTCHA. Pastikan perangkat terhubung ke internet.'));
        }
    }
}
