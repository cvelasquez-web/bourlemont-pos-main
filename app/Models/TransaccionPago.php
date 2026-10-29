<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\MetodoPago;

class TransaccionPago extends Model
{
    protected $table = 'transacciones_pago';
    protected $primaryKey = 'id_pago';
    public $timestamps = false;

    protected $fillable = [
        'id_orden', 
        'id_usuario_cobro', 
        'metodo_pago', 
        'monto_pagado', 
        'propina',
        'fecha_transaccion' // <-- Agregado
    ];

    protected $casts = [
        'metodo_pago' => MetodoPago::class, 
        'monto_pagado' => 'decimal:2', 
        'propina' => 'decimal:2',
        'fecha_transaccion' => 'datetime' // <-- Agregado
    ];

    // Relación con la Orden (para sacar la Mesa)
    public function orden()
    {
        return $this->belongsTo(Orden::class, 'id_orden', 'id_orden');
    }

    // Relación con el Usuario (para sacar el nombre del Bartender)
    public function usuarioCobro()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_cobro', 'id_usuario');
    }
}