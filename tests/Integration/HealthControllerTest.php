<?php

/**
 * Integration tests for the health endpoint (GET /health)
 *
 * Verifie les contrats de supervision du service :
 * - 200 "ok"/"degraded" quand le service est fonctionnel
 * - 503 "fail" des qu'une verification critique echoue
 * - 200 "degraded" (et non 503) si seules des dependances optionnelles manquent
 * - aucun token expose dans la reponse (route publique non authentifiee)
 * - aucun cookie de session pose (sonde NRPE)
 * - cablage route (config/routes.php) et front controller (public/index.php)
 */

namespace OTTIS\Tests\Integration;

use App\Controller\HealthController;
use App\Service\Router;
use App\Service\TokenConfigService;
use PHPUnit\Framework\TestCase;

class HealthControllerTest extends TestCase
{
    private const REPO_ROOT = __DIR__ . '/../..';
    private const EXPECTED_CHECKS = ['php', 'extensions', 'storage', 'tempdir', 'translation', 'imagick', 'libreoffice', 'cas'];

    private string $badTokensFile = '';

    protected function tearDown(): void
    {
        if ($this->badTokensFile !== '' && is_file($this->badTokensFile)) {
            unlink($this->badTokensFile);
            $this->badTokensFile = '';
        }
    }

    public function testNominalReturnsWithExpectedStructure(): void
    {
        if (!is_readable(self::REPO_ROOT . '/tables/tokens.json')) {
            $this->markTestSkipped('tables/tokens.json absent : configuration de traduction non initialisee');
        }

        [$data, $code] = $this->runIndex();

        $this->assertSame(200, $code);
        $this->assertContains($data['status'], ['ok', 'degraded'], 'un service fonctionnel ne doit jamais renvoyer fail');
        $this->assertSame(self::EXPECTED_CHECKS, array_keys($data['checks']));
        $this->assertIsFloat($data['duration_ms']);
        $this->assertGreaterThanOrEqual(0.0, $data['duration_ms']);

        foreach ($data['checks'] as $name => $check) {
            $this->assertIsArray($check, "check $name doit etre un tableau");
            $this->assertContains($check['status'], ['ok', 'warn', 'fail'], "statut inconnu pour $name");
            $this->assertArrayHasKey('detail', $check, "detail manquant pour $name");
        }
    }

    public function testCriticalFailureReturns503AndFailStatus(): void
    {
        $controller = $this->makeController();
        $controller->overrideStorage = fn (): array => ['status' => 'fail', 'detail' => 'tmp/ non inscriptible'];

        [$data, $code] = $controller->runIndex();

        $this->assertSame(503, $code, 'un echec critique doit renvoyer 503 pour declencher la sonde');
        $this->assertSame('fail', $data['status']);
        $this->assertSame('fail', $data['checks']['storage']['status']);
    }

    public function testOptionalDependencyMissingReturnsDegradedButNotFail(): void
    {
        $controller = $this->makeController();
        $controller->overrideImagick = fn (): array => ['status' => 'warn', 'detail' => 'imagick absent'];
        $controller->overrideLibreOffice = fn (): array => ['status' => 'warn', 'detail' => 'soffice absent'];

        [$data, $code] = $controller->runIndex();

        $this->assertSame(200, $code, 'une dependance optionnelle absente ne doit pas declencher 503');
        $this->assertSame('degraded', $data['status']);
        $this->assertSame('warn', $data['checks']['imagick']['status']);
    }

    public function testInvalidTokensJsonFailsCriticalCheck(): void
    {
        $this->badTokensFile = tempnam(sys_get_temp_dir(), 'ottis-health-test-');
        file_put_contents($this->badTokensFile, '{ json invalide');

        $controller = $this->makeController();
        $controller->tokenServiceFactory = fn (): TokenConfigService => new TokenConfigService($this->badTokensFile);

        [$data, $code] = $controller->runIndex();

        $this->assertSame(503, $code, 'tokens.json invalide doit faire echouer le check critique translation');
        $this->assertSame('fail', $data['checks']['translation']['status']);
    }

