<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class HomeController extends Controller
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

        return Inertia::render('Dashboard', [
            // Le paso todos los productos a mi vista
            'productos' => $productos,
        ]);
    }
}
