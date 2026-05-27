<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY age INT NULL");
        DB::statement("ALTER TABLE users MODIFY gender ENUM('male', 'female', 'other') NULL");
    }

    public function down(): void
    {
        DB::table('users')->whereNull('age')->update(['age' => 18]);
        DB::table('users')->whereNull('gender')->update(['gender' => 'other']);

        DB::statement("ALTER TABLE users MODIFY age INT NOT NULL");
        DB::statement("ALTER TABLE users MODIFY gender ENUM('male', 'female', 'other') NOT NULL");
    }
};
