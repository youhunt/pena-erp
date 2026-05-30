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
    $routes->post('uoms/(:num)', 'Inventory::updateUnitOfMeasure/$1');
    $routes->post('categories', 'Inventory::createCategory');
    $routes->post('categories/(:num)', 'Inventory::updateCategory/$1');
    $routes->post('products', 'Inventory::createProduct');
    $routes->post('products/(:num)', 'Inventory::updateProduct/$1');
    $routes->post('products/(:num)/status', 'Inventory::updateProductStatus/$1');
    $routes->post('warehouses', 'Inventory::createWarehouse');
    $routes->post('warehouses/(:num)', 'Inventory::updateWarehouse/$1');
    $routes->post('warehouses/(:num)/status', 'Inventory::updateWarehouseStatus/$1');
    $routes->post('locations', 'Inventory::createLocation');
    $routes->post('locations/(:num)', 'Inventory::updateLocation/$1');
    $routes->post('uom-conversions', 'Inventory::createUomConversion');
    $routes->post('item-taxes', 'Inventory::createItemTax');
    $routes->post('batches', 'Inventory::createBatch');
    $routes->post('batches/(:num)', 'Inventory::updateBatch/$1');
    $routes->post('product-profiles', 'Inventory::saveProductProfile');
    $routes->post('product-prices', 'Inventory::createProductPrice');
    $routes->post('stock-adjustments', 'Inventory::createStockAdjustment');
    $routes->post('stock-adjustments/(:num)/post', 'Inventory::postStockAdjustment/$1');
    $routes->post('stock-transfers', 'Inventory::createStockTransfer');
    $routes->post('stock-transfers/(:num)/post', 'Inventory::postStockTransfer/$1');
    $routes->post('status/(:segment)/(:num)', 'Inventory::updateMasterStatus/$1/$2');
});

$routes->group('setup', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('', 'Setup::index');
    $routes->post('departments', 'Setup::createDepartment');
    $routes->post('departments/(:num)', 'Setup::updateDepartment/$1');
    $routes->post('transaction-codes', 'Setup::createTransactionCode');
    $routes->post('transaction-codes/(:num)', 'Setup::updateTransactionCode/$1');
    $routes->post('addresses', 'Setup::createAddress');
    $routes->post('addresses/(:num)', 'Setup::updateAddress/$1');
    $routes->post('currencies', 'Setup::createCurrency');
    $routes->post('currencies/(:num)', 'Setup::updateCurrency/$1');
    $routes->post('tax-codes', 'Setup::createTaxCode');
    $routes->post('tax-codes/(:num)', 'Setup::updateTaxCode/$1');
    $routes->post('status/(:segment)/(:num)', 'Setup::updateStatus/$1/$2');
});

$routes->group('sales/master', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('', 'CommercialMaster::sales');
    $routes->post('terms', 'CommercialMaster::createCustomerTerm');
    $routes->post('terms/(:num)', 'CommercialMaster::updateCustomerTerm/$1');
    $routes->post('partners', 'CommercialMaster::createCustomer');
    $routes->post('partners/(:num)', 'CommercialMaster::updateCustomer/$1');
    $routes->post('profiles', 'CommercialMaster::saveCustomerProfile');
    $routes->post('addresses', 'CommercialMaster::linkCustomerAddress');
    $routes->post('addresses/(:num)', 'CommercialMaster::updateCustomerAddress/$1');
    $routes->post('promotions', 'CommercialMaster::createCustomerPromotion');
    $routes->post('promotions/(:num)', 'CommercialMaster::updateCustomerPromotion/$1');
    $routes->post('status/(:segment)/(:num)', 'CommercialMaster::updateSalesStatus/$1/$2');
});

$routes->group('sales/orders', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('', 'CommercialOrder::sales');
    $routes->post('', 'CommercialOrder::createSalesOrder');
    $routes->post('(:num)/confirm', 'CommercialOrder::confirmSalesOrder/$1');
});

$routes->group('sales', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('deliveries', 'SalesDelivery::index');
    $routes->post('deliveries/create', 'SalesDelivery::create');
    $routes->post('deliveries/(:num)/post', 'SalesDelivery::post/$1');
    $routes->get('deliveries/so-items/(:num)', 'SalesDelivery::soItems/$1');
});

$routes->group('purchasing/master', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('', 'CommercialMaster::purchasing');
    $routes->post('terms', 'CommercialMaster::createSupplierTerm');
    $routes->post('terms/(:num)', 'CommercialMaster::updateSupplierTerm/$1');
    $routes->post('partners', 'CommercialMaster::createSupplier');
    $routes->post('partners/(:num)', 'CommercialMaster::updateSupplier/$1');
    $routes->post('profiles', 'CommercialMaster::saveSupplierProfile');
    $routes->post('addresses', 'CommercialMaster::linkSupplierAddress');
    $routes->post('addresses/(:num)', 'CommercialMaster::updateSupplierAddress/$1');
    $routes->post('promotions', 'CommercialMaster::createSupplierPromotion');
    $routes->post('promotions/(:num)', 'CommercialMaster::updateSupplierPromotion/$1');
    $routes->post('status/(:segment)/(:num)', 'CommercialMaster::updatePurchasingStatus/$1/$2');
});

