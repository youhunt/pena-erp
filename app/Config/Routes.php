<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index', ['filter' => 'session']);
$routes->get('workspace/(:num)', 'Workspace::index/$1', ['filter' => 'session']);

$routes->group('administration', ['filter' => ['session', 'permission:platform.company.manage']], static function ($routes): void {
    $routes->get('companies', 'Administration::companies');
    $routes->get('companies/new', 'Administration::newCompany');
    $routes->post('companies', 'Administration::createCompany');
    $routes->get('companies/(:num)/edit', 'Administration::editCompany/$1');
    $routes->post('companies/(:num)', 'Administration::updateCompany/$1');
    $routes->get('branches', 'Administration::branches');
    $routes->get('branches/new', 'Administration::newBranch');
    $routes->post('branches', 'Administration::createBranch');
    $routes->get('branches/(:num)/edit', 'Administration::editBranch/$1');
    $routes->post('branches/(:num)', 'Administration::updateBranch/$1');
    $routes->get('regions', 'Administration::regions');
    $routes->get('access', 'Administration::access');
    $routes->post('access', 'Administration::assignAccess');
});

// ERP accounts are provisioned by administrators; public registration is disabled.
service('auth')->routes($routes, ['except' => ['register']]);
