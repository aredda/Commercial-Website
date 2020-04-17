<?php

namespace App\Utility;

abstract class OperatorUtility
{
    public const EQUALS = 1;
    public const BIGGER_THAN = 2;
    public const SMALLER_THAN = 3;
    public const CONTAINS = 4;
    public const BIGGER_THAN_OR_EQUALS = 5;
    public const SMALLER_THAN_OR_EQUALS = 6;
    public const LATER_THAN = 7;
    public const OLDER_THAN = 8;

    public static function get (int $operator = self::EQUALS)
    {
        switch ($operator)
        {
            case self::BIGGER_THAN:             return function ($a, $b) { return $a > $b; };
            case self::SMALLER_THAN:            return function ($a, $b) { return $a < $b; };
            case self::CONTAINS:                return function ($a, $b) { return (strpos ($a, $b) !== false); };
            case self::BIGGER_THAN_OR_EQUALS:   return function ($a, $b) { return $a >= $b; };
            case self::SMALLER_THAN_OR_EQUALS:  return function ($a, $b) { return $a <= $b; };
            case self::LATER_THAN:              return function ($a, $b) { return strtotime($a) >= strtotime($b); };
            case self::OLDER_THAN:              return function ($a, $b) { return strtotime($a) <= strtotime($b); };
        }
        # Default case
        return function ($a, $b) { return $a == $b; };
    }
}