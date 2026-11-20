<?php

/**
 * Tests unitaires pour App\Error\ErrorHandler
 *
 * Couvre la part testable en CLI : detection de l environnement,
 * handleError (respect de error_reporting, contexte enrichi, mapping
 * niveau PHP -> niveau Monolog). renderErrorPage/handleShutdown
 * sortent par exit() : hors perimetre des tests automatises.
 */

namespace OTTIS\Tests\Unit\Error;

use App\Error\ErrorHandler;
use App\Utils\Logger;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
    private TestHandler $handler;
    private string|false $previousEnv;
    private int $previousReporting;

    protected function setUp(): void
    {
        $this->previousEnv = getenv('APP_ENV');
        $this->previousReporting = error_reporting();

        $this->handler = new TestHandler();
        $property = new \ReflectionProperty(Logger::class, 'logger');
        $property->setAccessible(true);
        $property->setValue(null, new MonologLogger('ottis-test', [$this->handler]));

        putenv('APP_ENV=development');
        error_reporting(E_ALL);
    }

    protected function tearDown(): void
    {
        if ($this->previousEnv === false) {
            putenv('APP_ENV');
        } else {
            putenv('APP_ENV=' . $this->previousEnv);
        }
        error_reporting($this->previousReporting);

        $property = new \ReflectionProperty(Logger::class, 'logger');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testIsProductionReadsEnvironment(): void
    {
        putenv('APP_ENV=production');
        $this->assertTrue(ErrorHandler::isProduction());

        putenv('APP_ENV=development');
        $this->assertFalse(ErrorHandler::isProduction());
    }

    public function testHandleErrorReturnsFalseAndLogsEnrichedContext(): void
    {
        $result = ErrorHandler::handleError(E_WARNING, 'alerte-test', 'fichier.php', 42);

        $this->assertFalse($result, 'hors production, PHP doit gerer l affichage');
        $this->assertTrue($this->handler->hasWarning('alerte-test'));

        $record = $this->handler->getRecords()[0];
        $this->assertSame('alerte-test', $record['context']['error_message']);
        $this->assertSame('fichier.php', $record['context']['file']);
        $this->assertSame(42, $record['context']['line']);
    }

    public function testHandleErrorRespectsSuppressedReporting(): void
    {
        error_reporting(E_ALL & ~E_WARNING);

        $result = ErrorHandler::handleError(E_WARNING, 'supprimee', 'fichier.php', 1);

        $this->assertFalse($result);
        $this->assertFalse($this->handler->hasWarning('supprimee'), 'niveau exclu : rien ne doit etre journalise');
    }

    public function testPhpErrorLevelMapsToMonologLevel(): void
    {
        ErrorHandler::handleError(E_ERROR, 'fatale', 'f.php', 1);
        ErrorHandler::handleError(E_WARNING, 'alerte', 'f.php', 2);
        ErrorHandler::handleError(E_NOTICE, 'notice', 'f.php', 3);
        ErrorHandler::handleError(E_DEPRECATED, 'obsolete', 'f.php', 4);

        $expected = [
            ['fatale', Level::Critical],
            ['alerte', Level::Warning],
            ['notice', Level::Notice],
            ['obsolete', Level::Info],
        ];
        foreach ($this->handler->getRecords() as $index => $record) {
            // Les enregistrements Monolog portent la severite numerique
            // (500 = Critical), l enum Level n'est que sur l API.
            $this->assertSame($expected[$index][1]->value, $record['level'], $expected[$index][0]);
        }
    }
}
