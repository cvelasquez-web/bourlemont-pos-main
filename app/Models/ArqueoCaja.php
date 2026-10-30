<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstadoArqueo;

class ArqueoCaja extends Model
{
    protected $table = 'arqueos_caja';
    protected $primaryKey = 'id_arqueo';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'fecha_apertura',
        'fecha_cierre',
        'fondo_inicial',
        'efectivo_declarado',
        'tarjetas_declarado',
        'efectivo_teorico_sistema',
        'diferencia_efectivo',
        'estado',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'fondo_inicial' => 'decimal:2',
        'efectivo_declarado' => 'decimal:2',
        'tarjetas_declarado' => 'decimal:2',
        'efectivo_teorico_sistema' => 'decimal:2',
        'diferencia_efectivo' => 'decimal:2',
        'estado' => EstadoArqueo::class,
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}