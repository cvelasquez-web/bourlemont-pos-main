<?php
namespace App\Enums;

enum MetodoPago: string {
    case EFECTIVO = 'EFECTIVO';
    case TARJETA_VISA = 'TARJETA_VISA';
    case TARJETA_MASTERCARD = 'TARJETA_MASTERCARD';
    case TRANSFERENCIA = 'TRANSFERENCIA';
}