<?php

$router = $this->getRouter();

$router->map('GET', '/', [\Neutron\Controller\HomeController::class, 'index']);
