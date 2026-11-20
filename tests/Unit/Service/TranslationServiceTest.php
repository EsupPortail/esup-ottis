<?php

/**
 * Tests unitaires pour App\Service\TranslationService
 *
 * Couvre les fonctions pures du pipeline (accents, extraction) et le
 * cache par langue (FinalSub/PartialSub). Aucun appel reseau : seule la
 * lecture du cache est exercee.
 */

namespace OTTIS\Tests\Unit\Service;

use App\Service\TranslationService;
use PHPUnit\Framework\TestCase;

class TranslationServiceTest extends TestCase
{
    private const PREFIX = 'svc-test-tr-';

    private TranslationService $service;
    private static string $room = '';

    public static function setUpBeforeClass(): void
    {
        self::$room = self::PREFIX . bin2hex(random_bytes(3));
    }

    protected function setUp(): void
    {
        $this->service = new TranslationService();
        @mkdir(sys_get_temp_dir() . '/ottis', 0775, true);
    }

    protected function tearDown(): void
    {
        $dir = sys_get_temp_dir() . '/ottis';
        @unlink($dir . '/FinalSub_' . self::$room . '_fr');
        @unlink($dir . '/FinalSub_' . self::$room . '_en');
        @unlink($dir . '/PartialSub_' . self::$room . '_fr');
    }

    public function testRemoveAccents(): void
    {
        $this->assertSame('ACEace', $this->service->removeAccents('ÀÇÉace'));
    }

    public function testExtractTranslatedTextHandlesAllApiShapes(): void
    {
        $this->assertSame('Hello', $this->service->extractTranslatedText('{"translations":[{"text":"Hello"}]}'));
        $this->assertSame('Hi', $this->service->extractTranslatedText('{"data":{"translations":[{"text":"Hi"}]}}'));
        $this->assertSame('Yo', $this->service->extractTranslatedText('{"translatedText":"Yo"}'));
        $this->assertSame('', $this->service->extractTranslatedText('pas du json'));
    }

    public function testReadFromFinalCacheByLineid(): void
    {
        file_put_contents(sys_get_temp_dir() . '/ottis/FinalSub_' . self::$room . '_fr', "5;bonjour\n");

        $result = $this->service->readFromTranslationFiles(self::$room, '5', 'fr');

        $decoded = json_decode($result, true);
        $this->assertSame('bonjour', $decoded['translations'][0]['text']);
    }

    public function testReadFromPartialCacheBySourceHash(): void
    {
        file_put_contents(
            sys_get_temp_dir() . '/ottis/PartialSub_' . self::$room . '_fr',
            md5('texte source') . ';coucou'
        );

        $result = $this->service->readFromTranslationFiles(self::$room, 'DIRECT', 'fr', 'texte source');

        $decoded = json_decode($result, true);
        $this->assertSame('coucou', $decoded['translations'][0]['text']);
    }

    public function testEnglishCacheRemovesAccents(): void
    {
        file_put_contents(sys_get_temp_dir() . '/ottis/FinalSub_' . self::$room . '_en', "3;CAFÉ\n");

        $result = $this->service->readFromTranslationFiles(self::$room, '3', 'en');

        $decoded = json_decode($result, true);
        $this->assertSame('CAFE', $decoded['translations'][0]['text']);
    }

    public function testCacheMissReturnsEmptyString(): void
    {
        $this->assertSame('', $this->service->readFromTranslationFiles(self::$room . '-missing', '7', 'fr'));
    }
}
