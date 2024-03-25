<?php

namespace App\Traits;

trait EnumsTrait
{
    /**
     * Converts the object to an array.
     *
     * @return array The object as an array.
     */
    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->name] = $case->value;
        }

        return $array;
    }

    /**
     * Converts the cases to an associative array with the case value as key and the case name as value.
     *
     * @return array An associative array with the case value as key and the case name as value.
     */
    public static function toArrayNames(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->name;
        }

        return $array;
    }
}