    public function testNoSecretExposedInResponse(): void
    {
        $tokensFile = self::REPO_ROOT . '/tables/tokens.json';
        if (!is_readable($tokensFile)) {
            $this->markTestSkipped('tables/tokens.json absent');
        }
        $tokens = json_decode((string) file_get_contents($tokensFile), true) ?: [];

        [$data] = $this->runIndex();
        $body = (string) json_encode($data);

        foreach (['GOOGLETOKENS', 'DEEPLFREETOKENS', 'DEEPLPROTOKENS'] as $key) {
            $this->assertStringNotContainsString($key, $body, "le nom de cle $key ne doit pas apparaitre dans /health");
            $values = array_filter(
                (array) ($tokens[$key] ?? []),
                static fn ($token): bool => is_string($token) && trim($token) !== ''
            );
            foreach ($values as $token) {
                $this->assertStringNotContainsString((string) $token, $body, 'un token API ne doit jamais fuiter dans /health');
            }
        }
    }

    public function testNoSessionCookieStarted(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            $this->markTestSkipped('session deja demarree par un autre test');
        }

        $this->runIndex();

        $this->assertSame(PHP_SESSION_NONE, session_status(), '/health ne doit pas demarrer de session');
    }

    public function testRouteIsWiredInRoutesConfig(): void
    {
        $routes = require self::REPO_ROOT . '/config/routes.php';

        $this->assertArrayHasKey('/health', $routes);
        $this->assertSame(HealthController::class, $routes['/health']['controller']);
        $this->assertSame('index', $routes['/health']['action']);
        $this->assertContains('GET', $routes['/health']['methods']);
    }

    public function testRouteIsTreatedAsApiByFrontController(): void
    {
        $index = (string) file_get_contents(self::REPO_ROOT . '/public/index.php');

        $this->assertStringContainsString(
            "str_starts_with(\$requestUri, '/health')",
            $index,
            'si ce cablage disparait, BASE_PATH est injecte avant le JSON'
        );
    }

    /**
     * @return array{0: array, 1: int} [reponse JSON, code HTTP]
     */
    private function runIndex(): array
    {
        return $this->makeController()->runIndex();
    }

    private function makeController(): CapturingHealthController
    {
        return new CapturingHealthController(new Router());
    }
}

/**
 * HealthController instrumente pour le test : jsonResponse capture la
 * reponse au lieu d'appeler exit(), et les closures publiques permettent
 * d'injecter des etats de check impossibles a produire autrement.
 */
final class CapturingHealthController extends HealthController
{
    /** @var array<int, array{0: array, 1: int}> */
    private array $responses = [];

    /** @var (\Closure(): array)|null */
    public ?\Closure $overrideStorage = null;
    /** @var (\Closure(): array)|null */
    public ?\Closure $overrideImagick = null;
    /** @var (\Closure(): array)|null */
    public ?\Closure $overrideLibreOffice = null;
    /** @var (\Closure(): TokenConfigService)|null */
    public ?\Closure $tokenServiceFactory = null;

    protected function jsonResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        $this->responses[] = [$data, $statusCode];
    }

    protected function checkStorage(): array
    {
        return $this->overrideStorage !== null ? ($this->overrideStorage)() : parent::checkStorage();
    }

    protected function checkImagick(): array
    {
        return $this->overrideImagick !== null ? ($this->overrideImagick)() : parent::checkImagick();
    }

    protected function checkLibreOffice(): array
    {
        return $this->overrideLibreOffice !== null ? ($this->overrideLibreOffice)() : parent::checkLibreOffice();
    }

    protected function createTokenConfigService(): TokenConfigService
    {
        return $this->tokenServiceFactory !== null
            ? ($this->tokenServiceFactory)()
            : parent::createTokenConfigService();
    }

    /**
     * @return array{0: array, 1: int}
     */
    public function runIndex(): array
    {
        $this->index();
        /** @var array{0: array, 1: int} */
        return $this->responses[0] ?? [[], 0];
    }
}
