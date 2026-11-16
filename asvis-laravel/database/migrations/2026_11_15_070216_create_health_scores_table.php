<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('score_date');
            $table->integer('total_score');
            $table->integer('nutrition_score')->nullable();
            $table->integer('activity_score')->nullable();
            $table->integer('calories_in')->default(0);
            $table->integer('calories_burned')->default(0);
            $table->text('ai_insight')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_scores');
    }
};