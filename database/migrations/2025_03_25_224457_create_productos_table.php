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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->nullable(false);
            $table->string('tipo')->nullable();
            $table->string('titulo')->nullable();
            $table->string('familia')->nullable();
            $table->string('subfamilia')->nullable();
            $table->decimal('precio_normal', 10, 2)->nullable();
            $table->decimal('precio_mayorista', 10, 2)->nullable();
            $table->text('link_producto')->nullable();
            $table->text('image_url')->nullable();
            $table->integer('stock')->nullable();
            $table->json('atributos')->nullable(); // Guardo los atributos en formato JSON dentro de la BBDD. De esta manera (json) puedo almacenar los atributos en formato JSON dentro de la BBDD, que es la manera ideal para guardar estructuras como arrays u objetos.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
