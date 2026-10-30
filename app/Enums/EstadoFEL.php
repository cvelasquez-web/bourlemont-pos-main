<?php
namespace App\Enums;

enum EstadoFEL: string {
    case PENDIENTE = 'PENDIENTE';
    case CERTIFICADA = 'CERTIFICADA';
    case ERROR_OFFLINE = 'ERROR_OFFLINE';
    case ANULADA = 'ANULADA';
}