<?php

/**
 * Tests unitaires pour App\Service\Router
 *
 * Verifie la resolution des routes (exacte, avec parametres, methode,
 * query string) et l'exposition du chemin de configuration matche,
 * utilise par le front controller pour le controle CSRF central.
 */

namespace OTTIS\Tests\Unit\Core;

use App\Service\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/conference/test-room';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    public function testExactMatchReturnsHandlerAndConfigPath(): void
    {
        $_SERVER['REQUEST_URI'] = '/health';

        $router = new Router();
        $router->get('/health', 'HealthController', 'index');

        $this->assertSame(['HealthController', 'index', []], $router->match());
        $this->assertSame('/health', $router->getMatchedPath());
    }

    public function testParamMatchKeepsConfigPath(): void
    {
        $router = new Router();
        $router->get('/conference/{roomId}', 'ConferenceController', 'show');

        $this->assertSame(
            ['ConferenceController', 'show', ['roomId' => 'test-room']],
            $router->match()
        );
        $this->assertSame('/conference/{roomId}', $router->getMatchedPath());
    }

    public function testMethodMismatchDoesNotMatch(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/health';

        $router = new Router();
        $router->get('/health', 'HealthController', 'index');

        $this->assertNull($router->match());
        $this->assertNull($router->getMatchedPath());
    }

    public function testQueryStringIsIgnored(): void
    {
        $_SERVER['REQUEST_URI'] = '/health?probe=1';

        $router = new Router();
        $router->get('/health', 'HealthController', 'index');

        $this->assertNotNull($router->match());
        $this->assertSame('/health', $router->getMatchedPath());
    }

    public function testUnknownPathDoesNotMatch(): void
    {
        $router = new Router();
        $router->get('/health', 'HealthController', 'index');

        $this->assertNull($router->match());
        $this->assertNull($router->getMatchedPath());
    }
}
