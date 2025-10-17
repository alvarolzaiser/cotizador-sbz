<?php

namespace App\Http\Controllers;

use App\Models\Neumatico;
use App\Http\Requests\StoreNeumaticoRequest;
use App\Http\Requests\UpdateNeumaticoRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NeumaticoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtengo todos los neumaticos (ordenados alfabeticamente A-Z)
        $neumaticos = Neumatico::query()
                                ->orderBy('titulo', 'asc')
                                ->get();

        return Inertia::render('Neumaticos/Index', [
            // Le paso todos los neumaticos a mi vista
            'neumaticos' => $neumaticos,
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
    public function store(StoreNeumaticoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Neumatico $neumatico)
    {
        // Obtengo un neumático específico
        $neumatico = Neumatico::find($neumatico->id);

        return Inertia::render('Neumaticos/Show', [
            // Le envio a mi vista, la informacion del neumatico cuyo ID me llega por la URL '/neumaticos/{neumatico_id}'
            'neumatico' => $neumatico,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Neumatico $neumatico)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNeumaticoRequest $request, Neumatico $neumatico)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Neumatico $neumatico)
    {
        //
    }

    /**
     * Actualizar los datos de neumaticos
     */
    public function actualizarNeumaticos() {
        Artisan::call('neumaticos:fill'); // Se ejecutar el comando artisan 'neumaticos:fill'
        $output = Artisan::output(); // Guardamos el output en $output

        return response()->json([
            'status' => 'success', // Si llegamos a esa instancia, devolvemos 'success'
            'output' => $output, // Enviamos el '$output'
            'message' => 'Neumaticos actualizados correctamente.' // Enviamos un 'message'
        ]);
    }

    /**
     * Obtener neumáticos actualizados
     */
    public function neumaticosActualizados() {
        // Obtengo todos los neumaticos (ya actualizados) y ordenados alfabeticamente A-Z
        $neumaticos_actualizados = Neumatico::query()
                                            ->orderBy('titulo', 'asc')
                                            ->get();

        return response()->json([
            'status' => 'success', // Si llegamos a esa instancia, devolvemos 'success'
            'neumaticos_actualizados' => $neumaticos_actualizados, // Enviamos los neumaticos actualizados en "neumaticos_actualizados"
        ]);
    }

    /**
     * Obtener neumaticos que coincidan con el término de busqueda
     */
    public function search(Request $request) {
        $searchQuery = $request->query('search');
                    // `$request->query('search')` me permite capturar los parametros que estoy enviando por la URL. Es equivalente a acceder a $_GET['search'] en PHP nativo......  Por ejemplo, si haces una petición GET a una URL como: `/neumaticos-search?search=palabraClave`. La llamada $request->query('search') devolverá el valor "palabraClave"

        $results = Neumatico::query()
            ->where(function($query) use ($searchQuery) {
                $query->where('titulo', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('codigo', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('marca', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('modelo', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('rodado', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('ancho', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('alto', 'LIKE', "%{$searchQuery}%");
            })
            ->orderBy('titulo', 'asc')
            ->get();
            
        return response()->json([
            'searchQuery' => $searchQuery,
            'results' => $results,
        ]);
    }
}
