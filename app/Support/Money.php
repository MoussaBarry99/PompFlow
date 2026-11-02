<?php

namespace App\Support;

class Money
{
    public const CURRENCY = 'GNF';

    public static function format(float|int|string|null $value): string
    {
        return number_format((float) $value, 0, ',', "\u{00A0}") . "\u{00A0}" . self::CURRENCY;
    }
}