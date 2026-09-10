<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('bienes')) {
            Schema::table('bienes', function (Blueprint $table) {
                $table->string('resguardatario')->nullable()->after('ubicacion');
            });
        }

        if (Schema::hasTable('dictamenes')) {
            Schema::table('dictamenes', function (Blueprint $table) {
                $table->string('resguardatario')->nullable()->after('serie');
            });
        }

        if (Schema::hasTable('dictamen_versiones')) {
            Schema::table('dictamen_versiones', function (Blueprint $table) {
                $table->string('resguardatario')->nullable()->after('serie');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bienes')) {
            Schema::table('bienes', function (Blueprint $table) {
                $table->dropColumn('resguardatario');
            });
        }

        if (Schema::hasTable('dictamenes')) {
            Schema::table('dictamenes', function (Blueprint $table) {
                $table->dropColumn('resguardatario');
            });
        }

        if (Schema::hasTable('dictamen_versiones')) {
            Schema::table('dictamen_versiones', function (Blueprint $table) {
                $table->dropColumn('resguardatario');
            });
        }
    }
};
