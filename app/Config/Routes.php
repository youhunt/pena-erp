<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// ERP accounts are provisioned by administrators; public registration is disabled.
service('auth')->routes($routes, ['except' => ['register']]);
