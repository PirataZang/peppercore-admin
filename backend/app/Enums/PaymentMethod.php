<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Pix = 'pix';
    case Boleto = 'boleto';
    case CreditCard = 'credit_card';

    public function label(): string
    {
        return match ($this) {
            self::Pix => 'Pix',
            self::Boleto => 'Boleto',
            self::CreditCard => 'Cartão de Crédito',
        };
    }
}
