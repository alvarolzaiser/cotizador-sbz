<?php

namespace App\Console\Commands;

use App\Models\Producto;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;

class FillProductosTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'productos:fill'; // comando => 'php artisan productos:fill'

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rellenar tabla de productos a través de la API de Wordpress';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso XML...');
        $apiUrl = 'https://centrosbz.com/articulos.xml';
        // $query = [
        //     'empresa' => 'plin',
        //     'usuario' => 'consultaweb',
        //     'clave'   => 'web3007',
        //     'dato'    => 'productoswebestandar',
        // ];

        // Parámetros de lote
        $batchSize = 100;
        $batch = [];
        $total  = 0;
        $omitidos = 0;
        $skusApi = [];

        // 1) Obtenemos el XML como stream
        // $response = Http::get($apiUrl, $query); // Con $query si hay parámetros
        $response = Http::get($apiUrl);
        if (! $response->successful()) {
            $this->error("Error al obtener XML: {$response->status()}");
            return 1;
        }

        $xmlString = $response->body();
        $reader = new \XMLReader();
        $reader->XML($xmlString);

        // 2) Iteramos sólo los nodos <productosweb>
        while ($reader->read()) {
            if ($reader->nodeType === \XMLReader::ELEMENT
                && $reader->name === 'productosweb'
            ) {
                // Convertimos este nodo en SimpleXMLElement
                $node = new \SimpleXMLElement($reader->readOuterXML());

                // Extraemos campos
                $sku            = (string) $node->ARTICULO__ID;
                $titulo         = (string) $node->DENOMINACION;
                $familia        = (string) $node->FAMILIA;
                $subfamilia     = (string) $node->SUBFAMILIA;
                $stock          = (int)    $node->STOCK;
                $precioVenta    = (float)  $node->PRECIOVENTA;
                $precioMayorista = (float) $node->PRECIOVENTA__AUX;

                if (empty($sku)) {
                    $this->error("Omitido: SKU vacío");
                    $omitidos++;
                } else {
                    $skusApi[] = $sku;
                    $batch[] = compact(
                        'sku','titulo','familia','subfamilia',
                        'stock','precioVenta', 'precioMayorista'
                    );
                }

                // Cuando alcanzamos el tamaño de lote, procesamos y vaciamos
                if (count($batch) >= $batchSize) {
                    $total += $this->processBatch($batch, $this);
                    $batch = [];
                }
            }
        }

        // Procesar remanente
        if (count($batch) > 0) {
            $total += $this->processBatch($batch, $this);
        }

        $reader->close();

        $this->info("Productos procesados: $total, omitidos: $omitidos");

        // 3) Detectar obsoletos
        $this->info('Marcando productos obsoletos (sin stock)...');
        $codigosDb = Producto::pluck('codigo')->toArray();
        $codigosObsoletos = array_diff($codigosDb, $skusApi);

        if (! empty($codigosObsoletos)) {
            Producto::whereIn('codigo', $codigosObsoletos)
                   ->update(['stock' => 0]);
            $this->info('Obsoletos actualizados: ' . count($codigosObsoletos));
        } else {
            $this->info('No hay obsoletos.');
        }

        $this->info('Proceso finalizado.');
        return 0;
    }



    /**
     * Procesa un lote de productos: inserta o actualiza.
     *
     * @param array $batch
     * @param Command $console
     * @return int  Cantidad de registros guardados
     */
    protected function processBatch(array $batch, Command $console): int
    {
        $guardados = 0;

        foreach ($batch as $item) {
            try {
                Producto::updateOrCreate(
                    ['codigo' => $item['sku']],
                    [
                        'titulo'            => $item['titulo'],
                        'familia'           => $item['familia'],
                        'subfamilia'        => $item['subfamilia'],
                        'stock'             => $item['stock'],
                        'precio_normal'      => $item['precioVenta'],
                        'precio_mayorista'  => $item['precioMayorista'],
                    ]
                );
                $guardados++;
            } catch (\Exception $e) {
                $console->error("Error SKU {$item['sku']}: " . $e->getMessage());
            }
        }

        $console->info("  → Lote de " . count($batch) . " procesado. Guardados: $guardados");
        return $guardados;
    }
}