<?php
namespace App\Enums;

enum TipoProducto: string {
    case FINAL = 'FINAL';
    case INSUMO = 'INSUMO';
    case COMPUESTO = 'COMPUESTO';
}