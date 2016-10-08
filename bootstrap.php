<?php
ini_set('display_errors', 'On');

require_once __DIR__ . '/vendor/autoload.php';

$basename = basename(dirname($_SERVER['PHP_SELF']));
$request = str_replace($basename, '', $_SERVER['REQUEST_URI']);
$request = str_replace('//', '/', $request);

Neutron\Router::load(__DIR__ . '/app/routes.php')->route(rtrim($request, '/'));