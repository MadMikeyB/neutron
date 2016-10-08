<?php
ini_set('display_errors', 'On');

require_once __DIR__ . '/vendor/autoload.php';

define('APP_DIR', __DIR__ . '/app');
define('CORE_DIR', __DIR__ . '/core');
define('BASE_DIR', __DIR__ . '/');

Neutron\Router::load(__DIR__ . '/app/routes.php')->route(Neutron\Request::uri());