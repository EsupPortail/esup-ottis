<?php

/**
 * Tests unitaires pour App\Security\CasAuthenticator
 *
 * Couvre la logique testable sans reseau ni navigateur : detection des
 * routes protegees et publiques, filtrage par affiliations CAS (dont la
 * normalisation casse/espaces et les cas limites), init() sans CAS
 * (marqueurs de session dev) et fusion de la configuration.
 */

namespace OTTIS\Tests\Unit\Security;

use App\Security\CasAuthenticator;
use PHPUnit\Framework\TestCase;

class CasAuthenticatorTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_URI'] = '/conference';
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    /**
     * Invoque une methode privee statique sans modifier la visibilite du code.
     */
    private function invokePrivate(string $method, mixed ...$args): mixed
    {
        $reflection = new \ReflectionMethod(CasAuthenticator::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke(null, ...$args);
    }

    public function testProtectedRoutesRequireCas(): void
    {
        $requires = fn (string $path): bool => $this->invokePrivate('requiresCasAuth', $path);

        $this->assertTrue($requires('/'));
        $this->assertTrue($requires('/conference'));
        $this->assertTrue($requires('/conference/abc123'));

        $this->assertFalse($requires('/auditor'));
        $this->assertFalse($requires('/auditor/xyz'));
        $this->assertFalse($requires('/upload'));
        $this->assertFalse($requires('/health'));
        $this->assertFalse($requires('/text/read'));
    }

    public function testAuditorRoutesArePublic(): void
    {
        $isAuditor = fn (string $path): bool => $this->invokePrivate('isAuditorRoute', $path);

        $this->assertTrue($isAuditor('/auditor'));
        $this->assertTrue($isAuditor('/auditor/xyz'));

        $this->assertFalse($isAuditor('/auditorx'));
        $this->assertFalse($isAuditor('/conference'));
        $this->assertFalse($isAuditor('/'));
    }

    public function testAuthorizedAffiliationsGrantAccess(): void
    {
        $hasAccess = fn (): bool => $this->invokePrivate('casUserHasInternalAccess');

        foreach (['faculty', 'staff', 'employee', 'teacher'] as $affiliation) {
            $_SESSION['cas_attributes'] = ['eduPersonAffiliation' => [$affiliation]];
            $this->assertTrue($hasAccess(), "affiliation $affiliation doit etre autorisee");
        }

        $_SESSION['cas_attributes'] = ['eduPersonAffiliation' => ['student', 'faculty']];
        $this->assertTrue($hasAccess(), 'une affiliation autorisee suffit');
    }

    public function testAffiliationIsNormalized(): void
    {
        $_SESSION['cas_attributes'] = ['eduPersonAffiliation' => ' Faculty '];
        $this->assertTrue($this->invokePrivate('casUserHasInternalAccess'), 'casse et espaces ignores');
    }

    public function testStudentAloneIsRefused(): void
    {
        $_SESSION['cas_attributes'] = ['eduPersonAffiliation' => ['student']];

        $this->assertFalse($this->invokePrivate('casUserHasInternalAccess'));
    }

    public function testEmptyAffiliationsAreSkipped(): void
    {
        $_SESSION['cas_attributes'] = ['eduPersonAffiliation' => [null, '']];

        $this->assertFalse($this->invokePrivate('casUserHasInternalAccess'));
    }

    public function testMissingOrInvalidAttributesAreRefused(): void
    {
        $_SESSION['cas_attributes'] = [];
        $this->assertFalse($this->invokePrivate('casUserHasInternalAccess'));

        $_SESSION['cas_attributes'] = 'pas-un-tableau';
        $this->assertFalse($this->invokePrivate('casUserHasInternalAccess'));

        unset($_SESSION['cas_attributes']);
        $this->assertFalse($this->invokePrivate('casUserHasInternalAccess'));
    }

    public function testInitWithoutCasSetsDevMarkers(): void
    {
        $_SERVER['REQUEST_URI'] = '/conference';

        CasAuthenticator::init(['enabled' => false]);

        $this->assertSame('dev', $_SESSION['auth_provider']);
        $this->assertSame('ottis-dev-user', $_SESSION['cas_user']);
    }

    public function testInitSkipsAuditorRoutes(): void
    {
        $_SERVER['REQUEST_URI'] = '/auditor/xyz';

        CasAuthenticator::init(['enabled' => false]);

        $this->assertArrayNotHasKey('auth_provider', $_SESSION, 'route publique : aucun marqueur');
    }

    public function testLoadConfigMergesDefaults(): void
    {
        $config = CasAuthenticator::loadConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('cert_path', $config);
        $this->assertArrayHasKey('server_validation', $config);
        $this->assertArrayHasKey('protected_routes', $config);
        $this->assertNotNull($config['port']);
    }
}
