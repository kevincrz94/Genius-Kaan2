<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160)->unique();
            $table->string('code', 60)->nullable()->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_areas');
    }
};
