<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteFactory> */
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = // rellenables por asignacion masiva
    [
        'nombre',
        'telefono',
        'email',
        'direccion',
        'user_id',
    ];

    // Relación: Un cliente tiene muchas cotizaciones
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'cliente_id');
    }

    // Relación: Un cliente pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
