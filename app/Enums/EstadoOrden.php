<?php
namespace App\Enums;
enum EstadoOrden: string {
    case ABIERTA = 'ABIERTA';
    case PAGADA = 'PAGADA';
    case ANULADA = 'ANULADA';
}