$routes->group('purchasing/orders', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('', 'CommercialOrder::purchasing');
    $routes->post('', 'CommercialOrder::createPurchaseOrder');
    $routes->post('(:num)/confirm', 'PurchaseOrderLifecycle::confirm/$1');
});

$routes->group('purchasing', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes) {
    $routes->get('receipts', 'GoodsReceipt::index');
    $routes->post('receipts/create', 'GoodsReceipt::create');
    $routes->post('receipts/(:num)/post', 'GoodsReceipt::post/$1');
    $routes->get('receipts/po-items/(:num)', 'GoodsReceipt::poItems/$1');
});

$routes->group('pos/master', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('', 'PosMaster::index');
    $routes->post('registers', 'PosMaster::createRegister');
    $routes->post('registers/(:num)', 'PosMaster::updateRegister/$1');
    $routes->post('registers/(:num)/status', 'PosMaster::updateStatus/$1');
    $routes->post('payment-methods', 'PosMaster::createPaymentMethod');
    $routes->post('payment-methods/(:num)', 'PosMaster::updatePaymentMethod/$1');
    $routes->post('payment-methods/(:num)/status', 'PosMaster::updatePaymentStatus/$1');
    $routes->post('shifts/open', 'PosMaster::openShift');
    $routes->post('shifts/(:num)/close', 'PosMaster::closeShift/$1');
    $routes->post('sales', 'PosMaster::createSale');
});

$routes->group('finance/master', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('', 'FinanceMaster::index');
    $routes->post('accounts', 'FinanceMaster::createAccount');
    $routes->post('accounts/(:num)', 'FinanceMaster::updateAccount/$1');
    $routes->post('cash-bank-accounts', 'FinanceMaster::createCashBank');
    $routes->post('cash-bank-accounts/(:num)', 'FinanceMaster::updateCashBank/$1');
    $routes->post('exchange-rates', 'FinanceMaster::createExchangeRate');
    $routes->post('exchange-rates/(:num)', 'FinanceMaster::updateExchangeRate/$1');
    $routes->post('gl-books', 'FinanceMaster::createGlBook');
    $routes->post('gl-columns', 'FinanceMaster::createGlColumn');
    $routes->post('cost-types', 'FinanceMaster::createCostType');
    $routes->post('item-costs', 'FinanceMaster::createItemCost');
    $routes->post('fiscal-periods', 'FinanceMaster::createFiscalPeriod');
    $routes->post('fiscal-periods/(:num)/close', 'FinanceMaster::closeFiscalPeriod/$1');
    $routes->post('fiscal-periods/(:num)/reopen', 'FinanceMaster::reopenFiscalPeriod/$1');
    $routes->post('module-periods/close', 'FinanceMaster::closeModulePeriod');
    $routes->post('module-periods/(:num)/reopen', 'FinanceMaster::reopenModulePeriod/$1');
    $routes->post('journals', 'FinanceMaster::createManualJournal');
    $routes->post('journals/(:num)/post', 'FinanceMaster::postJournal/$1');
    $routes->post('status/(:segment)/(:num)', 'FinanceMaster::updateStatus/$1/$2');
});

$routes->group('finance/invoices', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('', 'FinanceInvoice::index');
    $routes->post('purchase-invoices', 'FinanceInvoice::createPurchaseInvoice');
    $routes->post('sales-invoices', 'FinanceInvoice::createSalesInvoice');
    $routes->post('payments', 'FinanceInvoice::createPayment');
    $routes->post('purchase-invoices/(:num)/post', 'FinanceInvoice::postPurchaseInvoice/$1');
    $routes->post('sales-invoices/(:num)/post', 'FinanceInvoice::postSalesInvoice/$1');
    $routes->post('payments/(:num)/post', 'FinanceInvoice::postPayment/$1');
    $routes->get('payments/(:num)/allocations', 'FinanceInvoice::allocations/$1');
    $routes->post('payments/(:num)/allocations', 'FinanceInvoice::createAllocation/$1');
    $routes->post('payments/(:num)/allocations/(:num)/delete', 'FinanceInvoice::deleteAllocation/$1/$2');
});

$routes->group('administration', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']], static function ($routes): void {
    $routes->get('companies', 'Administration::companies');
    $routes->get('companies/new', 'Administration::newCompany');
    $routes->post('companies', 'Administration::createCompany');
    $routes->get('companies/(:num)/edit', 'Administration::editCompany/$1');
    $routes->post('companies/(:num)', 'Administration::updateCompany/$1');
    $routes->post('companies/(:num)/status', 'Administration::updateCompanyStatus/$1');
    $routes->get('branches', 'Administration::branches');
    $routes->get('branches/new', 'Administration::newBranch');
    $routes->post('branches', 'Administration::createBranch');
    $routes->get('branches/(:num)/edit', 'Administration::editBranch/$1');
    $routes->post('branches/(:num)', 'Administration::updateBranch/$1');
    $routes->post('branches/(:num)/status', 'Administration::updateBranchStatus/$1');
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
    $routes->get('menu-debug', 'TenantMenuDebug::index');
});
$routes->get('administration/audit', 'Administration::audit', ['filter' => ['session', 'sessionsecurity', 'passwordrequired']]);

// ERP accounts are provisioned by administrators; public registration is disabled.
service('auth')->routes($routes, ['except' => ['register']]);
