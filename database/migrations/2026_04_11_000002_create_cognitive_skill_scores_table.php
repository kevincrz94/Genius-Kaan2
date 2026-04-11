<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cognitive_skill_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cognitive_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->decimal('score', 5, 2);
            $table->string('trend', 32)->default('stable');
            $table->timestamp('measured_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'name']);
            $table->index(['user_id', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cognitive_skill_scores');
    }
};
