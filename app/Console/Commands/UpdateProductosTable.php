<?php

namespace App\Console\Commands;

use App\Models\Producto;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;

class UpdateProductosTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'productos:update'; // comando => 'php artisan productos:update'

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza Imágenes, atributos, etc';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando el proceso de llenado de la tabla productos');

        $apiURL = 'https://golosinasplin.com.ar/wp-json/custom/v1/items';

        try {
            $page = 1;
            $perPage = 100;
            $totalRegistros = 0;
            $omitidos = 0; // Contador para registros omitidos

            do {
                $response = Http::get($apiURL, [
                    'page' => $page,
                    'per_page' => $perPage,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (empty($data)) {
                        break;
                    }
                    
                    // dd($data);

                    foreach ($data as $item) {
                        $sku = $item['sku'] ?? null;
                        $tipo = $item['type'] ?? null;
                        $link_producto = $item['permalink'] ?? null;
                        $imagen = $item['image_url'] ?? null;
                        $atributos = $item['attributes'] ?? []; // Extraer los atributos

                        if (empty($sku)) {
                            $this->error("Registro omitido: El campo 'sku' está ausente o vacío.");
                            $omitidos++;
                            continue;
                        }

                        try {
                            // Busco el producto de mi BBDD que coincida con el SKU que me llega por la API
                            $producto = Producto::where('codigo', $sku)->first();
                            // Si el producto existe, entonces actualizo los datos en base a lo que me llega de la API
                            if ($producto) {
                                $producto->tipo          = $tipo;
                                $producto->link_producto = $link_producto;
                                $producto->image_url     = $imagen;
                                $producto->atributos     = $atributos; // <-- sin 'json_encode'. No hace falta utilizar 'json_encode($atributos)', Laravel ya hace eso automáticamente gracias al '$casts' que agregaste en el modelo 'Producto'. De esta forma, Laravel almacenará el array como JSON en la base de datos, y al recuperar el modelo lo decodificará automáticamente como un array (gracias al cast definido en el modelo 'Producto')
                                $producto->save(); // Guardo los datos
                                $totalRegistros++; // Sumo al total de actualizados el producto actual
                            } else {
                                $omitidos++;
                                $this->warn("SKU {$sku} no encontrado en BD, se omitió.");
                            }
                        } catch (\Exception $e) {
                            $this->error("Error al guardar producto con SKU $sku: " . $e->getMessage());
                            $omitidos++;
                            continue;
                        }
                    }
                    $this->info("Página $page procesada. Registros guardados: $totalRegistros, Omitidos: $omitidos");
                    $page++;
                } else {
                    $this->error('Error, respuesta no válida: ' . $response->status());
                    break;
                }
            } while (true);
                
                $this->info("Proceso finalizado. Total guardados: $totalRegistros, Total omitidos: $omitidos");
        } catch (\Exception $e) {
            $this->error('Error general: ' . $e->getMessage());
        }
    }
}
