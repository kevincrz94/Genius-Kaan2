<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'image')) {
                $table->string('image')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'age')) {
                $table->integer('age')->nullable()->after('image');
            }

            if (! Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('age');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->tinyInteger('status')->default(1)->after('gender');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['image', 'age', 'gender', 'status'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
