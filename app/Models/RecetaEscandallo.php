<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecetaEscandallo extends Model
{
    protected $table = 'recetas_escandallos';
    protected $primaryKey = 'id_receta';
    public $timestamps = false;

    protected $fillable = [
        'id_producto_final', 
        'id_insumo_base', 
        'cantidad_descontar_ml'
    ];

    public function insumo()
    {
        return $this->belongsTo(Producto::class, 'id_insumo_base', 'id_producto');
    }
}