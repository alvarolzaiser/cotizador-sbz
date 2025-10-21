<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NeumaticoController;
use App\Http\Controllers\ProductoController;
use App\Models\Cliente;
use App\Models\Neumatico;
use App\Models\Producto;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth:sanctum'); // Antes de acceder a la ruta y ejecutar la lógica, se verifica el middleware "auth:sanctum"

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Todas estas rutas están cubiertas por el middleware "auth:sanctum"
        
        // Ruta para acceder al dashboard
        Route::get('/dashboard', [HomeController::class, 'index'])
            ->name('dashboard');

        // Ruta para acceder al carrito
        Route::get('/carrito', function(){
            return Inertia::render('Carrito/Cart', [
                'clientes' => Cliente::query()
                            ->where('user_id', auth()->user()->id)
                            ->orderBy('created_at', 'desc')
                            ->get(),
            ]);
        })->name('carrito');

        // Rutas para la entidad de Productos... Solo 'get' y 'show', ya que el resto del CRUD se hace en el otro server, aquí solo me interesa leer y mostrar esa info
        Route::get('/productos', [ProductoController::class, 'index']) // Mostrar una lista de productos
            ->name('productos.index');
        Route::get('/productos/{producto}', [ProductoController::class, 'show']) // Mostrar un neumatico especifico
            ->name('productos.show');

        // Rutas para la entidad Cliente
        Route::resource('clientes', ClienteController::class);

        // Rutas para la entidad Cliente
        Route::resource('cotizaciones', CotizacionController::class);
});

Route::post('/actualizar-productos', [ProductoController::class, 'actualizarProductos'])
    ->name('productos.actualizarProductos')
    ->middleware('auth:sanctum'); // Antes de acceder a la ruta y ejecutar la lógica, se verifica el middleware "auth:sanctum"

Route::get('/productos-actualizados', [ProductoController::class, 'productosActualizados'])
    ->name('productos.productosActualizados')
    ->middleware('auth:sanctum'); // Antes de acceder a la ruta y ejecutar la lógica, se verifica el middleware "auth:sanctum"

// Busqueda de productos
Route::get('/productos-search', [ProductoController::class, 'search'])
    ->name('productos.search')
    ->middleware('auth:sanctum'); // Antes de acceder a la ruta y ejecutar la lógica, se verifica el middleware "auth:sanctum"

// Busqueda de clientes
Route::get('/clientes-search', [ClienteController::class, 'search'])
    ->name('clientes.search')
    ->middleware('auth:sanctum'); // Antes de acceder a la ruta y ejecutar la lógica, se verifica el middleware "auth:sanctum"

// Actualizar numero desde la vista individual de la cotizacion
Route::put('/actualizar-numero/{cliente}', [ClienteController::class, 'actualizarNumero'])
    ->name('clientes.actualizarNumero')
    ->middleware('auth:sanctum');

// Actualizar estado desde la vista individual de la cotizacion
Route::put('/actualizar-estado/{cotizacion}', [CotizacionController::class, 'actualizarEstado'])
    ->name('cotizaciones.actualizarEstado')
    ->middleware('auth:sanctum');

// Enviar orden a 3C
Route::post('/enviar-cotizacion/{cotizacion}', [CotizacionController::class, 'enviarCotizacion'])
    ->name('cotizaciones.enviarCotizacion')
    ->middleware('auth:sanctum');

