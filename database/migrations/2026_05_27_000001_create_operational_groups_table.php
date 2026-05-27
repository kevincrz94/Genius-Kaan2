<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('security_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 160);
            $table->string('code', 60)->nullable();
            $table->string('shift', 80)->nullable();
            $table->string('assignment_type', 120)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['security_unit_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_groups');
    }
};
