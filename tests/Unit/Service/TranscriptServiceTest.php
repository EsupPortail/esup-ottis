<?php

/**
 * Tests unitaires pour App\Service\TranscriptService
 *
 * Sous-titres : FinalSub_{roomId}_{lang} et VerylastSub_{roomId} crees
 * dans le dossier temporaire systeme, supprimes systematiquement.
 */

namespace OTTIS\Tests\Unit\Service;

use App\Service\TranscriptService;
use PHPUnit\Framework\TestCase;

class TranscriptServiceTest extends TestCase
{
    private const PREFIX = 'svc-test-sub-';

    private TranscriptService $service;
    private static string $room = '';

    public static function setUpBeforeClass(): void
    {
        self::$room = self::PREFIX . bin2hex(random_bytes(3));
    }

    protected function setUp(): void
    {
        $this->service = new TranscriptService();
        @mkdir(sys_get_temp_dir() . '/ottis', 0775, true);
    }

    protected function tearDown(): void
    {
        $dir = sys_get_temp_dir() . '/ottis';
        @unlink($dir . '/FinalSub_' . self::$room . '_fr');
        @unlink($dir . '/VerylastSub_' . self::$room);
    }

    public function testReadFinalLinesRenumbersAndStripsPrefixes(): void
    {
        $file = sys_get_temp_dir() . '/ottis/FinalSub_' . self::$room . '_fr';
        file_put_contents($file, "C;ligne un\n12;ligne deux\n");

        $this->assertSame("0;ligne un\n1;ligne deux", $this->service->readFinalLines(self::$room, 'fr'));
    }

    public function testReadFinalLinesEmptyWhenNoHistory(): void
    {
        $this->assertSame('', $this->service->readFinalLines(self::$room . '-missing', 'fr'));
    }

    public function testReadLastPartialReturnsLastPublishedLine(): void
    {
        $file = sys_get_temp_dir() . '/ottis/VerylastSub_' . self::$room;
        file_put_contents($file, "partiel un\npartiel deux\n");

        $this->assertSame('partiel deux', $this->service->readLastPartial(self::$room));
    }

    public function testReadLastPartialEmptyWhenMissing(): void
    {
        $this->assertSame('', $this->service->readLastPartial(self::$room . '-missing'));
    }
}
