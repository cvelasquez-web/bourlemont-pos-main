<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstadoOrden;

class Orden extends Model
{
    protected $table = 'ordenes';
    protected $primaryKey = 'id_orden';
    public $timestamps = false;

    protected $fillable = [
        'numero_mesa', 
        'id_usuario_creacion', 
        'estado', 
        'total',
        'fecha_creacion',
        'fecha_cierre'
    ];

    protected $casts = [
        'estado' => EstadoOrden::class,
        'total' => 'decimal:2',
        'fecha_creacion' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    // Relación con los items de la orden
    public function items()
    {
        return $this->hasMany(DetalleOrden::class, 'id_orden', 'id_orden');
    }

    // Relación con el usuario que creó la orden (¡La que faltaba!)
    public function usuario_creacion()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_creacion', 'id_usuario');
    }
    public function factura()
    {
        return $this->hasOne(FacturaFel::class, 'id_orden', 'id_orden');
    }
    public function pagos()
    {
        return $this->hasMany(TransaccionPago::class, 'id_orden', 'id_orden');
    }
}