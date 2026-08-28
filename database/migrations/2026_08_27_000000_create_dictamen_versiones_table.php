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
        if (!Schema::hasTable('dictamen_versiones')) {
            Schema::create('dictamen_versiones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dictamen_id')->constrained('dictamenes')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedInteger('version')->default(1);
                $table->string('inventario')->nullable();
                $table->string('equipo')->nullable();
                $table->string('marca')->nullable();
                $table->string('modelo')->nullable();
                $table->string('serie')->nullable();
                $table->text('diagnostico')->nullable();
                $table->text('sugerencia')->nullable();
                $table->text('observaciones')->nullable();
                $table->string('motivo_cambio')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictamen_versiones');
    }
};
