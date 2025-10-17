<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    /** @use HasFactory<\Database\Factories\CotizacionFactory> */
    use HasFactory;

    protected $table = 'cotizacions';

    protected $fillable = // rellenables por asignacion masiva
    [
        'fecha_creacion',
        'estado',
        'total',
        'cliente_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
        ];
    }

    // Relación: Una cotización pertenece a un user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // Relación: Una cotización pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Relación: Una cotización tiene muchos detalles de cotización
    public function detalles()
    {
        return $this->hasMany(Detalle_Cotizacion::class, 'cotizacion_id');
    }
}
