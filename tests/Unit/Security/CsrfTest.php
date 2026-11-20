<?php

/**
 * Tests unitaires pour App\Utils\Csrf
 *
 * Couvre la generation du token, sa validation et les deux sources
 * acceptees pour une requete POST : champ csrf_token et en-tete
 * X-CSRF-Token (utilise par les modules front network/{fetch,xhr}.js).
 */

namespace OTTIS\Tests\Unit\Security;

use App\Utils\Csrf;
use PHPUnit\Framework\TestCase;

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
        $_SESSION = [];
    }

    public static function tearDownAfterClass(): void
    {
        // Rendre la session aux tests d'integration suivants :
        // HealthControllerTest verifie que /health ne demarre pas de session.
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
        }
    }

    public function testGeneratedTokenIsStablePerSession(): void
    {
        $token = Csrf::generateToken();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
        $this->assertSame($token, Csrf::generateToken(), 'le token ne doit pas etre regenere a chaque appel');
        $this->assertSame($token, $_SESSION['csrf_token']);
    }

    public function testGeneratedTokenDiffersPerSession(): void
    {
        $first = Csrf::generateToken();
        $_SESSION = [];
        $second = Csrf::generateToken();

        $this->assertNotSame($first, $second);
    }

    public function testValidateToken(): void
    {
        $token = Csrf::generateToken();

        $this->assertTrue(Csrf::validateToken($token));
        $this->assertFalse(Csrf::validateToken(str_repeat('0', 64)));
        $this->assertFalse(Csrf::validateToken(''));
    }

    public function testPostWithValidBodyTokenPasses(): void
    {
        $_POST['csrf_token'] = Csrf::generateToken();

        $this->assertTrue(Csrf::verifyRequest());
    }

    public function testPostWithoutTokenFails(): void
    {
        Csrf::generateToken();

        $this->assertFalse(Csrf::verifyRequest());
    }

    public function testPostWithInvalidBodyTokenFails(): void
    {
        Csrf::generateToken();
        $_POST['csrf_token'] = 'invalid-token';

        $this->assertFalse(Csrf::verifyRequest());
    }

    public function testPostWithValidHeaderTokenPasses(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = Csrf::generateToken();

        $this->assertTrue(Csrf::verifyRequest(), 'le front envoie le token via X-CSRF-Token sur les requetes JSON');
    }

    public function testPostWithInvalidHeaderTokenFails(): void
    {
        Csrf::generateToken();
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'invalid-token';

        $this->assertFalse(Csrf::verifyRequest());
    }

    public function testGetRequestAlwaysPasses(): void
    {
        Csrf::generateToken();
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->assertTrue(Csrf::verifyRequest());
    }

    public function testFieldRendersHiddenInputWithEscapedToken(): void
    {
        $token = Csrf::generateToken();

        $this->assertSame(
            '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">',
            Csrf::field()
        );
    }
}
