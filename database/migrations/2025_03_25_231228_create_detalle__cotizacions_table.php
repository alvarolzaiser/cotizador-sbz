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
        Schema::create('detalle__cotizacions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cotizacion_id')->constrained('cotizacions'); // Relación con la cotización
            $table->foreignId('producto_id')
                ->constrained('productos') // Relación con el producto
                ->onDelete('cascade'); // Cuando se elimine una cotización, tambien se eliminaran los detalles asociados a la misma
            // $table->foreignId('neumatico_id')->constrained('neumaticos'); // Relación con el neumático
            $table->integer('cantidad'); // Cantidad de neumáticos
            $table->decimal('precio_unitario', 10, 2); // Precio unitario en ese momento
            $table->decimal('subtotal', 10, 2); // Subtotal calculado

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle__cotizacions');
    }
};
