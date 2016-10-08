<?php

$router->register([
	'' => 'App\Controllers\HomeController@index',
	'/example' => 'App\Controllers\HomeController@example'
]);