<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BitacoraAuditoria extends Model
{
    protected $table = 'bitacora_auditoria';
    
    // 1. Le decimos a Laravel cuál es la verdadera Primary Key
    protected $primaryKey = 'id_bitacora';

    // 2. Le decimos que use los timestamps de Laravel
    public $timestamps = true;
    
    // 3. Como tu tabla NO tiene 'updated_at', lo anulamos para que no dé error
    const UPDATED_AT = null;

    // 4. Cambiamos 'fecha' por 'created_at' que es la columna real
    protected $fillable = [
        'id_usuario', 
        'accion', 
        'descripcion', 
        'created_at'
    ];

    // 5. Le decimos que trate created_at como fecha/hora
    protected $casts = [
        'created_at' => 'datetime'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}