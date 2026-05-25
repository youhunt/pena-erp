<?php

declare(strict_types=1);

use App\Database\Seeds\DevelopmentFoundationSeeder;
use App\Authorization\TenantAuthorizationService;
use App\Services\RegionImportService;
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
        $this->seeInDatabase('villages', ['code' => '31.73.01.1001', 'name' => 'Cengkareng Barat']);
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
        $this->assertSame(1, $this->db->table('villages')->where('code', '31.73.01.1001')->countAllResults());
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
}
