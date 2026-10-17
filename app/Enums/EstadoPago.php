<?php

namespace App\Enums;

enum EstadoPago: string
{
    case Pendiente = 'PENDIENTE';
    case Parcial = 'PARCIAL';
    case Pagado = 'PAGADO';
}
