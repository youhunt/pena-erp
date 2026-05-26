<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']]);
$routes->get('workspace', 'Workspace::chooser', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']]);
$routes->post('workspace/context', 'Workspace::select', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']]);
$routes->get('workspace/(:num)', 'Workspace::index/$1', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']]);
$routes->get('workspace/modules/(:segment)', 'Workspace::module/$1', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']]);
$routes->get('account/security/password', 'AccountSecurity::password', ['filter' => ['session', 'sessionsecurity']]);
$routes->post('account/security/password', 'AccountSecurity::updatePassword', ['filter' => ['session', 'sessionsecurity']]);

$routes->group('inventory', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('', 'Inventory::index');
    $routes->post('uoms', 'Inventory::createUnitOfMeasure');
    $routes->post('categories', 'Inventory::createCategory');
    $routes->post('products', 'Inventory::createProduct');
    $routes->post('products/(:num)/status', 'Inventory::updateProductStatus/$1');
    $routes->post('warehouses', 'Inventory::createWarehouse');
    $routes->post('warehouses/(:num)/status', 'Inventory::updateWarehouseStatus/$1');
});

$routes->group('administration', ['filter' => ['session', 'sessionsecurity', 'passwordrequired', 'permission:platform.company.manage']], static function ($routes): void {
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
    $routes->post('users', 'Administration::createUser');
    $routes->post('users/(:num)/status', 'Administration::updateUserStatus/$1');
    $routes->post('users/(:num)/password', 'Administration::replaceUserPassword/$1');
    $routes->post('access', 'Administration::assignAccess');
    $routes->post('access/revoke', 'Administration::revokeAccess');
    $routes->post('access/company-status', 'Administration::updateCompanyMembership');
    $routes->post('access/branch-status', 'Administration::updateBranchMembership');
    $routes->get('rbac', 'Administration::rbac');
    $routes->post('rbac/roles', 'Administration::createRole');
    $routes->post('rbac/roles/(:num)', 'Administration::updateRole/$1');
    $routes->post('rbac/permissions', 'Administration::createPermission');
    $routes->post('rbac/grants', 'Administration::grantPermission');
    $routes->post('rbac/grants/revoke', 'Administration::revokePermission');
    $routes->post('rbac/menu-mappings', 'Administration::grantMenuPermission');
    $routes->post('rbac/menu-mappings/revoke', 'Administration::revokeMenuPermission');
});
$routes->get('administration/audit', 'Administration::audit', ['filter' => ['session', 'sessionsecurity', 'passwordrequired', 'permission:platform.audit.view']]);

// ERP accounts are provisioned by administrators; public registration is disabled.
service('auth')->routes($routes, ['except' => ['register']]);
