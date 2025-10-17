<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Neumatico;

class FillNeumaticosTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neumaticos:fill'; // comando => 'php artisan neumaticos:fill'

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Llena la tabla neumaticos consumiendo la API de WordPress';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando el proceso de llenado de la tabla neumaticos');

        // Url de la api
        $apiURL = 'https://cotizador.todoneumaticos.com.ar/wp-json/wp/v2/neumaticos';

        try {
            $page = 1;
            $perPage = 10; // Número de registros por página
            $totalRegistros = 0; // Contador de registros procesados

            // Bucle para recorrer páginas hasta que la respuesta sea un array vacío ([])
            do {
                // Intento hacer la solicitud o request HTTP a la API 
                $response = Http::get($apiURL, [
                    // Construir la URL con los parámetros de paginación
                    'page' => $page,
                    'per_page' => $perPage,
                ]);

                // Verifico que la respuesta es exitosa (en ese caso, guardo en la variable $data la respuesta en formato JSON)
                if($response->successful()) {
                    $data = $response->json();

                    // Si la respuesta está vacía, detenemos el bucle
                    if (empty($data)) {
                        break;
                    }

                    // dd($data); // Depuracion que me permite ver lo que contiene $data

                    // Iteramos sobre la data en formato JSON
                    foreach($data as $item) {

                        // Acceder al array "meta" donde están los datos relevantes
                        $meta = $item['meta'] ?? [];

                        // Extraemos los campos reelevantes del JSON
                        $codigo = $meta['codigo'] ?? null;
                        $titulo = $meta['titulo'] ?? null;
                        $marca = $meta['marca'] ?? null;
                        $modelo = $meta['modelo'] ?? null;
                        $precio_normal = $meta['precio_normal'] ?? 0;
                        $precio_mayorista = $meta['precio_mayorista'] ?? 0;
                        $precio_efectivo = $meta['precio_efectivo'] ?? 0;
                        $rodado = $meta['rodado'] ?? null;
                        $ancho = $meta['ancho'] ?? null;
                        $alto = $meta['alto'] ?? null;
                        $capacidad_carga = $meta['capacidad_carga'] ?? null;
                        $link_producto = $meta['link_producto'] ?? null;
                        $stock = $meta['stock'] ?? 9999;

                        // Validar que el campo "codigo" no sea nulo. Ignoramos aquellos registros que no tengan código
                        if (empty($codigo)) {
                            $this->error("Registro omitido: El campo 'codigo' está ausente.");
                            continue; // Ignorar este registro
                        }

                        // Crear registro en la tabla de neumaticos (si el código no existe)
                        Neumatico::updateOrCreate(
                            ['codigo' => $codigo], // Clave única para identificar cada neumático y evitar duplicados (a traves de este codigo determina si tiene que crear o actualizar un registro, si el codigo no está en la BBDD, entonces significa que tiene que crear, pero si el codigo está, significa que debe actualizar el registro)
                            [
                                'titulo' => $titulo,
                                'marca' => $marca,
                                'modelo' => $modelo,
                                'precio_normal' => $precio_normal,
                                'precio_mayorista' => $precio_mayorista,
                                'precio_efectivo' => $precio_efectivo,
                                'rodado' => $rodado,
                                'ancho' => $ancho,
                                'alto' => $alto,
                                'capacidad_carga' => $capacidad_carga,
                                'link_producto' => $link_producto,
                                'stock' => $stock,
                            ]
                        ); 

                        // Sumamos a la variable $totalRegistros una unidad por cada neumatico procesado, para saber cuantos neumáticos se procesaron
                        $totalRegistros++;
                    }
                    $this->info("Pagina $page procesada. Registros procesados: $totalRegistros");

                    // Incrementamos el numero de la página. Si llegamos a este punto, continuamos con la página siguiente
                    $page++;
                } else {
                    $this->error('Error, respuesta no válida: ' . $response->status());
                    break;
                }

            } while (true); // Continuar hasta que no haya más datos.
                // ¿Por qué "while (true)"?
                    // 1. No sabemos cuántas páginas hay: 
                        // La API no proporciona directamente información sobre el número total de páginas o registros. 
                        // En lugar de eso, sabemos que podemos detectar el final de los datos cuando la respuesta de la API está vacía ([]) o cuando "$response->successfull" sea FALSE (debido a que la respuesta de la API es un array vacío).
                    // 2. Bucle infinito controlado: 
                        // Usamos "while (true)" para crear un bucle que se ejecute indefinidamente, hasta que una condición interna (en este caso, $response->successful() == FALSE porque la respuesta de la API está vacía) indique que debemos detenerlo con break.
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
