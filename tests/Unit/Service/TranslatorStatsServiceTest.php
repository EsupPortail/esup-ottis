<?php

/**
 * Tests unitaires pour App\Service\TranslatorStatsService
 *
 * Non-regression du chantier securite : le token DeepL ne doit jamais
 * apparaitre sur la ligne de commande du processus curl (lisible dans
 * /proc/<pid>/cmdline), ni dans l'URL appelee.
 */

namespace OTTIS\Tests\Unit\Service;

use App\Service\TranslatorStatsService;
use PHPUnit\Framework\TestCase;

class TranslatorStatsServiceTest extends TestCase
{
    public function testBuildCurlCommandSendsCredentialsViaStdin(): void
    {
        $command = TranslatorStatsService::buildCurlCommand('https://api.deepl.com/v2/usage');

        $this->assertStringContainsString('-K -', $command, 'la configuration curl (avec le token) doit passer par stdin');
        $this->assertStringContainsString("'https://api.deepl.com/v2/usage'", $command);
        $this->assertStringContainsString('-m 10', $command, 'un appel bloque ne doit pas geler la page');
    }

    public function testBuildCurlCommandEscapesEndpointAndProxy(): void
    {
        $malicious = "https://example.fr'; rm -rf /";
        $command = TranslatorStatsService::buildCurlCommand($malicious, 'p.corp:3128');

        $this->assertSame(
            'curl -q -sS -m 10 -K - ' . escapeshellarg($malicious) . ' -x ' . escapeshellarg('p.corp:3128'),
            $command
        );
    }

    public function testBuildCurlCommandOmitsProxyWhenEmpty(): void
    {
        $command = TranslatorStatsService::buildCurlCommand('https://api.deepl.com/v2/usage');

        $this->assertStringNotContainsString(' -x ', $command);
    }

    public function testBuildAuthConfigCarriesHeaderWithoutInjection(): void
    {
        $config = TranslatorStatsService::buildAuthConfig('ab"cd\ef:36');

        $this->assertStringContainsString('Authorization: DeepL-Auth-Key abcdef:36', $config);
        $this->assertStringNotContainsString('ab"cd', $config, 'un guillemet ne doit pas permettre d\'injecter une directive curl');
    }

    public function testNormalizeProxyHandlesLegacyPrefix(): void
    {
        $this->assertSame('', TranslatorStatsService::normalizeProxy(''));
        $this->assertSame('', TranslatorStatsService::normalizeProxy('  '));
        $this->assertSame('http://p:3128', TranslatorStatsService::normalizeProxy('curl -x http://p:3128'));
        $this->assertSame('http://p:3128', TranslatorStatsService::normalizeProxy(' http://p:3128 '));
    }

    public function testEmptyTokenFetchesNothing(): void
    {
        $service = new TranslatorStatsService();

        $this->assertSame([], $service->getDeepLProStats(''));
        $this->assertSame([], $service->getDeepLFreeStats(''));
    }

    public function testUnknownTranslatorReturnsEmptyStats(): void
    {
        $result = (new TranslatorStatsService())->getTranslatorStats('GOOGLE', 'some-token');

        $this->assertSame(['stats' => [], 'percentage_used' => 0], $result);
    }
}
