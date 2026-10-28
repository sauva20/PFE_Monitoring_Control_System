<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, LogsActivity, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'pin',
        'role',
        'jadwal_rutin_tanggal',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $nama = $this->name ?? 'User';
                $role = $this->role ?? 'User';

                return match ($eventName) {
                    'created' => "Menambahkan user baru: {$nama} ({$role})",
                    'updated' => "Memperbarui data akun user: {$nama} ({$role})",
                    'deleted' => "Menghapus akun user: {$nama}",
                    default => "User {$nama} telah di-{$eventName}",
                };
            });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function gedungs()
    {
        return $this->belongsToMany(Gedung::class);
    }

    public function lokasis()
    {
        return $this->belongsToMany(Lokasi::class);
    }

    public static function verifyPin(string $inputPin, ?string $storedPin): bool
    {
        if (empty($storedPin)) {
            return false;
        }

        if ((string) $inputPin === (string) $storedPin) {
            return true;
        }

        if (str_starts_with($storedPin, '$2y$') || str_starts_with($storedPin, '$2a$') || str_starts_with($storedPin, '$2b$')) {
            try {
                return Hash::check($inputPin, $storedPin);
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    public function matchesPin(string $inputPin): bool
    {
        return static::verifyPin($inputPin, $this->pin);
    }
}
