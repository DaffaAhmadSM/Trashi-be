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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->bigIncrements('detail_id');
            $table->foreignId('trans_id')->constrained('transactions', 'trans_id')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('waste_categories', 'category_id')->cascadeOnDelete();
            $table->float('actual_weight');
            $table->timestamps();

            $table->index('trans_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
