<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Dtos\Enum;

use Nezasa\Checkout\Integrations\Nezasa\Enums\GenderEnum;

enum ErgoNamePrefixEnum: string
{
    case B = 'B';
    case I = 'I';
    case K = 'K';
    case H = 'H';
    case D = 'D';
    case G = 'G';
    case F = 'F';

    public function getDescription(): string
    {
        return match ($this) {
            self::B => 'Baby',
            self::I => 'Infant',
            self::K => 'Child',
            self::H => 'Mr.',
            self::D => 'Mrs.',
            self::G => 'Group',
            self::F => 'Company',
        };
    }

    public static function fromNezasaGender(?GenderEnum $gender): self
    {
        return match ($gender) {
            GenderEnum::Female => self::D,
            GenderEnum::Male => self::H,
            default => self::H,
        };
    }
}
