<?php

namespace App\Support;

class Money
{
    public static function format(int|float|string|null $amount, int $decimals = 0, bool $symbol = true): string
    {
        $formatted = self::number((float) $amount, $decimals);

        return $symbol ? config('agri.currency').' '.$formatted : $formatted;
    }

    public static function number(int|float|string|null $value, int $decimals = 0): string
    {
        $value = round((float) $value, $decimals);

        if (config('agri.number_grouping') !== 'south_asian') {
            return number_format($value, $decimals);
        }

        $negative = $value < 0;
        [$whole, $fraction] = array_pad(explode('.', number_format(abs($value), $decimals, '.', '')), 2, null);
        $lastThree = substr($whole, -3);
        $rest = substr($whole, 0, -3);
        $grouped = $rest !== '' ? preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest).','.$lastThree : $lastThree;

        return ($negative ? '-' : '').$grouped.($fraction !== null ? '.'.$fraction : '');
    }

    public static function quantity(int|float|string|null $value): string
    {
        $value = (float) $value;
        $decimals = strlen(rtrim(substr(strrchr(number_format($value, 3, '.', ''), '.'), 1), '0'));

        return self::number($value, $decimals);
    }
}
