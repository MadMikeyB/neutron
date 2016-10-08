<?php
ini_set('display_errors', 'On');

require_once __DIR__ . '/vendor/autoload.php';

Neutron\Router::load(__DIR__ . '/app/routes.php')->route(Neutron\Request::uri());