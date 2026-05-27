<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('security_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('operational_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 120);
            $table->string('severity', 40)->default('seguimiento');
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['severity', 'resolved_at']);
            $table->index(['security_unit_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_alerts');
    }
};
