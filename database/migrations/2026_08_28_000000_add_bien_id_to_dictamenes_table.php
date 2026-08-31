<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega bien_id a la tabla dictamenes para vincular
     * formalmente cada dictamen con un bien del inventario.
     */
    public function up(): void
    {
        Schema::table('dictamenes', function (Blueprint $table) {
            $table->unsignedBigInteger('bien_id')->nullable()->after('reporte_id');
            $table->index('bien_id');
        });

        // Agregar FK solo si bienes usa InnoDB (lo cual es el caso por defecto en Laravel)
        if (Schema::hasTable('bienes')) {
            Schema::table('dictamenes', function (Blueprint $table) {
                $table->foreign('bien_id')->references('id')->on('bienes')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dictamenes', function (Blueprint $table) {
            $table->dropForeign(['bien_id']);
            $table->dropIndex(['bien_id']);
            $table->dropColumn('bien_id');
        });
    }
};
