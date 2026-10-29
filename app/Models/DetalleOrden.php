<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    protected $table = 'detalle_orden';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_orden', 
        'id_producto', 
        'cantidad', 
        'precio_unitario', 
        'subtotal'
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2', 
        'subtotal' => 'decimal:2'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'id_orden', 'id_orden');
    }
}