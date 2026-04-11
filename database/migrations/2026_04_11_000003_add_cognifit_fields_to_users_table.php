<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'cognifit_user_token')) {
                $table->string('cognifit_user_token')->nullable()->after('status');
            }

            if (! Schema::hasColumn('users', 'cognifit_locale')) {
                $table->string('cognifit_locale', 8)->default('es')->after('cognifit_user_token');
            }

            if (! Schema::hasColumn('users', 'cognifit_registered_at')) {
                $table->timestamp('cognifit_registered_at')->nullable()->after('cognifit_locale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'cognifit_registered_at')) {
                $table->dropColumn('cognifit_registered_at');
            }

            if (Schema::hasColumn('users', 'cognifit_locale')) {
                $table->dropColumn('cognifit_locale');
            }

            if (Schema::hasColumn('users', 'cognifit_user_token')) {
                $table->dropColumn('cognifit_user_token');
            }
        });
    }
};
