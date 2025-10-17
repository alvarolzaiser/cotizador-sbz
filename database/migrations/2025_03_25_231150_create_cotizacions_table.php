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
        Schema::create('cotizacions', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_creacion');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('estado', ['pendiente', 'revision', 'aprobada', 'rechazada', 'sin respuesta'])->default('pendiente');
            $table->decimal('total', 10, 2)->default(0);
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacions');
    }
};
