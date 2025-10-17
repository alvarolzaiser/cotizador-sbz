<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cotizacion>
 */
class CotizacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fecha_creacion' => $this->faker->date(),
            'user_id' => 1, // Yo mismo
            'estado' => $this->faker->randomElement(['pendiente', 'revision', 'aprobada', 'rechazada', 'sin respuesta']),
            'total' => $this->faker->randomFloat(2, 0, 10000), // Total entre 0 y 10,000 con 2 decimales
            'cliente_id' => Cliente::factory(), // Crea automáticamente un cliente asociado
        ];
    }
}
