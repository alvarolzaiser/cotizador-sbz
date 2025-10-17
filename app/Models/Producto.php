<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    /** @use HasFactory<\Database\Factories\ProductoFactory> */
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = // rellenables por asignacion masiva
    [
        'codigo',
        'tipo',
        'titulo',
        'familia',
        'subfamilia',
        'precio_normal',
        'precio_mayorista',
        'link_producto',
        'image_url',
        'stock',
        'atributos',
    ];

    // Casteo los atributos para transformarlos de JSON a un ARRAY
    protected $casts = [ // Con esto, los atributos se guardarán como un objeto JSON en la base de datos y se devolverán como un array en PHP cuando consultes los productos. De esta forma, Laravel almacenará el array como JSON en la base de datos, y al recuperar el modelo lo decodificará automáticamente como un array (gracias a este cast)
        'atributos' => 'array',
    ];

    
    // Relación: Un producto puede estar en muchos detalles de cotización
    public function detalles()
    {
        return $this->hasMany(Detalle_Cotizacion::class, 'producto_id');
    }
    
}
