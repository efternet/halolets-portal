<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('resolution_types', 'deleted_at')) {
            Schema::table('resolution_types', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (! Schema::hasColumn('work_tasks', 'deleted_at')) {
            Schema::table('work_tasks', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('work_tasks', 'deleted_at')) {
            Schema::table('work_tasks', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('resolution_types', 'deleted_at')) {
            Schema::table('resolution_types', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
