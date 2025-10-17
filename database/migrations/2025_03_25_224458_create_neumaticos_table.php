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
        Schema::create('neumaticos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->nullable(false);
            $table->string('titulo')->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->decimal('precio_normal', 10, 2)->nullable();
            $table->decimal('precio_mayorista', 10, 2)->nullable();
            $table->decimal('precio_efectivo', 10, 2)->nullable();
            $table->string('rodado')->nullable();
            $table->string('ancho')->nullable();
            $table->string('alto')->nullable();
            $table->string('capacidad_carga')->nullable();
            $table->text('link_producto')->nullable();
            $table->string('stock')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neumaticos');
    }
};
