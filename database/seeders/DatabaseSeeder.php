<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Alvaro Ledesma Zaiser',
            'email' => 'alvarozaiser@gmail.com',
        ]);

        Cliente::factory()->create([
            'nombre' => 'X',
            'telefono' => '3518019558',
            'direccion' => 'X',
            'email' => 'ejemplo@ejemplo.com',
            'user_id' => 1,
        ]);

        // Editamos el seeder principal (DatabaseSeeder.php) para que se ejecute el comando `php artisan neumaticos:fill` cada vez que ejecutamos ``php artisan migrate:fresh --seed``
        Artisan::call('productos:fill'); // Se ejecutar el comando artisan 'productos:fill' => actualiza precios, stock, etc
        Artisan::call('productos:update'); // Se ejecutar el comando artisan 'productos:update' => actualiza enlaces, fotos, etc

        // Artisan::call('neumaticos:fill'); // Ejecutar el comando `neumaticos:fill`... Ahora, cuando ejecutes ``php artisan migrate:fresh --seed``, también se ejecutará el comando ``neumaticos:fill``.
    
        // // Llamo a mis seeders que ejecutan los factory's
        // $this->call([
        //     CotizacionSeeder::class, // Cuando se ejecute, por cada cotización creará un cliente asociado a esa cotización, por lo cual, no hace falta llamar al seeder de Cliente para que ejecute su factory, porque se va a crear un cliente de ejemplo por cada cotización de ejemplo que se genere
        //     Detalle_CotizacionSeeder::class, // Creamos los detalles asociados a cada cotización, cada detalle se va a ir asociando a alguna de las cotizaciones ya creadaadas en la BBDD por el factory de "Cotizacion" 
        // ]);
    
    }
}
