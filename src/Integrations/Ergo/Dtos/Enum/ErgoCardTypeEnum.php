<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\Enum;

enum ErgoCardTypeEnum: string
{
    case AX = 'AX'; // American Express
    case BC = 'BC'; // Bank Card
    case BL = 'BL'; // Carte Bleu
    case CB = 'CB'; // Carte Blanche
    case DN = 'DN'; // Diner's Club
    case DS = 'DS'; // Discover Card
    case EC = 'EC'; // Eurocard
    case JC = 'JC'; // Japanese Credit Bureau Credit Card
    case MC = 'MC'; // Mastercard
    case TP = 'TP'; // Universal Air Travel Card
    case VI = 'VI'; // Visa

    public function getDescription(): string
    {
        return match ($this) {
            self::AX => 'American Express',
            self::BC => 'Bank Card',
            self::BL => 'Carte Bleu',
            self::CB => 'Carte Blanche',
            self::DN => 'Diner\'s Club',
            self::DS => 'Discover Card',
            self::EC => 'Eurocard',
            self::JC => 'Japanese Credit Bureau Credit Card',
            self::MC => 'Mastercard',
            self::TP => 'Universal Air Travel Card',
            self::VI => 'Visa',
        };
    }
}
