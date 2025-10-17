<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Http\Requests\StoreCotizacionRequest;
use App\Http\Requests\UpdateCotizacionRequest;
use App\Models\Cliente;
use App\Models\Detalle_Cotizacion;
use App\Models\Producto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use function Pest\Laravel\json;

class CotizacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtengo todas las cotizaciones (junto a los datos asociados que tiene, detalles, usuario que la creo, cliente que la solicitó, etc)
        $cotizaciones = Cotizacion::query()
                                    ->orderBy('created_at', 'desc')
                                    ->with([
                                        'detalles' => function($query) {
                                            $query->with('producto');
                                        }, 
                                        'user', 
                                        'cliente'
                                    ])
                                    ->get();

        return Inertia::render('Cotizaciones/Index', [
            // Le paso todas las cotizaciones a mi vista
            'cotizaciones' => $cotizaciones,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->enviar_cliente) {
            $clienteId = null;

            if ($request->crear_cliente) {
                // Validamos los datos
                $validate = $request->validate([
                    'nombre' => 'nullable|string|max:255',
                    'telefono' => 'nullable|string|max:255',
                    'email' => 'nullable|string|max:255',
                    'direccion' => 'nullable|string|max:255',
                ]);
                // Creamos el cliente
                $clienteNuevo = Cliente::create($validate);
                // Obtenemos el ID del cliente recien creado
                $clienteId = $clienteNuevo->id;
            } elseif ($request->cliente_seleccionado) {
                $clienteValidate = $request->validate([
                    'cliente_seleccionado' => 'numeric'
                ]);

                $clienteId = $clienteValidate['cliente_seleccionado'];
            } else {
                // En caso que no se seleccione un cliente, enviamos este error
                return response()->json([
                    'error' => 'Por favor elige un cliente'
                ], 422);
            }
        } else {
            $clienteId = 1;
        }

        $cotizacion = Cotizacion::create([
            'fecha_creacion' => now(),
            'user_id' => $request->user()->id,
            'estado' => 'pendiente',
            'total' => $request->total,
            'cliente_id' => $clienteId
        ]);

        foreach($request->carrito as $index => $item) {
            // Casting explícito del precio por si viene como string
            $precio = (float) $item['precio'];
            $cantidad = (int) $item['quantity'];

            Detalle_Cotizacion::create([
                'cotizacion_id' => $cotizacion->id, // ID de la cotizacion recién creada
                'producto_id' => $item['id'],
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => $cantidad * $precio
            ]);
        }

        $cotizacionCompleta = $cotizacion->load([
            'detalles' => function($query) {
                $query->with('producto');
            },
            'user',
            'cliente'
        ]);

        return response()->json([
            'formulario' => $request->all(),
            'cliente_id' => $clienteId,
            'user_id' => $request->user()->id,
            'resultado' => "Cotización #$cotizacion->id creada con éxito",
            'cotizacion' => $cotizacionCompleta,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cotizacion $cotizacione)
    {
        // Obtengo una cotizacion específica
        $cotizacion = $cotizacione->load([
            'detalles' => function ($query) {
                    $query->with('producto');
                }, 
            'user', 
            'cliente'
        ]);

        return Inertia::render('Cotizaciones/Show', [
            // Le envio a mi vista, la informacion de la cotización cuyo ID me llega por la URL '/cotizaciones/{cotizacione}'
            'cotizacion' => $cotizacion,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cotizacion $cotizacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cotizacion $cotizacione)
    {
        // Log::info('Update called', ['cotizacion_id' => $cotizacione->id]);

        $validated = $request->validate([
            'total' => 'required|numeric|min:0',
            'detalles' => 'required|array',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        DB::transaction(function() use ($cotizacione, $validated) {
            // Log::info('Transaction start', ['cotizacion_id' => $cotizacione->id, 'detalles_count' => count($validated['detalles'])]);

            $cotizacione->detalles()->delete();

            foreach ($validated['detalles'] as $item) {
                // Forzamos cotizacion_id para evitar problemas
                Detalle_Cotizacion::create([
                    'cotizacion_id'    => $cotizacione->id,
                    'producto_id'      => $item['producto_id'],
                    'cantidad'         => $item['cantidad'],
                    'precio_unitario'  => $item['precio_unitario'],
                    'subtotal'         => $item['cantidad'] * $item['precio_unitario'],
                ]);
            }

            $cotizacione->update(['total' => $validated['total']]);
        });

        return to_route('cotizaciones.show', $cotizacione->id)->with('success', 'Cotización actualizada con éxito.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cotizacion $cotizacion)
    {
        //
    }

    /**
     * Actualizar el estado de la cotizacion
     */
    public function actualizarEstado(Cotizacion $cotizacion, Request $request) {
        
        $cotizacion->update([
            'estado' => $request->newEstado
        ]);

        return response()->json([
            'resultadoEstado' => "Cambio de $request->oldEstado a $request->newEstado"
        ]);
    }

    /**
     * Enviar cotización a 3C
     */
    public function enviarCotizacion(Cotizacion $cotizacion) {

        $cotizacion = $cotizacion->load('detalles.producto');

        // Construimos el XML
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<documento>';
        $xml .= '<detallePedido>';

        foreach ($cotizacion->detalles as $item) {
            $denominacion = $item->producto->titulo;
            $cantidad = $item->cantidad;
            $codigo = $item->producto->codigo;
            $porbonif = 0;
            $precio__con__iva = $item->precio_unitario;
            $precio__id = 1;

            $xml .= '<detallepedido>';
            $xml .= "<cantidad>{$cantidad}</cantidad>";
            $xml .= "<codigo>{$codigo}</codigo>";
            $xml .= "<denominacion>{$denominacion}</denominacion>";
            $xml .= "<porbonif>{$porbonif}</porbonif>";
            $xml .= "<precio__con__iva>{$precio__con__iva}</precio__con__iva>";
            $xml .= "<precio__id>" . $precio__id . "</precio__id>";
            $xml .= '</detallepedido>';
        }

        $xml .= '</detallePedido>';
        $xml .= '<sObservaciones />';
        $xml .= '<sPersona__id>' . 101 . '</sPersona__id>';
        $xml .= '<sTipo>Nota de Pedido</sTipo>';
        $xml .= '<sTransporte__id>0</sTransporte__id>';
        $xml .= '</documento>';

        $url = 'http://dw01.ddns.net:7780/AppRest/servlet/ServletAndroid'
            . '?empresa=plin'
            . '&usuario=consultaweb'
            . '&clave=web3007'
            . '&dato=enviodocumento';

        // Finalizar ejecucion aqui hasta tener url valida
        exit();
        die();

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/xml; charset=UTF-8',
            ])
            ->timeout(5)
            ->withBody($xml, 'application/xml')
            ->post($url);

            // Parseamos la respuesta que viene en XML
            $body = $response->body();
            $xmlObj = simplexml_load_string($body); // Convertimos el string XML a un objeto SimpleXMLElement
            
            // Extraemos los valores específicos del XML
            $estado  = (string) ($xmlObj->estado ?? '');
            $mensaje = (string) ($xmlObj->mensaje ?? '');

            return response()->json([
                'body' => $response->body(),
                'estado' => $estado ?? '',
                'mensaje' => $mensaje ?? '',
                'xml' => $xml,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            Log::error('3C request failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Error al enviar a 3C',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
