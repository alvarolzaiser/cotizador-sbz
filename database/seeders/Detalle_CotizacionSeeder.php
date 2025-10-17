<?php

namespace Database\Seeders;

use App\Models\Detalle_Cotizacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Detalle_CotizacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creará 300 detalles de cotizacione de ejemplo
        Detalle_Cotizacion::factory(300)->create();
    }
}
