<?php

namespace App\Enums;

enum EstadoOrdenVenta: string
{
    case Pendiente = 'PENDIENTE';
    case Completada = 'COMPLETADA';
    case Cancelada = 'CANCELADA';
}
