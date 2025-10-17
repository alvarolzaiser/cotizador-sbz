<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_Cotizacion extends Model
{
    /** @use HasFactory<\Database\Factories\DetalleCotizacionFactory> */
    use HasFactory;

    protected $table = 'detalle__cotizacions';

    protected $fillable = // rellenables por asignacion masiva
    [
        'cotizacion_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    // Relación: Un detalle pertenece a una cotización
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    // Relación: Un detalle pertenece a un neumático
    // public function neumatico()
    // {
    //     return $this->belongsTo(Neumatico::class, 'neumatico_id');
    // }

    // Relación: Un detalle pertenece a un producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
