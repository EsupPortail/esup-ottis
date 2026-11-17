<?php

/**
 * Tests unitaires pour App\Utils\Logger
 *
 * Valide le partage/mise en cache de l'instance Monolog et le pass-through
 * des methodes statiques (niveau + contexte), via un TestHandler injecte :
 * aucune ecriture dans logs/.
 */

namespace OTTIS\Tests\Unit\Utils;

use App\Utils\Logger;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    private TestHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new TestHandler();
        $this->injectLogger(new MonologLogger('ottis-test', [$this->handler]));
    }

    protected function tearDown(): void
    {
        // Rendre l'instance reelle (config/logging.php) aux suites suivantes
        $this->resetLogger();
    }

    private function injectLogger(MonologLogger $logger): void
    {
        $property = new \ReflectionProperty(Logger::class, 'logger');
        $property->setAccessible(true);
        $property->setValue(null, $logger);
    }

    private function resetLogger(): void
    {
        $property = new \ReflectionProperty(Logger::class, 'logger');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testGetReturnsCachedMonologInstance(): void
    {
        $logger = Logger::get();

        $this->assertInstanceOf(MonologLogger::class, $logger);
        $this->assertSame($logger, Logger::get(), 'l instance doit etre mise en cache');
    }

    public function testErrorWritesMessageAndContext(): void
    {
        Logger::error('message-erreur', ['cle' => 'valeur']);

        $this->assertTrue($this->handler->hasError('message-erreur'));
        $record = $this->handler->getRecords()[0];
        $this->assertSame('valeur', $record['context']['cle']);
    }

    public function testWarningInfoDebugWriteAtTheirLevels(): void
    {
        Logger::warning('w');
        Logger::info('i');
        Logger::debug('d');

        $this->assertTrue($this->handler->hasWarning('w'));
        $this->assertTrue($this->handler->hasInfo('i'));
        $this->assertTrue($this->handler->hasDebug('d'));
    }

    public function testLevelsAreMonologLevels(): void
    {
        Logger::error('e');

        $record = $this->handler->getRecords()[0];
        // Les enregistrements Monolog portent la severite numerique :
        // l enum Level n'est expose que sur l API (Logger::error, etc.).
        $this->assertSame(Level::Error->value, $record['level']);
    }
}
