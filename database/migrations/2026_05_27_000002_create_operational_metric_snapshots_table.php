<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('security_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('operational_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 120);
            $table->string('metric_name', 160);
            $table->decimal('score', 5, 2);
            $table->string('level', 60)->default('seguimiento');
            $table->string('trend', 40)->default('stable');
            $table->string('source', 80)->default('manual');
            $table->timestamp('measured_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'category', 'measured_at']);
            $table->index(['security_unit_id', 'category']);
            $table->index(['operational_group_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_metric_snapshots');
    }
};
