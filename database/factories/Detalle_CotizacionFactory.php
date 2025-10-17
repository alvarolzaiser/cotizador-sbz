<?php

namespace Database\Factories;

use App\Models\Cotizacion;
use App\Models\Neumatico;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Detalle_Cotizacion>
 */
class Detalle_CotizacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        // Seleccionar un producto de los existentes en la BBDD de manera aleatoria
        $producto = Producto::inRandomOrder()->first(); // Obtiene un neumático aleatorio
        if (!$producto) {
            throw new \Exception('No hay productos disponibles en la base de datos.');
        }

        // Cantidad aleatoria
        $cantidad = $this->faker->numberBetween(1, 10); // Cantidad entre 1 y 10
        // Precio unitario aleatorio
        $precioUnitario = $producto->precio_normal != 0 ? $producto->precio_normal : 1; // Precio normal del producto seleccionado. En caso que el precio sea 0, se coloca un 1 por default

        return [
            'cotizacion_id' => Cotizacion::inRandomOrder()->first()->id, // Este detalle de cotización se va a asignar a alguna de las cotizacions ya existentes en la BBDD de manera aleatoria
            'producto_id' => $producto->id, // Usa el ID del producto existente
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal' => $cantidad * $precioUnitario, // Calcula el subtotal
        ];
    }
}
