<?php

declare(strict_types=1);

namespace app\enum;

enum CountryIso: string
{
    use EnumCommonTrait;

    case PT = 'PT'; // Portugal
    case ES = 'ES'; // Spain
    case FR = 'FR'; // France
    case IT = 'IT'; // Italy

    public function getName(): string
    {
        return match ($this) {
            self::PT => 'Portugal',
            self::ES => 'Spain',
            self::FR => 'France',
            self::IT => 'Italy',
        };
    }
}
