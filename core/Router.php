<?php

namespace Neutron;

use Exception;

class Router {

	protected $routes = [];

	public static function load($file)
	{
		$router = new static;
		require $file;
		return $router;
	}

	public function register($routes)
	{
		$this->routes = $routes;
	}

	public function route($uri)
	{
		if ( array_key_exists($uri, $this->routes) )
		{
			$route = explode('@', $this->routes[$uri]);
			$class = new $route[0];
			$method = $route[1];
			return $class->$method();
		}

		throw new Exception('Route of '. $uri .' not found');
	}

}