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
        Schema::create('payment_fees', function (Blueprint $table) {
            $table->bigIncrements('fee_id');
            $table->foreignId('trans_id')->constrained('transactions', 'trans_id')->cascadeOnDelete();
            $table->foreignId('transaction_detail_id')->nullable()->constrained('transaction_details', 'detail_id')->nullOnDelete();
            $table->string('name');
            $table->string('category');
            $table->decimal('price', 15, 2);
            $table->string('currency');
            $table->timestamps();

            $table->index('trans_id');
            $table->index('transaction_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_fees');
    }
};
