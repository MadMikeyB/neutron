<?php
// Autoloader
require_once __DIR__.'/vendor/autoload.php';

// Load our Environment Variables
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// are we in debug?
if (getenv('APP_DEBUG') == true) {
    ini_set('display_errors', 'On');
}

// Set up our Paths
define('BASE_DIR', __DIR__.'/');
define('APP_DIR', BASE_DIR.'/app');
define('CORE_DIR', BASE_DIR.'/core');
define('VIEW_PATH', APP_DIR.'/views');

// Boot up our Router and handle requests
Neutron\Router::load(__DIR__.'/app/routes.php')->route(Neutron\Request::uri());
