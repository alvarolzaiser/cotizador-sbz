<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtengo todos los clientes
        $clientes = Cliente::query()
                            // ->where('user_id', auth()->user()->id)
                            ->orderBy('created_at', 'desc')
                            ->get();

        return Inertia::render('Clientes/Index', [
            // Le paso todos los clientes a mi vista
            'clientes' => $clientes,
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
    public function store(StoreClienteRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validate = $request->validate([
            'nombre' => 'string|max:255',
            'telefono' => 'string|max:255',
            'email' => 'string|max:255',
            'direccion' => 'string|max:255',
        ]);

        $cliente->update($validate);

        return response()->json([
            'status' => 'success',
            'cliente' => $cliente,
            'message' => "Cliente {$cliente} actualizado correctamente"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        //
    }
    
    /**
     * Obtener cliente que coincidan con el término de busqueda
     */
    public function search(Request $request) {
        $searchQuery = $request->query('search');
                    // `$request->query('search')` me permite capturar los parametros que estoy enviando por la URL. Es equivalente a acceder a $_GET['search'] en PHP nativo......  Por ejemplo, si haces una petición GET a una URL como: `/neumaticos-search?search=palabraClave`. La llamada $request->query('search') devolverá el valor "palabraClave"

        $results = Cliente::query()
            ->where(function($query) use ($searchQuery) {
                $query->where('nombre', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('telefono', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('email', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('direccion', 'LIKE', "%{$searchQuery}%");
            })
            // ->andWhere('user_id', $request->user()->id) // Asegura que solo se busquen clientes del usuario autenticado
            // El 'user_id' es un campo que tiene cada cliente y determina quien creó el usuario, para no mostrar clientes de vendedores diferentes.
            ->get();
            
        return response()->json([
            'searchQuery' => $searchQuery,
            'results' => $results,
        ]);
    }

    /**
     * Actualizar numero del usuario desde la vista de una cotizacion
     */
    public function actualizarNumero(Cliente $cliente, Request $request) {
        $validate = $request->validate([
            'telefono' => 'string|max:255',
        ]);

        $cliente->update($validate);
        
        return response()->json([
            'resultado' => "Numero actualizado para $cliente->nombre",
        ]);
    }
}
