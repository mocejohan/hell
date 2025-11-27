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
        Schema::create('dictamenes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reporte_id')->unsigned();
            $table->string('inventario');
            $table->string('equipo');
            $table->string('marca');
            $table->string('modelo');
            $table->string('serie');
            $table->text('diagnostico');
            $table->text('sugerencia');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictamens');
    }
};
