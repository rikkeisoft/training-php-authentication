<?php

namespace App;

class Hasher
{
    /**
     * @param string $string
     * @return string
     */
    public static function hash($string)
    {
        return md5($string);
    }

    /**
     * @param string $raw
     * @param string $hashed
     * @return bool
     */
    public static function match($raw, $hashed)
    {
        return static::hash($raw) === $hashed;
    }

    /**
     * @param string $hashed
     * @return bool
     */
    public static function needReHased($hashed)
    {
        return false;
    }
}
