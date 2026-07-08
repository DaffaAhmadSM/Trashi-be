<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->date('scheduled_date')->nullable()->after('status');
            $table->string('time_slot')->nullable()->after('scheduled_date');
            $table->string('payment_status')->default('pending')->after('time_slot');
            $table->text('payment_proof')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['scheduled_date', 'time_slot', 'payment_status', 'payment_proof']);
        });
    }
};
