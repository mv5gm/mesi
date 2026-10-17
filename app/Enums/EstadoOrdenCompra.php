<?php

namespace App\Enums;

enum EstadoOrdenCompra: string
{
    case Pendiente = 'PENDIENTE';
    case Recibida = 'RECIBIDA';
    case Cancelada = 'CANCELADA';
}
