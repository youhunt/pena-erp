<?php

declare(strict_types=1);

use App\Database\Seeds\DevelopmentFoundationSeeder;
use App\Authorization\TenantAuthorizationService;
use App\Services\RegionImportService;
use App\Services\RegionApiSyncService;
use App\Services\TenantContextService;
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
