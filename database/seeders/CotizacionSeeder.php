<?php

namespace Database\Seeders;

use App\Models\Cotizacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CotizacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creará 30 cotizaciones de ejemplo
        Cotizacion::factory(30)->create();
    }
}
