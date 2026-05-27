<?php

declare(strict_types=1);

use App\Database\Seeds\DevelopmentFoundationSeeder;
use App\Database\Seeds\MultiCompanyDemoSeeder;
use App\Auth\ShieldUserProvisioningService;
use App\Authorization\TenantAuthorizationService;
use App\Services\RegionImportService;
use App\Services\RegionApiSyncService;
use App\Services\TenantContextService;
use App\Services\TenantMenuService;
use App\Services\UserSessionSecurityService;
use App\Models\AdministrationReadModel;
use App\Models\AdministrationWriteModel;
use App\Models\CommercialReadModel;
use App\Models\CommercialWriteModel;
use App\Models\FinanceReadModel;
use App\Models\FinanceWriteModel;
use App\Models\InventoryReadModel;
use App\Models\InventoryWriteModel;
use App\Models\PosReadModel;
use App\Models\PosWriteModel;
use App\Models\SetupReadModel;
use App\Models\SetupWriteModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class FoundationMasterTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = [
        'CodeIgniter\Shield',
        'App',
    ];

    protected $seed = DevelopmentFoundationSeeder::class;

    public function testDevelopmentSeedBuildsRegionalHierarchyAndCompanyBranch(): void
    {
        $this->seeInDatabase('provinces', ['code' => '31', 'name' => 'DKI Jakarta']);
        $this->seeInDatabase('villages', ['code' => '3174070006', 'name' => 'Cengkareng Barat']);
        $this->seeInDatabase('companies', ['code' => 'PENA', 'name' => 'PT Pena Inovasi Sistem']);
        $this->seeInDatabase('branches', ['code' => 'JKT', 'name' => 'Jakarta Head Office']);
        $this->seeInDatabase('roles', ['code' => 'owner', 'name' => 'Owner']);
        $this->seeInDatabase('permissions', ['code' => 'company.dashboard.view']);
    }

    public function testDevelopmentSeedCanRunMoreThanOnceWithoutDuplicatingMasters(): void
    {
        $this->seed(DevelopmentFoundationSeeder::class);

        $this->assertSame(1, $this->db->table('companies')->where('code', 'PENA')->countAllResults());
        $this->assertSame(1, $this->db->table('branches')->where('code', 'JKT')->countAllResults());
        $this->assertSame(1, $this->db->table('villages')->where('code', '3174070006')->countAllResults());
        $this->assertSame(1, $this->db->table('roles')->where('code', 'owner')->countAllResults());
    }

    public function testTenantPermissionRequiresMembershipAndRoleGrant(): void
    {
        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $this->db->table('users')->insert([
            'username'   => 'administrator',
            'active'     => true,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userId = (int) $this->db->insertID();
        $this->db->table('auth_identities')->insert([
            'user_id'    => $userId,
            'type'       => 'email_password',
            'secret'     => 'admin@belajardisiniaja.com',
            'secret2'    => 'not-used-for-authorization-test',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $service = new TenantAuthorizationService();
        $this->assertFalse($service->can($userId, $companyId, 'company.dashboard.view'));

        $this->seed(DevelopmentFoundationSeeder::class);

        $this->assertTrue($service->can($userId, $companyId, 'company.dashboard.view'));
    }

    public function testRegionImporterLoadsVersionedHierarchyIdempotently(): void
    {
        $importer = new RegionImportService($this->db);
        $path     = ROOTPATH . 'tests/_support/Data/regions';

        $counts = $importer->importDirectory($path, 'test-regions-v1');
        $importer->importDirectory($path, 'test-regions-v1');

        $this->assertSame(1, $counts['provinces']);
        $this->seeInDatabase('provinces', ['code' => '12', 'source_version' => 'test-regions-v1']);
        $this->seeInDatabase('regencies', ['code' => '12.75', 'name' => 'Kota Medan']);
        $this->seeInDatabase('villages', ['code' => '12.75.01.1001', 'postal_code' => '20212']);
        $this->assertSame(1, $this->db->table('villages')->where('code', '12.75.01.1001')->countAllResults());
    }

    public function testRegionApiSyncMapsOfficialHierarchyIntoMasterTables(): void
    {
        $payloads = [
            '/provinsi/'  => [['id' => '35', 'description' => 'JAWA TIMUR']],
            '/kabupaten/' => [['id' => '3579', 'description' => 'KOTA BATU', 'provinsi_id' => '35']],
            '/kecamatan/' => [['id' => '3579020', 'description' => 'JUNREJO', 'kabupaten_id' => '3579']],
            '/desa/'      => [['id' => '3579020004', 'description' => 'TORONGREJO', 'kecamatan_id' => '3579020']],
        ];
        $sync = new RegionApiSyncService(
            new RegionImportService($this->db),
            static fn (string $path): array => $payloads[$path],
        );

        $counts = $sync->sync('https://regions.example.test', 'test-token', 'api-test-v1');

        $this->assertSame(['provinces' => 1, 'regencies' => 1, 'districts' => 1, 'villages' => 1], $counts);
        $this->seeInDatabase('provinces', ['code' => '35', 'name' => 'JAWA TIMUR', 'source_version' => 'api-test-v1']);
        $this->seeInDatabase('regencies', ['code' => '3579', 'name' => 'BATU', 'type' => 'kota']);
        $this->seeInDatabase('villages', ['code' => '3579020004', 'name' => 'TORONGREJO', 'type' => 'desa_kelurahan']);
    }

    public function testTenantContextUsesAssignedBranchAndRejectsUnavailableBranch(): void
    {
        $this->db->table('users')->insert([
            'username'   => 'administrator',
            'active'     => true,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userId = (int) $this->db->insertID();
        $this->db->table('auth_identities')->insert([
            'user_id'    => $userId,
            'type'       => 'email_password',
            'secret'     => 'admin@belajardisiniaja.com',
            'secret2'    => 'not-used-for-context-test',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->seed(DevelopmentFoundationSeeder::class);

        $session = service('session');
        $session->remove(['tenant_company_id', 'tenant_branch_id']);
        $service = new TenantContextService($this->db, $session);
        $context = $service->current($userId);

        $this->assertNotNull($context);
        $this->assertSame('PENA', $context['company_code']);
        $this->assertSame('JKT', $context['branch_code']);
        $this->assertNull($service->activate($userId, (int) $context['company_id'], 999999));
    }

    public function testDynamicRoleGrantCannotCrossCompanyBoundary(): void
    {
        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $actorId = $this->createTestUser('rbac-owner', 'rbac-owner@example.com');
        $foreignCompanyId = $this->insertForeignCompany();
        $writer = new AdministrationWriteModel();

        $writer->createRole([
            'company_id' => $companyId,
            'code'       => 'purchasing',
            'name'       => 'Purchasing',
            'status'     => 'active',
        ], $actorId);
        $writer->createPermission([
            'company_id' => $foreignCompanyId,
            'code'       => 'purchasing.po.view',
            'name'       => 'View purchase order',
            'module'     => 'purchasing',
        ], $actorId);

        $roleId = (int) $this->db->table('roles')->where(['company_id' => $companyId, 'code' => 'purchasing'])->get()->getFirstRow()->id;
        $permissionId = (int) $this->db->table('permissions')->where(['company_id' => $foreignCompanyId, 'code' => 'purchasing.po.view'])->get()->getFirstRow()->id;

        $this->assertFalse($writer->grantRolePermission($companyId, $roleId, $permissionId, $actorId));
        $this->assertSame(0, $this->db->table('role_permissions')->where(['role_id' => $roleId, 'permission_id' => $permissionId])->countAllResults());
    }

    public function testDynamicTenantRoleCanGrantPermissionToAssignedUser(): void
    {
        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $userId = $this->createTestUser('purchasing-user', 'purchasing@example.com');
        $writer = new AdministrationWriteModel();

        $writer->createRole([
            'company_id' => $companyId,
            'code'       => 'purchasing',
            'name'       => 'Purchasing',
            'status'     => 'active',
        ], $userId);
        $writer->createPermission([
            'company_id' => $companyId,
            'code'       => 'purchasing.po.view',
            'name'       => 'View purchase order',
            'module'     => 'purchasing',
        ], $userId);

        $roleId = (int) $this->db->table('roles')->where(['company_id' => $companyId, 'code' => 'purchasing'])->get()->getFirstRow()->id;
        $permissionId = (int) $this->db->table('permissions')->where(['company_id' => $companyId, 'code' => 'purchasing.po.view'])->get()->getFirstRow()->id;

        $this->assertTrue($writer->grantRolePermission($companyId, $roleId, $permissionId, $userId));
        $this->assertTrue($writer->assignRole($companyId, $userId, $roleId, null, $userId));
        $this->assertTrue((new TenantAuthorizationService())->can($userId, $companyId, 'purchasing.po.view'));
    }

    public function testAdministrativeWritesAndContextSwitchAreAudited(): void
    {
        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $branchId  = (int) $this->db->table('branches')->where(['company_id' => $companyId, 'code' => 'JKT'])->get()->getFirstRow()->id;
        $userId    = $this->createTestUser('audited-user', 'audited@example.com');
        $writer    = new AdministrationWriteModel();

        $writer->createRole([
            'company_id' => $companyId,
            'code'       => 'audited_role',
            'name'       => 'Audited Role',
            'status'     => 'active',
        ], $userId);
        $roleId = (int) $this->db->table('roles')->where(['company_id' => $companyId, 'code' => 'audited_role'])->get()->getFirstRow()->id;
        $this->assertTrue($writer->assignRole($companyId, $userId, $roleId, $branchId, $userId));

        $session = service('session');
        $session->remove(['tenant_company_id', 'tenant_branch_id']);
        $this->assertNotNull((new TenantContextService($this->db, $session))->activate($userId, $companyId, $branchId));

        $this->seeInDatabase('audit_logs', ['event_type' => 'ROLE_CREATED', 'company_id' => $companyId, 'user_id' => $userId]);
        $this->seeInDatabase('audit_logs', ['event_type' => 'USER_ROLE_ASSIGNED', 'company_id' => $companyId, 'user_id' => $userId]);
        $this->seeInDatabase('audit_logs', ['event_type' => 'TENANT_CONTEXT_CHANGED', 'company_id' => $companyId, 'branch_id' => $branchId]);
    }

    public function testInactiveCompanyRevokesTenantContextAndPermission(): void
    {
        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $userId    = $this->createTestUser('inactive-company-user', 'admin@belajardisiniaja.com');
        $this->seed(DevelopmentFoundationSeeder::class);

        $this->assertTrue((new TenantAuthorizationService())->can($userId, $companyId, 'company.dashboard.view'));
        $this->db->table('companies')->where('id', $companyId)->update(['status' => 'inactive']);

        $session = service('session');
        $session->remove(['tenant_company_id', 'tenant_branch_id']);
        $this->assertNull((new TenantContextService($this->db, $session))->current($userId));
        $this->assertFalse((new TenantAuthorizationService())->can($userId, $companyId, 'company.dashboard.view'));
    }

    public function testInactiveAssignedBranchIsNotOfferedAsUnscopedContext(): void
    {
        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $userId    = $this->createTestUser('inactive-branch-user', 'admin@belajardisiniaja.com');
        $this->seed(DevelopmentFoundationSeeder::class);
        $this->db->table('branches')->where(['company_id' => $companyId, 'code' => 'JKT'])->update(['status' => 'inactive']);

        $service = new TenantContextService($this->db, service('session'));
        $this->assertSame([], $service->availableContexts($userId));
    }

    public function testBranchCannotBeMovedAcrossCompaniesByRegularEdit(): void
    {
        $companyId        = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $branchId         = (int) $this->db->table('branches')->where(['company_id' => $companyId, 'code' => 'JKT'])->get()->getFirstRow()->id;
        $foreignCompanyId = $this->insertForeignCompany();
        $actorId          = $this->createTestUser('branch-editor', 'branch-editor@example.com');

        (new AdministrationWriteModel())->updateBranch($branchId, [
            'company_id' => $foreignCompanyId,
            'name'       => 'Still Pena Branch',
            'status'     => 'active',
        ], $actorId);

        $this->seeInDatabase('branches', ['id' => $branchId, 'company_id' => $companyId, 'name' => 'Still Pena Branch']);
        $this->seeInDatabase('audit_logs', ['event_type' => 'BRANCH_UPDATED', 'entity_id' => $branchId, 'company_id' => $companyId]);
    }

    public function testDemoSeederBuildsSeveralTenantsAndRoleSpecificMenus(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $this->seeInDatabase('companies', ['code' => 'PENA']);
        $this->seeInDatabase('companies', ['code' => 'NUSA']);
        $this->seeInDatabase('companies', ['code' => 'KARYA']);
        $this->seeInDatabase('branches', ['code' => 'SBY']);
        $this->seeInDatabase('branches', ['code' => 'MKS']);
        $this->seeInDatabase('menus', ['code' => 'documents', 'label' => 'AI Document Processing']);

        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $purchasingId = $this->demoUserId('purchasing@demo.pena-erp.test');
        $financeId = $this->demoUserId('finance@demo.pena-erp.test');
        $service = new TenantMenuService($this->db);

        $purchasingMenus = array_column($service->accessibleMenus($purchasingId, $companyId), 'code');
        $financeMenus = array_column($service->accessibleMenus($financeId, $companyId), 'code');

        $this->assertContains('purchasing', $purchasingMenus);
        $this->assertContains('inventory', $purchasingMenus);
        $this->assertContains('documents', $purchasingMenus);
        $this->assertNotContains('finance', $purchasingMenus);
        $this->assertContains('finance', $financeMenus);
        $this->assertContains('cashbank', $financeMenus);
        $this->assertNotContains('purchasing', $financeMenus);
    }

    public function testDemoOwnerCanSwitchAcrossCompaniesAndSeedingIsIdempotent(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);
        $this->seed(MultiCompanyDemoSeeder::class);

        $ownerId = $this->demoUserId('owner@demo.pena-erp.test');
        $contexts = (new TenantContextService($this->db, service('session')))->availableContexts($ownerId);

        $this->assertCount(3, $contexts);
        $this->assertSame(3, $this->db->table('companies')->countAllResults());
        $this->assertSame(1, $this->db->table('auth_identities')->where('secret', 'owner@demo.pena-erp.test')->countAllResults());
        $this->assertSame(1, $this->db->table('menus')->where(['company_id' => $contexts[0]['company_id'], 'code' => 'documents'])->countAllResults());
    }

    public function testDemoSeederBuildsIsolatedInventoryMastersAndWarehousePermission(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $warehouseUserId = $this->demoUserId('warehouse@demo.pena-erp.test');
        $reader = new InventoryReadModel();

        $this->seeInDatabase('products', ['company_id' => $penaId, 'sku' => 'ATK-A4-80']);
        $this->seeInDatabase('warehouses', ['company_id' => $penaId, 'code' => 'MAIN']);
        $this->seeInDatabase('product_profiles', ['company_id' => $penaId, 'alternate_code' => 'A4-80']);
        $this->seeInDatabase('product_prices', ['company_id' => $penaId, 'price_type' => 'sales', 'unit_price' => '72500.0000']);
        $this->assertCount(1, $reader->products($penaId));
        $this->assertCount(1, $reader->products($nusaId));
        $this->assertSame('ATK-A4-80', $reader->products($penaId)[0]['sku']);
        $this->assertSame('RTL-SNACK-01', $reader->products($nusaId)[0]['sku']);
        $this->assertSame(1, $this->db->table('products')->where(['company_id' => $penaId, 'sku' => 'ATK-A4-80'])->countAllResults());
        $this->assertTrue((new TenantAuthorizationService())->can($warehouseUserId, $penaId, 'inventory.master.manage'));
    }

    public function testInventoryWritesRejectForeignTenantReferencesAndAuditStatus(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $penaUomId = (int) $this->db->table('units_of_measure')->where(['company_id' => $penaId, 'code' => 'REAM'])->get()->getFirstRow()->id;
        $nusaCategoryId = (int) $this->db->table('product_categories')->where(['company_id' => $nusaId, 'code' => 'RETAIL'])->get()->getFirstRow()->id;
        $nusaBranchId = (int) $this->db->table('branches')->where(['company_id' => $nusaId, 'code' => 'BDG'])->get()->getFirstRow()->id;
        $nusaDepartmentId = (int) $this->db->table('departments')->where(['company_id' => $nusaId, 'branch_id' => $nusaBranchId])->get()->getFirstRow()->id;
        $productId = (int) $this->db->table('products')->where(['company_id' => $penaId, 'sku' => 'ATK-A4-80'])->get()->getFirstRow()->id;
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $writer = new InventoryWriteModel();

        $this->assertFalse($writer->createProduct([
            'company_id'    => $penaId,
            'category_id'   => $nusaCategoryId,
            'sku'           => 'INVALID-CROSS-TENANT',
            'name'          => 'Invalid foreign category',
            'base_uom_id'   => $penaUomId,
            'product_type'  => 'stock',
            'track_lot'     => false,
            'standard_cost' => '1.0000',
            'status'        => 'active',
        ], $actorId));
        $this->assertFalse($writer->createWarehouse([
            'company_id' => $penaId,
            'branch_id'  => $nusaBranchId,
            'department_id' => $nusaDepartmentId,
            'code'       => 'INVALID',
            'name'       => 'Invalid foreign branch',
            'is_active'  => true,
        ], $actorId));
        $this->assertTrue($writer->updateProductStatus($penaId, $productId, 'inactive', $actorId));
        $this->seeInDatabase('products', ['id' => $productId, 'company_id' => $penaId, 'status' => 'inactive']);
        $this->seeInDatabase('audit_logs', ['event_type' => 'PRODUCT_STATUS_UPDATED', 'entity_id' => $productId, 'company_id' => $penaId]);
    }

    public function testHierarchyWritesRejectInactiveOperationalParents(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $branchId = (int) $this->db->table('branches')->where(['company_id' => $penaId, 'code' => 'JKT'])->get()->getFirstRow()->id;
        $departmentId = (int) $this->db->table('departments')->where(['company_id' => $penaId, 'branch_id' => $branchId, 'code' => 'OPS'])->get()->getFirstRow()->id;
        $warehouseId = (int) $this->db->table('warehouses')->where(['company_id' => $penaId, 'code' => 'MAIN'])->get()->getFirstRow()->id;
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');

        $this->db->table('branches')->where('id', $branchId)->update(['status' => 'inactive']);

        $this->assertFalse((new SetupWriteModel())->createDepartment([
            'company_id' => $penaId,
            'branch_id'  => $branchId,
            'code'       => 'CLOSED-SITE',
            'name'       => 'Closed Site Department',
            'status'     => 'active',
        ], $actorId));
        $this->assertFalse((new SetupWriteModel())->createTransactionCode([
            'company_id'    => $penaId,
            'branch_id'     => $branchId,
            'module'        => 'sales',
            'code'          => 'CLOSED',
            'prefix'        => 'CLOSED-',
            'next_number'   => 1,
            'number_length' => 6,
            'reset_rule'    => 'never',
            'status'        => 'active',
        ], $actorId));
        $this->assertFalse((new InventoryWriteModel())->createWarehouse([
            'company_id'    => $penaId,
            'branch_id'     => $branchId,
            'department_id' => $departmentId,
            'code'          => 'CLOSED',
            'name'          => 'Closed Site Warehouse',
            'is_active'     => true,
        ], $actorId));

        $this->db->table('warehouses')->where('id', $warehouseId)->update(['is_active' => false]);

        $this->assertFalse((new InventoryWriteModel())->createLocation([
            'company_id'   => $penaId,
            'warehouse_id' => $warehouseId,
            'branch_id'    => $branchId,
            'code'         => 'CLOSED-BIN',
            'name'         => 'Closed Warehouse Bin',
            'status'       => 'active',
        ], $actorId));
        $this->assertSame(0, $this->db->table('warehouse_bins')->where(['company_id' => $penaId, 'code' => 'CLOSED-BIN'])->countAllResults());
    }

    public function testItemEnrichmentWritesRejectForeignOrInactiveReferencesAndAuditUpdates(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $productId = (int) $this->db->table('products')->where(['company_id' => $penaId, 'sku' => 'ATK-A4-80'])->get()->getFirstRow()->id;
        $warehouseId = (int) $this->db->table('warehouses')->where(['company_id' => $penaId, 'code' => 'MAIN'])->get()->getFirstRow()->id;
        $uomId = (int) $this->db->table('units_of_measure')->where(['company_id' => $penaId, 'code' => 'REAM'])->get()->getFirstRow()->id;
        $currencyId = (int) $this->db->table('currencies')->where(['company_id' => $penaId, 'code' => 'IDR'])->get()->getFirstRow()->id;
        $foreignWarehouseId = (int) $this->db->table('warehouses')->where(['company_id' => $nusaId, 'code' => 'STORE'])->get()->getFirstRow()->id;
        $writer = new InventoryWriteModel();

        $this->assertFalse($writer->saveProductProfile([
            'company_id'           => $penaId,
            'product_id'           => $productId,
            'alternate_code'       => 'BAD-WH',
            'default_warehouse_id' => $foreignWarehouseId,
            'package_uom_id'       => $uomId,
            'status'               => 'active',
        ], $actorId));

        $this->db->table('warehouses')->where('id', $warehouseId)->update(['is_active' => false]);
        $this->assertFalse($writer->saveProductProfile([
            'company_id'           => $penaId,
            'product_id'           => $productId,
            'alternate_code'       => 'BAD-INACTIVE',
            'default_warehouse_id' => $warehouseId,
            'package_uom_id'       => $uomId,
            'status'               => 'active',
        ], $actorId));

        $this->db->table('warehouses')->where('id', $warehouseId)->update(['is_active' => true]);
        $this->assertTrue($writer->saveProductProfile([
            'company_id'           => $penaId,
            'product_id'           => $productId,
            'alternate_code'       => 'A4-UPDATED',
            'default_warehouse_id' => $warehouseId,
            'package_uom_id'       => $uomId,
            'units_per_package'    => '5.000000',
            'status'               => 'active',
        ], $actorId));
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'event_type' => 'PRODUCT_PROFILE_UPDATED']);

        $this->db->table('currencies')->where('id', $currencyId)->update(['status' => 'inactive']);
        $this->assertFalse($writer->createProductPrice([
            'company_id'     => $penaId,
            'product_id'     => $productId,
            'price_type'     => 'purchase',
            'currency_id'    => $currencyId,
            'uom_id'         => $uomId,
            'unit_price'     => '60000.0000',
            'effective_from' => '2026-06-01',
            'status'         => 'active',
        ], $actorId));

        $this->db->table('currencies')->where('id', $currencyId)->update(['status' => 'active']);
        $this->assertTrue($writer->createProductPrice([
            'company_id'     => $penaId,
            'product_id'     => $productId,
            'price_type'     => 'purchase',
            'currency_id'    => $currencyId,
            'uom_id'         => $uomId,
            'unit_price'     => '60000.0000',
            'effective_from' => '2026-06-01',
            'status'         => 'active',
        ], $actorId));
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'event_type' => 'PRODUCT_PRICE_CREATED']);
    }

    public function testSetupMasterSeedProvidesOperationalReferencesPerTenant(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $reader = new SetupReadModel();

        $this->seeInDatabase('countries', ['iso2' => 'ID', 'name' => 'Indonesia']);
        $this->seeInDatabase('departments', ['company_id' => $penaId, 'code' => 'OPS']);
        $this->assertSame(2, $this->db->table('departments')->where('company_id', $penaId)->countAllResults());
        $this->seeInDatabase('tax_codes', ['company_id' => $penaId, 'code' => 'PPN11']);
        $this->assertSame('IDR', $reader->currencies($penaId)[0]['code']);
        $this->assertSame('PPN11', $reader->taxCodes($nusaId)[0]['code']);
        $this->assertSame(1, $this->db->table('addresses')->where(['company_id' => $penaId, 'code' => 'MAIN'])->countAllResults());
        $this->assertSame(2, $this->db->table('transaction_codes')->where(['company_id' => $penaId, 'code' => 'SO'])->countAllResults());
    }

    public function testSetupAndExtendedInventoryWritesProtectTenantReferences(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $nusaBranchId = (int) $this->db->table('branches')->where(['company_id' => $nusaId, 'code' => 'BDG'])->get()->getFirstRow()->id;
        $penaBranchId = (int) $this->db->table('branches')->where(['company_id' => $penaId, 'code' => 'JKT'])->get()->getFirstRow()->id;
        $nusaProductId = (int) $this->db->table('products')->where(['company_id' => $nusaId, 'sku' => 'RTL-SNACK-01'])->get()->getFirstRow()->id;
        $penaTaxId = (int) $this->db->table('tax_codes')->where(['company_id' => $penaId, 'code' => 'PPN11'])->get()->getFirstRow()->id;

        $this->assertFalse((new SetupWriteModel())->createTransactionCode([
            'company_id'    => $penaId,
            'branch_id'     => $nusaBranchId,
            'module'        => 'sales',
            'code'          => 'BAD',
            'prefix'        => 'BAD-',
            'next_number'   => 1,
            'number_length' => 6,
            'reset_rule'    => 'never',
            'status'        => 'active',
        ], $actorId));
        $this->assertFalse((new InventoryWriteModel())->createItemTax([
            'company_id'  => $penaId,
            'product_id'  => $nusaProductId,
            'tax_code_id' => $penaTaxId,
            'usage_type'  => 'sales',
            'status'      => 'active',
        ], $actorId));

        (new SetupWriteModel())->createDepartment([
            'company_id' => $penaId,
            'branch_id'  => $penaBranchId,
            'code'       => 'FIN',
            'name'       => 'Finance',
            'status'     => 'active',
        ], $actorId);
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'event_type' => 'DEPARTMENT_CREATED']);
        $this->assertSame(0, $this->db->table('transaction_codes')->where(['company_id' => $penaId, 'code' => 'BAD'])->countAllResults());
        $this->assertFalse((new SetupWriteModel())->createDepartment([
            'company_id' => $penaId,
            'branch_id'  => $nusaBranchId,
            'code'       => 'BAD-DEPT',
            'name'       => 'Foreign Site Department',
            'status'     => 'active',
        ], $actorId));
    }

    public function testSetupMasterListActionsUpdateAndDeactivateOnlyWithinActiveTenant(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $penaDepartmentId = (int) $this->db->table('departments')->where(['company_id' => $penaId, 'code' => 'OPS'])->get()->getFirstRow()->id;
        $nusaDepartmentId = (int) $this->db->table('departments')->where(['company_id' => $nusaId, 'code' => 'OPS'])->get()->getFirstRow()->id;
        $writer = new SetupWriteModel();

        $this->assertFalse($writer->updateDepartment($penaId, $nusaDepartmentId, ['name' => 'Cross tenant attempt'], $actorId));
        $this->assertTrue($writer->updateDepartment($penaId, $penaDepartmentId, ['name' => 'Operations Updated'], $actorId));
        $this->assertTrue($writer->updateStatus('department', $penaId, $penaDepartmentId, 'inactive', $actorId));
        $this->assertFalse($writer->updateStatus('department', $penaId, $nusaDepartmentId, 'inactive', $actorId));

        $this->seeInDatabase('departments', ['id' => $penaDepartmentId, 'company_id' => $penaId, 'name' => 'Operations Updated', 'status' => 'inactive']);
        $this->seeInDatabase('departments', ['id' => $nusaDepartmentId, 'company_id' => $nusaId, 'name' => 'Operations BDG', 'status' => 'active']);
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'event_type' => 'DEPARTMENT_UPDATED']);
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'event_type' => 'DEPARTMENT_STATUS_UPDATED']);
    }

    public function testCommercialMasterSeedBuildsIsolatedCustomerSupplierReferencesAndMenus(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $purchasingId = $this->demoUserId('purchasing@demo.pena-erp.test');
        $salesId = $this->demoUserId('sales@demo.pena-erp.test');
        $reader = new CommercialReadModel();
        $auth = new TenantAuthorizationService();

        $this->assertCount(1, $reader->customers($penaId));
        $this->assertCount(1, $reader->suppliers($nusaId));
        $this->assertSame('CUS-DEMO', $reader->customers($penaId)[0]['code']);
        $this->assertSame('SUP-DEMO', $reader->suppliers($nusaId)[0]['code']);
        $this->assertCount(2, $reader->customerAddresses($penaId));
        $this->assertCount(1, $reader->supplierPromotions($nusaId));
        $this->assertCount(1, $reader->customerProfiles($penaId));
        $this->assertCount(1, $reader->supplierProfiles($nusaId));
        $this->assertSame('PPN11', $reader->customerProfiles($penaId)[0]['tax_code']);
        $this->assertSame('STORE', $reader->supplierProfiles($nusaId)[0]['warehouse_code']);
        $this->seeInDatabase('customer_addresses', ['company_id' => $penaId, 'address_type' => 'mailing']);
        $this->assertSame(1, $this->db->table('customer_terms')->where(['company_id' => $penaId, 'code' => 'NET30'])->countAllResults());
        $this->assertSame(1, $this->db->table('supplier_terms')->where(['company_id' => $nusaId, 'code' => 'NET14'])->countAllResults());
        $this->assertTrue($auth->can($purchasingId, $penaId, 'purchasing.master.manage'));
        $this->assertTrue($auth->can($salesId, $nusaId, 'sales.master.manage'));
    }

    public function testCommercialWritesRejectForeignTenantReferencesAndAuditCreate(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $penaCurrencyId = (int) $this->db->table('currencies')->where(['company_id' => $penaId, 'code' => 'IDR'])->get()->getFirstRow()->id;
        $nusaTermId = (int) $this->db->table('customer_terms')->where(['company_id' => $nusaId, 'code' => 'NET30'])->get()->getFirstRow()->id;
        $penaAddressId = (int) $this->db->table('addresses')->where(['company_id' => $penaId, 'code' => 'MAIN'])->get()->getFirstRow()->id;
        $nusaCustomerId = (int) $this->db->table('customers')->where(['company_id' => $nusaId, 'code' => 'CUS-DEMO'])->get()->getFirstRow()->id;
        $penaCustomerId = (int) $this->db->table('customers')->where(['company_id' => $penaId, 'code' => 'CUS-DEMO'])->get()->getFirstRow()->id;
        $nusaTaxId = (int) $this->db->table('tax_codes')->where(['company_id' => $nusaId, 'code' => 'PPN11'])->get()->getFirstRow()->id;
        $penaWarehouseId = (int) $this->db->table('warehouses')->where(['company_id' => $penaId, 'code' => 'MAIN'])->get()->getFirstRow()->id;
        $writer = new CommercialWriteModel();

        $this->assertFalse($writer->createCustomer([
            'company_id'      => $penaId,
            'code'            => 'BAD-CUSTOMER',
            'name'            => 'Foreign Terms Customer',
            'currency_id'     => $penaCurrencyId,
            'default_term_id' => $nusaTermId,
            'credit_limit'    => '0.0000',
            'status'          => 'active',
        ], $actorId));
        $this->assertFalse($writer->linkCustomerAddress([
            'company_id'   => $penaId,
            'customer_id'  => $nusaCustomerId,
            'address_id'   => $penaAddressId,
            'address_type' => 'billing',
            'is_default'   => true,
            'status'       => 'active',
        ], $actorId));
        $this->assertFalse($writer->saveCustomerProfile([
            'company_id'           => $penaId,
            'customer_id'          => $penaCustomerId,
            'reference_name'       => 'Invalid Tax Tenant',
            'default_tax_code_id'  => $nusaTaxId,
            'default_warehouse_id' => $penaWarehouseId,
            'status'               => 'active',
        ], $actorId));

        $writer->createCustomerTerm([
            'company_id'    => $penaId,
            'code'          => 'CASH',
            'name'          => 'Cash on Delivery',
            'due_days'      => 0,
            'discount_days' => 0,
            'discount_rate' => '0.000000',
            'status'        => 'active',
        ], $actorId);
        $this->assertTrue($writer->saveCustomerProfile([
            'company_id'           => $penaId,
            'customer_id'          => $penaCustomerId,
            'reference_name'       => 'Updated Reference',
            'contact_name'         => 'Updated Contact',
            'default_tax_code_id'  => null,
            'default_warehouse_id' => $penaWarehouseId,
            'account_manager_name' => 'Updated Sales PIC',
            'quantity_limit'       => '1200.0000',
            'limit_days'           => 20,
            'status'               => 'active',
        ], $actorId));
        $this->seeInDatabase('customer_terms', ['company_id' => $penaId, 'code' => 'CASH']);
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'event_type' => 'CUSTOMER_TERM_CREATED']);
        $this->seeInDatabase('customer_profiles', ['company_id' => $penaId, 'customer_id' => $penaCustomerId, 'reference_name' => 'Updated Reference']);
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'event_type' => 'CUSTOMER_PROFILE_UPDATED']);
        $this->assertSame(0, $this->db->table('customers')->where(['company_id' => $penaId, 'code' => 'BAD-CUSTOMER'])->countAllResults());
    }

    public function testCommercialProfileRejectsInactiveDefaultReferences(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $customerId = (int) $this->db->table('customers')->where(['company_id' => $penaId, 'code' => 'CUS-DEMO'])->get()->getFirstRow()->id;
        $taxId = (int) $this->db->table('tax_codes')->where(['company_id' => $penaId, 'code' => 'PPN11'])->get()->getFirstRow()->id;
        $warehouseId = (int) $this->db->table('warehouses')->where(['company_id' => $penaId, 'code' => 'MAIN'])->get()->getFirstRow()->id;
        $writer = new CommercialWriteModel();
        $profile = [
            'company_id'           => $penaId,
            'customer_id'          => $customerId,
            'reference_name'       => 'Should Not Be Stored',
            'default_tax_code_id'  => $taxId,
            'default_warehouse_id' => $warehouseId,
            'status'               => 'active',
        ];

        $this->db->table('tax_codes')->where('id', $taxId)->update(['status' => 'inactive']);
        $this->assertFalse($writer->saveCustomerProfile($profile, $actorId));

        $this->db->table('tax_codes')->where('id', $taxId)->update(['status' => 'active']);
        $this->db->table('warehouses')->where('id', $warehouseId)->update(['is_active' => false]);
        $this->assertFalse($writer->saveCustomerProfile($profile, $actorId));
        $this->dontSeeInDatabase('customer_profiles', ['company_id' => $penaId, 'reference_name' => 'Should Not Be Stored']);
    }

    public function testPosMasterSeedAndWritesAreTenantScopedAndAudited(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $reader = new PosReadModel();
        $register = $reader->registers($penaId)[0];
        $payment = $reader->paymentMethods($penaId)[0];
        $foreignCustomerId = (int) $this->db->table('customers')->where(['company_id' => $nusaId, 'code' => 'CUS-DEMO'])->get()->getFirstRow()->id;
        $foreignCashBankId = (int) $this->db->table('cash_bank_accounts')->where(['company_id' => $nusaId, 'code' => 'CASH-MAIN'])->get()->getFirstRow()->id;
        $writer = new PosWriteModel();

        $this->assertSame(1, $this->db->table('pos_registers')->where(['company_id' => $penaId, 'code' => 'REG-01'])->countAllResults());
        $this->assertCount(2, $reader->paymentMethods($penaId));
        $this->seeInDatabase('transaction_codes', ['company_id' => $penaId, 'code' => 'POS', 'module' => 'pos']);
        $this->assertContains('pos', array_column((new TenantMenuService($this->db))->accessibleMenus($actorId, $penaId), 'code'));
        $this->assertFalse($writer->createRegister([
            'company_id'          => $penaId,
            'branch_id'           => (int) $register['branch_id'],
            'department_id'       => (int) $register['department_id'],
            'warehouse_id'        => (int) $register['warehouse_id'],
            'default_customer_id' => $foreignCustomerId,
            'currency_id'         => (int) $register['currency_id'],
            'transaction_code_id' => (int) $register['transaction_code_id'],
            'code'                => 'BAD-POS',
            'name'                => 'Invalid Foreign Customer Register',
            'status'              => 'active',
        ], $actorId));
        $this->assertFalse($writer->createPaymentMethod([
            'company_id'           => $penaId,
            'register_id'          => (int) $register['id'],
            'cash_bank_account_id' => $foreignCashBankId,
            'code'                 => 'BADPAY',
            'name'                 => 'Invalid Foreign Bank',
            'payment_type'         => 'transfer',
            'is_default'           => false,
            'sort_order'           => 30,
            'status'               => 'active',
        ], $actorId));
        $this->assertTrue($writer->openShift([
            'company_id'      => $penaId,
            'register_id'     => (int) $register['id'],
            'cashier_user_id' => $actorId,
            'opened_at'       => date('Y-m-d H:i:s'),
            'opening_cash'    => '100000.0000',
        ], $actorId));
        $shift = $reader->shifts($penaId)[0];
        $this->assertFalse($writer->openShift([
            'company_id'      => $penaId,
            'register_id'     => (int) $register['id'],
            'cashier_user_id' => $actorId,
            'opened_at'       => date('Y-m-d H:i:s'),
            'opening_cash'    => '50000.0000',
        ], $actorId));
        $this->assertTrue($writer->closeShift($penaId, (int) $shift['id'], '125000.0000', $actorId));
        $this->assertTrue($writer->updateStatus($penaId, (int) $register['id'], 'inactive', $actorId));
        $this->assertTrue($writer->updatePaymentStatus($penaId, (int) $payment['id'], 'inactive', $actorId));
        $this->seeInDatabase('pos_registers', ['id' => $register['id'], 'status' => 'inactive']);
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'entity_id' => $register['id'], 'event_type' => 'POS_REGISTER_STATUS_UPDATED']);
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'entity_id' => $payment['id'], 'event_type' => 'POS_PAYMENT_METHOD_STATUS_UPDATED']);
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'entity_id' => $shift['id'], 'event_type' => 'POS_SHIFT_OPENED']);
        $this->seeInDatabase('audit_logs', ['company_id' => $penaId, 'entity_id' => $shift['id'], 'event_type' => 'POS_SHIFT_CLOSED']);
    }

    public function testFinanceMasterSeedAndWritesAreTenantScopedAndAudited(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);
        $this->seed(MultiCompanyDemoSeeder::class);

        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $actorId = $this->demoUserId('finance@demo.pena-erp.test');
        $reader = new FinanceReadModel();
        $cashBank = $reader->cashBankAccounts($penaId)[0];
        $foreignCurrency = (int) $this->db->table('currencies')->where(['company_id' => $nusaId, 'code' => 'IDR'])->get()->getFirstRow()->id;

        $this->assertCount(3, $reader->accounts($penaId));
        $this->assertCount(2, $reader->cashBankAccounts($penaId));
        $this->assertCount(1, $reader->exchangeRates($penaId));
        $this->assertContains('finance', array_column((new TenantMenuService($this->db))->accessibleMenus($actorId, $penaId), 'code'));
        $this->assertFalse((new FinanceWriteModel())->createCashBankAccount([
            'company_id'   => $penaId,
            'account_id'   => (int) $cashBank['account_id'],
            'currency_id'  => $foreignCurrency,
            'code'         => 'FOREIGN',
            'name'         => 'Foreign tenant reference',
            'account_type' => 'bank',
            'status'       => 'active',
        ], $actorId));
        $this->assertTrue((new FinanceWriteModel())->updateStatus('cash-bank', $penaId, (int) $cashBank['id'], 'inactive', $actorId));
        $this->seeInDatabase('audit_logs', ['event_type' => 'CASH_BANK_ACCOUNT_STATUS_UPDATED', 'entity_id' => (int) $cashBank['id']]);
    }

    public function testRevokingRolePermissionRemovesMenuAndWritesAuditEvent(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $userId = $this->demoUserId('purchasing@demo.pena-erp.test');
        $roleId = (int) $this->db->table('roles')->where(['company_id' => $companyId, 'code' => 'purchasing'])->get()->getFirstRow()->id;
        $permissionId = (int) $this->db->table('permissions')->where(['company_id' => $companyId, 'code' => 'purchasing.master.view'])->get()->getFirstRow()->id;
        $grantId = (int) $this->db->table('role_permissions')->where([
            'company_id'    => $companyId,
            'role_id'       => $roleId,
            'permission_id' => $permissionId,
        ])->get()->getFirstRow()->id;
        $menuService = new TenantMenuService($this->db);

        $this->assertContains('purchasing', array_column($menuService->accessibleMenus($userId, $companyId), 'code'));
        $this->assertTrue((new AdministrationWriteModel())->revokeRolePermission($companyId, $grantId, $userId));
        $this->assertNotContains('purchasing', array_column($menuService->accessibleMenus($userId, $companyId), 'code'));
        $this->seeInDatabase('audit_logs', [
            'company_id' => $companyId,
            'event_type' => 'ROLE_PERMISSION_REVOKED',
            'entity_id'  => $grantId,
        ]);

        $logs = (new AdministrationReadModel())->auditLogs($companyId, 'ROLE_PERMISSION_REVOKED');
        $this->assertSame('ROLE_PERMISSION_REVOKED', $logs[0]['event_type']);
    }

    public function testInactiveRoleStopsMenusAndIsAudited(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $userId = $this->demoUserId('purchasing@demo.pena-erp.test');
        $roleId = (int) $this->db->table('roles')->where(['company_id' => $companyId, 'code' => 'purchasing'])->get()->getFirstRow()->id;
        $service = new TenantMenuService($this->db);

        $this->assertContains('purchasing', array_column($service->accessibleMenus($userId, $companyId), 'code'));
        $this->assertTrue((new AdministrationWriteModel())->updateRole($roleId, ['name' => 'Purchasing', 'status' => 'inactive'], $userId));
        $this->assertSame([], $service->accessibleMenus($userId, $companyId));
        $this->seeInDatabase('audit_logs', ['company_id' => $companyId, 'event_type' => 'ROLE_UPDATED', 'entity_id' => $roleId]);
    }

    public function testRevokingUserRoleOnlyRemovesItsCompanyContext(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $userId = $this->demoUserId('finance@demo.pena-erp.test');
        $penaId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $nusaId = (int) $this->db->table('companies')->where('code', 'NUSA')->get()->getFirstRow()->id;
        $assignmentId = (int) $this->db->table('user_roles')
            ->select('user_roles.id AS assignment_id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where(['user_roles.company_id' => $nusaId, 'user_roles.user_id' => $userId, 'roles.code' => 'finance'])
            ->get()
            ->getFirstRow()
            ->assignment_id;
        $contextService = new TenantContextService($this->db, service('session'));

        $this->assertCount(2, $contextService->availableContexts($userId));
        $this->assertTrue((new AdministrationWriteModel())->revokeUserRole($nusaId, $assignmentId, $userId));
        $remaining = $contextService->availableContexts($userId);

        $this->assertCount(1, $remaining);
        $this->assertSame($penaId, (int) $remaining[0]['company_id']);
        $this->seeInDatabase('user_company_memberships', ['company_id' => $nusaId, 'user_id' => $userId, 'status' => 'inactive']);
        $this->seeInDatabase('user_company_memberships', ['company_id' => $penaId, 'user_id' => $userId, 'status' => 'active']);
        $this->seeInDatabase('audit_logs', ['company_id' => $nusaId, 'event_type' => 'USER_ROLE_REVOKED', 'entity_id' => $userId]);
    }

    public function testMenuPermissionMatrixExplainsSidebarVisibility(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $matrix = (new AdministrationReadModel())->menuPermissionMatrix();
        $documents = array_values(array_filter(
            $matrix,
            static fn (array $mapping): bool => $mapping['company_code'] === 'PENA' && $mapping['permission_code'] === 'documents.upload',
        ));

        $this->assertNotSame([], $documents);
        $this->assertSame('AI Document Processing', $documents[0]['menu_label']);
    }

    public function testShieldProvisioningCreatesActiveUserWithoutWritingPasswordToAudit(): void
    {
        $actorId = $this->createTestUser('platform-owner', 'platform-owner@example.com');
        $password = 'StrongTemp#2026';

        $userId = (new ShieldUserProvisioningService($this->db))->provision([
            'username' => 'new-staff',
            'email'    => 'new-staff@example.com',
            'password' => $password,
        ], $actorId);

        $this->seeInDatabase('users', ['id' => $userId, 'username' => 'new-staff', 'active' => 1]);
        $identity = $this->db->table('auth_identities')
            ->where(['user_id' => $userId, 'type' => 'email_password'])
            ->get()
            ->getFirstRow('array');
        $audit = $this->db->table('audit_logs')
            ->where(['event_type' => 'USER_PROVISIONED', 'entity_id' => $userId])
            ->get()
            ->getFirstRow('array');

        $this->assertNotNull($identity);
        $this->assertTrue(password_verify($password, $identity['secret2']));
        $this->assertNotNull($audit);
        $this->assertStringNotContainsString($password, (string) $audit['after_json']);
    }

    public function testMenuPermissionMappingAddsAndRemovesRoleVisibleMenu(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $userId = $this->demoUserId('purchasing@demo.pena-erp.test');
        $menuId = (int) $this->db->table('menus')->where(['company_id' => $companyId, 'code' => 'finance'])->get()->getFirstRow()->id;
        $permissionId = (int) $this->db->table('permissions')->where(['company_id' => $companyId, 'code' => 'purchasing.po.view'])->get()->getFirstRow()->id;
        $service = new TenantMenuService($this->db);
        $writer = new AdministrationWriteModel();

        $this->assertNotContains('finance', array_column($service->accessibleMenus($userId, $companyId), 'code'));
        $this->assertTrue($writer->grantMenuPermission($companyId, $menuId, $permissionId, $userId));
        $this->assertContains('finance', array_column($service->accessibleMenus($userId, $companyId), 'code'));

        $mappingId = (int) $this->db->table('menu_permissions')->where([
            'company_id'    => $companyId,
            'menu_id'       => $menuId,
            'permission_id' => $permissionId,
        ])->get()->getFirstRow()->id;
        $this->assertTrue($writer->revokeMenuPermission($companyId, $mappingId, $userId));
        $this->assertNotContains('finance', array_column($service->accessibleMenus($userId, $companyId), 'code'));
        $this->seeInDatabase('audit_logs', ['event_type' => 'MENU_PERMISSION_GRANTED', 'entity_id' => $mappingId]);
        $this->seeInDatabase('audit_logs', ['event_type' => 'MENU_PERMISSION_REVOKED', 'entity_id' => $mappingId]);
    }

    public function testInactiveShieldUserLosesTenantAccessAndPasswordReplacementIsAuditedSafely(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $userId = $this->demoUserId('purchasing@demo.pena-erp.test');
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $auth = new TenantAuthorizationService();
        $menus = new TenantMenuService($this->db);
        $context = new TenantContextService($this->db, service('session'));
        $users = new ShieldUserProvisioningService($this->db);

        $this->assertTrue($auth->can($userId, $companyId, 'purchasing.po.view'));
        $this->assertTrue($users->setActive($userId, false, $actorId));
        $this->assertFalse($auth->can($userId, $companyId, 'purchasing.po.view'));
        $this->assertSame([], $menus->accessibleMenus($userId, $companyId));
        $this->assertSame([], $context->availableContexts($userId));

        $newPassword = 'ReplacedTemp#2026';
        $this->assertTrue($users->setTemporaryPassword($userId, $newPassword, $actorId));
        $identity = $this->db->table('auth_identities')
            ->where(['user_id' => $userId, 'type' => 'email_password'])
            ->get()
            ->getFirstRow('array');
        $passwordAudit = $this->db->table('audit_logs')
            ->where(['event_type' => 'USER_PASSWORD_REPLACED', 'entity_id' => $userId])
            ->get()
            ->getFirstRow('array');

        $this->assertTrue(password_verify($newPassword, $identity['secret2']));
        $this->assertSame(1, (int) $identity['force_reset']);
        $this->assertStringNotContainsString($newPassword, (string) $passwordAudit['after_json']);
        $this->seeInDatabase('audit_logs', ['event_type' => 'USER_STATUS_UPDATED', 'entity_id' => $userId]);
        $this->seeInDatabase('audit_logs', ['event_type' => 'USER_SESSIONS_REVOKED', 'entity_id' => $userId]);
    }

    public function testSuspendedCompanyMembershipRequiresExplicitBranchReactivation(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $userId = $this->demoUserId('purchasing@demo.pena-erp.test');
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $branchMembershipId = (int) $this->db->table('user_branch_memberships')
            ->where(['company_id' => $companyId, 'user_id' => $userId])
            ->get()
            ->getFirstRow()
            ->id;
        $writer = new AdministrationWriteModel();
        $context = new TenantContextService($this->db, service('session'));

        $this->assertNotSame([], $context->availableContexts($userId));
        $this->assertTrue($writer->updateCompanyMembership($companyId, $userId, 'inactive', $actorId));
        $this->assertSame([], $context->availableContexts($userId));
        $this->seeInDatabase('user_branch_memberships', ['id' => $branchMembershipId, 'status' => 'inactive', 'can_switch' => 0]);

        $this->assertTrue($writer->updateCompanyMembership($companyId, $userId, 'active', $actorId));
        $this->assertSame([], $context->availableContexts($userId));
        $this->assertTrue($writer->updateBranchMembership($companyId, $branchMembershipId, 'active', true, $actorId));
        $this->assertNotSame([], $context->availableContexts($userId));
        $this->seeInDatabase('audit_logs', ['event_type' => 'USER_COMPANY_MEMBERSHIP_UPDATED', 'company_id' => $companyId]);
        $this->seeInDatabase('audit_logs', ['event_type' => 'USER_BRANCH_MEMBERSHIP_UPDATED', 'company_id' => $companyId]);
    }

    public function testSessionSecurityVersionRejectsRevokedSessionAndPasswordCompletionClearsReset(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $userId = $this->demoUserId('purchasing@demo.pena-erp.test');
        $actorId = $this->demoUserId('owner@demo.pena-erp.test');
        $session = service('session');
        $security = new UserSessionSecurityService($this->db, $session);
        $users = new ShieldUserProvisioningService($this->db);

        $security->stampLogin($userId);
        $this->assertTrue($security->currentSessionIsValid($userId));
        $this->assertTrue($users->setTemporaryPassword($userId, 'MustReplace#2026', $actorId));
        $this->assertFalse($security->currentSessionIsValid($userId));
        $this->assertTrue($security->requiresPasswordReset($userId));

        $security->stampLogin($userId);
        $this->assertTrue($security->currentSessionIsValid($userId));
        $this->assertTrue($users->setTemporaryPassword($userId, 'CompletedNew#2026', $userId, false));
        $this->assertFalse($security->requiresPasswordReset($userId));
        $this->assertFalse($security->currentSessionIsValid($userId));
    }

    private function createTestUser(string $username, string $email): int
    {
        $this->db->table('users')->insert([
            'username'   => $username,
            'active'     => true,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userId = (int) $this->db->insertID();
        $this->db->table('auth_identities')->insert([
            'user_id'    => $userId,
            'type'       => 'email_password',
            'secret'     => $email,
            'secret2'    => 'not-used',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $userId;
    }

    private function demoUserId(string $email): int
    {
        return (int) $this->db->table('auth_identities')
            ->where(['type' => 'email_password', 'secret' => $email])
            ->get()
            ->getFirstRow()
            ->user_id;
    }

    private function insertForeignCompany(): int
    {
        $this->db->table('companies')->insert([
            'code'          => 'OTHER',
            'name'          => 'Other Tenant',
            'base_currency' => 'IDR',
            'timezone'      => 'Asia/Jakarta',
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }
}
