<?php

namespace Neutron;

class Request
{
    public static function uri()
    {
        $basename = basename(dirname($_SERVER['PHP_SELF']));
        $request = str_replace($basename, '', $_SERVER['REQUEST_URI']);
        $request = str_replace('//', '/', $request);
        $request = rtrim($request, '/');

        return $request;
    }

    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}
