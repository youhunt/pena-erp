<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AuthUiTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = [
        'CodeIgniter\Settings',
        'CodeIgniter\Shield',
    ];

    public function testLoginPageUsesPenaSkoteShell(): void
    {
        $response = $this->get('login');

        $response->assertOK();
        $response->assertSee('Pena ERP');
        $response->assertSee('assets/css/app.min.css');
        $response->assertSee('name="email"');
        $response->assertSee('login/magic-link');
    }

    public function testMagicLinkPageUsesPenaSkoteShell(): void
    {
        $response = $this->get('login/magic-link');

        $response->assertOK();
        $response->assertSee('Tautan masuk');
        $response->assertSee('name="email"');
    }

    public function testDashboardRequiresAuthenticatedSession(): void
    {
        $response = $this->get('/');

        $response->assertRedirect();
        $response->assertRedirectTo(site_url('login'));
    }

    public function testAdministrationRequiresAuthenticatedSession(): void
    {
        $response = $this->get('/administration/companies');

        $response->assertRedirect();
        $response->assertRedirectTo(site_url('login'));
    }
}
