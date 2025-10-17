<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Neumatico extends Model
{
    /** @use HasFactory<\Database\Factories\NeumaticoFactory> */
    use HasFactory;

    protected $table = 'neumaticos';

    protected $fillable = // rellenables por asignacion masiva
    [
        'codigo',
        'titulo',
        'marca',
        'modelo',
        'precio_normal',
        'precio_mayorista',
        'precio_efectivo',
        'rodado',
        'ancho',
        'alto',
        'capacidad_carga',
        'link_producto',
        'stock',
    ];

    // Relación: Un neumático puede estar en muchos detalles de cotización
    public function detalles()
    {
        return $this->hasMany(Detalle_Cotizacion::class, 'neumatico_id');
    }
}
