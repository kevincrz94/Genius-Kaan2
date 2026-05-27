<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('code', 60)->nullable()->unique();
            $table->string('type', 80)->nullable();
            $table->string('municipality', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_units');
    }
};
