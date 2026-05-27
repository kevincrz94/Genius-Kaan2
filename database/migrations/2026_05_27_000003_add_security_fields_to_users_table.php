<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'badge_number')) {
                $table->string('badge_number', 80)->nullable()->after('id');
            }

            if (! Schema::hasColumn('users', 'rank')) {
                $table->string('rank', 120)->nullable()->after('badge_number');
            }

            if (! Schema::hasColumn('users', 'assignment_area')) {
                $table->string('assignment_area', 160)->nullable()->after('rank');
            }

            if (! Schema::hasColumn('users', 'security_unit_id')) {
                $table->foreignId('security_unit_id')->nullable()->after('assignment_area')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'operational_group_id')) {
                $table->foreignId('operational_group_id')->nullable()->after('security_unit_id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['operational_group_id', 'security_unit_id'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['assignment_area', 'rank', 'badge_number'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
