<?php

namespace Neutron;

use Exception;

class Router
{
    protected $routes = [];

    public static function load($file)
    {
        $router = new static();
        require $file;

        return $router;
    }

    public function register($routes)
    {
        $this->routes = $routes;
    }

    public function route($uri)
    {
        if (array_key_exists($uri, $this->routes)) {
            $route = explode('@', $this->routes[$uri]);
            if (class_exists($route[0])) {
                $class = new $route[0]();
                $method = $route[1];
                if (method_exists($class, $method)) {
                    return $class->$method();
                } else {
                    throw new Exception($route[0].'::'.$route[1].' not found');
                }
            } else {
                throw new Exception($route[0].' not found');
            }
        }

        throw new Exception('Route of '.$uri.' not found');
    }
}
