<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtengo todos los productos (ordenados alfabeticamente A-Z)
        $productos = Producto::query()
                                ->orderBy('titulo', 'asc')
                                ->get();

        return Inertia::render('Productos/Index', [
            // Le paso todos los productos a mi vista
            'productos' => $productos,
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        // Obtengo un producto específico
        $producto = Producto::find($producto->id);

        return Inertia::render('Productos/Show', [
            // Le envio a mi vista, la informacion del producto cuyo ID me llega por la URL '/productos/{neumatico_id}'
            'producto' => $producto,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        //
    }

    /**
     * Actualizar los datos de productos
     */
    public function actualizarProductos() {
        Artisan::call('productos:fill'); // Se ejecutar el comando artisan 'productos:fill' => actualiza precios, stock, etc
        Artisan::call('productos:update'); // Se ejecutar el comando artisan 'productos:update' => actualiza enlaces, fotos, etc
        $output = Artisan::output(); // Guardamos el output en $output

        return response()->json([
            'status' => 'success', // Si llegamos a esa instancia, devolvemos 'success'
            'output' => $output, // Enviamos el '$output'
            'message' => 'Productos actualizados correctamente.' // Enviamos un 'message'
        ]);
    }

    /**
     * Obtener neumáticos actualizados
     */
    public function productosActualizados() {
        // Obtengo todos los productos (ya actualizados) y ordenados alfabeticamente A-Z
        $productos_actualizados = Producto::query()
                                            ->orderBy('titulo', 'asc')
                                            ->get();

        return response()->json([
            'status' => 'success', // Si llegamos a esa instancia, devolvemos 'success'
            'productos_actualizados' => $productos_actualizados, // Enviamos los productos actualizados en "productos_actualizados"
        ]);
    }

    /**
     * Obtener productos que coincidan con el término de busqueda
     */
    public function search(Request $request) {
        $searchQuery = $request->query('search');
                    // `$request->query('search')` me permite capturar los parametros que estoy enviando por la URL. Es equivalente a acceder a $_GET['search'] en PHP nativo......  Por ejemplo, si haces una petición GET a una URL como: `/productos-search?search=palabraClave`. La llamada $request->query('search') devolverá el valor "palabraClave"

        $results = Producto::query()
            ->where(function($query) use ($searchQuery) {
                $query->where('titulo', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('codigo', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('familia', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('subfamilia', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('atributos', 'LIKE', "%{$searchQuery}%");
            })
            ->orderBy('titulo', 'asc')
            ->get();
            
        return response()->json([
            'searchQuery' => $searchQuery,
            'results' => $results,
        ]);
    }
}
