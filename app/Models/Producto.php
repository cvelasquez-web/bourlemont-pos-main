<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\CategoriaProducto;
use App\Enums\TipoProducto;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'categoria', 'tipo', 'precio_venta', 
        'stock_ml', 'stock_minimo_ml', 'unidad_medida', 'imagen_url'
    ];

    protected $casts = [
        'categoria' => CategoriaProducto::class,
        'tipo' => TipoProducto::class,
        'precio_venta' => 'decimal:2',
    ];

    public function recetas()
    {
        return $this->hasMany(RecetaEscandallo::class, 'id_producto_final', 'id_producto');
    }
}