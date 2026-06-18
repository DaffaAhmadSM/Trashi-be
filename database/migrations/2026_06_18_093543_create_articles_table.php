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
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('article_id');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->dateTime('created_at');
            $table->boolean('is_published')->default(false);
            $table->timestamp('updated_at')->nullable();

            $table->index('author_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
