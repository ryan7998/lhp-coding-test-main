<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('city')->nullable()->after('longitude');
            $table->string('country')->nullable()->after('city');
            $table->string('country_code', 2)->nullable()->after('country');
            $table->string('timezone')->nullable()->after('country_code');

            $table->index(['status', 'created_time']);
            $table->index(['country_code', 'city']);
            $table->index(['type', 'created_time']);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_time']);
            $table->dropIndex(['country_code', 'city']);
            $table->dropIndex(['type', 'created_time']);
            $table->dropColumn(['city', 'country', 'country_code', 'timezone']);
        });
    }
};
