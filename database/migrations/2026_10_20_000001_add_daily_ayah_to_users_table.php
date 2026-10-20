<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('daily_ayah_enabled')->default(false);
            $table->string('daily_ayah_time', 5)->default('07:00');
            $table->boolean('daily_ayah_arabic')->default(true);
            $table->boolean('daily_ayah_translation')->default(true);
            $table->boolean('daily_ayah_reference')->default(true);
            $table->boolean('daily_ayah_simple')->default(false);
            $table->string('timezone', 64)->nullable();
            $table->date('daily_ayah_last_sent_on')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'daily_ayah_enabled', 'daily_ayah_time', 'daily_ayah_arabic', 'daily_ayah_translation',
                'daily_ayah_reference', 'daily_ayah_simple', 'timezone', 'daily_ayah_last_sent_on',
            ]);
        });
    }
};
