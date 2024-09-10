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
        Schema::create('article_movement_logs', function (Blueprint $table) {
            $table->id();

            $table->text('description')->nullable();
            $table->foreignId('article_id')->constrained(table: 'articles');
            $table->foreignId('user_id')->constrained(table: 'users'); 

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_movement_logs');
    }
};
