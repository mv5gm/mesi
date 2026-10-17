<?php

namespace App\Enums;

enum TipoMovimiento: string
{
    case Entrada = 'ENTRADA';
    case Salida = 'SALIDA';
    case Transferencia = 'TRANSFERENCIA';
    case Ajuste = 'AJUSTE';
}
