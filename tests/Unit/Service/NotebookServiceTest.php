<?php

/**
 * Tests unitaires pour App\Service\NotebookService
 *
 * Fichiers Link_{roomId} (transcript de la salle) crees dans tmp/ sous un
 * prefixe unique par run, supprimes systematiquement.
 */

namespace OTTIS\Tests\Unit\Service;

use App\Service\NotebookService;
use PHPUnit\Framework\TestCase;

class NotebookServiceTest extends TestCase
{
    private const PREFIX = 'svc-test-notebook-';

    private NotebookService $service;
    private static string $room = '';

    public static function setUpBeforeClass(): void
    {
        self::$room = self::PREFIX . bin2hex(random_bytes(3));
    }

    protected function setUp(): void
    {
        $this->service = new NotebookService();
    }

    protected function tearDown(): void
    {
        @unlink(APP_ROOT . '/tmp/Link_' . self::$room);
    }

    public function testSaveLineAndReadLinesRoundTrip(): void
    {
        $first = $this->service->saveLine(self::$room, 'fr', 'bonjour', 'en', 'hello', 100000);
        $second = $this->service->saveLine(self::$room, 'fr', 'au revoir', 'en', 'goodbye', 100000);

        $this->assertSame(0, $first['lineid']);
        $this->assertSame(1, $second['lineid']);
        $this->assertSame(strlen('bonjour'), $first['saved']);

        $content = $this->service->readLines(self::$room, 0, 7);
        $this->assertStringContainsString('7;0;fr;bonjour;en;hello', $content);
        $this->assertStringContainsString('7;1;fr;au revoir;en;goodbye', $content);
    }

    public function testSaveLineIncrementsLineId(): void
    {
        $this->service->saveLine(self::$room, 'fr', 'ligne 0', '', '', 100000);
        $result = $this->service->saveLine(self::$room, 'fr', 'ligne 1', '', '', 100000);

        $this->assertSame(1, $result['lineid']);
    }

    public function testSaveLineDefaultsTargetToSource(): void
    {
        $this->service->saveLine(self::$room, 'fr', 'texte seul', '', '', 100000);

        $content = (string) file_get_contents(APP_ROOT . '/tmp/Link_' . self::$room);
        $this->assertStringContainsString('0;fr;texte seul;fr;texte seul', $content);
    }

    public function testLastlinesTruncation(): void
    {
        $this->service->saveLine(self::$room, 'fr', 'l0', '', '', 2);
        $this->service->saveLine(self::$room, 'fr', 'l1', '', '', 2);
        $this->service->saveLine(self::$room, 'fr', 'l2', '', '', 2);

        $content = (string) file_get_contents(APP_ROOT . '/tmp/Link_' . self::$room);

        $this->assertStringNotContainsString('l0', $content, 'la plus ancienne ligne doit etre tronquee');
        $this->assertStringContainsString('l1', $content);
        $this->assertStringContainsString('l2', $content);
    }

    public function testReadLinesFromGivenId(): void
    {
        $this->service->saveLine(self::$room, 'fr', 'l0', '', '', 100000);
        $this->service->saveLine(self::$room, 'fr', 'l1', '', '', 100000);

        $this->assertSame('', $this->service->readLines(self::$room, 5, 1));
        $this->assertStringContainsString('1;0;fr;l0', $this->service->readLines(self::$room, 0, 1));
    }

    public function testReadLinesMissingRoomIsEmpty(): void
    {
        $this->assertSame('', $this->service->readLines(self::$room . '-missing', 0, 1));
    }

    public function testReadVoiceOriginalExtractsIdAndSourceText(): void
    {
        $this->service->saveLine(self::$room, 'fr', 'bonjour tout le monde', 'en', 'hello world', 100000);

        $vo = $this->service->readVoiceOriginal(self::$room);

        $this->assertSame('0;bonjour tout le monde', $vo);
    }
}
