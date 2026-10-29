<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstadoFEL;

class FacturaFel extends Model
{
    protected $table = 'facturas_fel';
    protected $primaryKey = 'id_fel';
    public $timestamps = false;

    protected $fillable = [
        'id_orden',
        'uuid_autorizacion',
        'serie',
        'numero_dte',
        'fecha_certificacion',
        'nit_receptor',
        'nombre_receptor',
        'estado_certificacion',
        'xml_firmado',
    ];

    protected $casts = [
        'estado_certificacion' => EstadoFEL::class,
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'id_orden', 'id_orden');
    }
}