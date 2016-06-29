<?php

namespace Purpose;

class Shared
{
    private static $data = array();

    public static function set($name, $value)
    {
        self::$data[$name] = $value;
    }

    public static function get($name, \Closure $fn = null)
    {
        if (array_key_exists($name, self::$data)) {
            return self::$data[$name];
        }

        return self::$data[$name] = $fn();
    }
}
