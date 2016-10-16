<?php

namespace Neutron;

class App
{
    protected static $repository = [];

    public static function set($key, $value)
    {
        static::$repository[$key] = $value;
    }

    public static function get($key)
    {
        if (!array_key_exists($key, static::$repository)) {
            throw new Exception('No key named '.$key.' found in container');
        }

        return static::$repository[$key];
    }
}
