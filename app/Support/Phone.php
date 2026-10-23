<?php

namespace App\Support;

class Phone
{
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '92') && strlen($digits) === 12) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }

    public static function display(?string $phone): string
    {
        if (! $phone || strlen($phone) !== 11) {
            return (string) $phone;
        }

        return substr($phone, 0, 4).' '.substr($phone, 4);
    }
}
