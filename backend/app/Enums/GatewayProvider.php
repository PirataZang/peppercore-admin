<?php

namespace App\Enums;

enum GatewayProvider: string
{
    case MercadoPago = 'mercado_pago';

    public function label(): string
    {
        return match ($this) {
            self::MercadoPago => 'Mercado Pago',
        };
    }
}
