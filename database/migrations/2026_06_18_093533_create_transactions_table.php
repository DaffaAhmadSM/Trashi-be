<?php

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
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('trans_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('address_id')->constrained('addresses', 'address_id')->cascadeOnDelete();
            $table->dateTime('date');
            $table->decimal('total_paid', 15, 2);
            $table->timestamps();

            $table->index('user_id');
            $table->index('address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
