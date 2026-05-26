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
use App\Models\AdministrationReadModel;
use App\Models\AdministrationWriteModel;
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

    public function testRevokingRolePermissionRemovesMenuAndWritesAuditEvent(): void
    {
        $this->seed(MultiCompanyDemoSeeder::class);

        $companyId = (int) $this->db->table('companies')->where('code', 'PENA')->get()->getFirstRow()->id;
        $userId = $this->demoUserId('purchasing@demo.pena-erp.test');
        $roleId = (int) $this->db->table('roles')->where(['company_id' => $companyId, 'code' => 'purchasing'])->get()->getFirstRow()->id;
        $permissionId = (int) $this->db->table('permissions')->where(['company_id' => $companyId, 'code' => 'purchasing.po.view'])->get()->getFirstRow()->id;
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
