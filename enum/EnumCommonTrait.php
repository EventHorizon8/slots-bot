<?php

declare(strict_types=1);

namespace app\enum;

trait EnumCommonTrait
{
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
