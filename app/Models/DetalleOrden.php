<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    use HasFactory;

    // Nombre exacto de la tabla en la base de datos
    protected $table = 'detalle_orden';

    // 👇 ESTA ES LA LÍNEA QUE SOLUCIONA EL ERROR 500 👇
    // Le dice a Laravel que la llave primaria se llama 'id' y no 'id_detalle'
    protected $primaryKey = 'id'; 
    
    // Desactivamos los timestamps porque la tabla no tiene created_at ni updated_at
    public $timestamps = false;

    protected $fillable = [
        'id_orden',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    // Relaciones
    public function orden()
    {
        return $this->belongsTo(Orden::class, 'id_orden', 'id_orden');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}