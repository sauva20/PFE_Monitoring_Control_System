<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bersihkan user selain ID 1 dan reset data user ID 1
        User::where('id', '!=', 1)->delete();
        $u = User::find(1);
        if ($u) {
            $u->role = 'Head';
            $u->pin = '123456';
            $u->save();
        }

        // 2. Tambahkan unique ke kolom pin
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['pin']);
        });
    }
};
