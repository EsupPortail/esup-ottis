<?php

namespace App\Controller;

use App\Service\TokenConfigService;

/**
 * HealthController - Endpoint de supervision du service OTTIS
 *
 * GET /health : rapport JSON destine a la supervision (NRPE, Nagios, Centreon...).
 *
 * Codes HTTP :
 * - 200 "ok"       : toutes les verifications passent
 * - 200 "degraded" : service fonctionnel, dependances optionnelles absentes
 * - 503 "fail"     : au moins une verification critique echoue
 *
 * Contraintes : route publique (pas de CAS), pas de session (pas de cookie),
 * aucun appel HTTP externe, aucun secret expose (les tokens ne sortent jamais).
 */
class HealthController extends BaseController
{
    /**
     * Extensions PHP requises (cf. README, section "Extensions PHP requises")
     */
    private const REQUIRED_EXTENSIONS = ['curl', 'dom', 'simplexml', 'xmlreader', 'xmlwriter', 'zip', 'mbstring', 'fileinfo', 'ctype'];

    /**
     * Repertoires de travail requis au deploiement (inscriptibles)
     */
    private const STORAGE_DIRS = ['tmp', 'uploads', 'logs'];

    /**
     * Une sonde de supervision ne doit pas poser de cookie de session.
     */
    protected function initialize(): void
    {
    }

    public function index(): void
    {
        $start = microtime(true);

        $checks = [
            'php' => $this->checkPhp(),
            'extensions' => $this->checkExtensions(),
            'storage' => $this->checkStorage(),
            'tempdir' => $this->checkTempDir(),
            'translation' => $this->checkTranslation(),
            'imagick' => $this->checkImagick(),
            'libreoffice' => $this->checkLibreOffice(),
            'cas' => $this->checkCas(),
        ];

        $status = 'ok';
        $httpCode = 200;
        foreach ($checks as $check) {
            if ($check['status'] === 'fail') {
                $status = 'fail';
                $httpCode = 503;
                break;
            }
            if ($check['status'] === 'warn') {
                $status = 'degraded';
            }
        }

        $this->jsonResponse([
            'status' => $status,
            'environment' => getenv('APP_ENV') ?: 'unknown',
            'timestamp' => date('c'),
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'checks' => $checks,
        ], $httpCode);
    }

    protected function checkPhp(): array
    {
        if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
            return ['status' => 'ok', 'detail' => PHP_VERSION];
        }
        return ['status' => 'fail', 'detail' => 'PHP >= 8.2 requis, version ' . PHP_VERSION . ' detectee'];
    }

    protected function checkExtensions(): array
    {
        $missing = [];
        foreach (self::REQUIRED_EXTENSIONS as $extension) {
            if (!extension_loaded($extension)) {
                $missing[] = $extension;
            }
        }
        if ($missing === []) {
            return ['status' => 'ok', 'detail' => 'toutes les extensions requises chargees'];
        }
        return ['status' => 'fail', 'detail' => 'extensions manquantes : ' . implode(', ', $missing)];
    }

    protected function checkStorage(): array
    {
        $root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2);
        $problems = [];
        foreach (self::STORAGE_DIRS as $dir) {
            $path = $root . '/' . $dir;
            if (!is_dir($path)) {
                $problems[] = $dir . '/ absent';
            } elseif (!is_writable($path)) {
                $problems[] = $dir . '/ non inscriptible';
            }
        }
        if ($problems === []) {
            return ['status' => 'ok', 'detail' => 'tmp/, uploads/, logs/ presents et inscriptibles'];
        }
        return ['status' => 'fail', 'detail' => implode(' ; ', $problems)];
    }

    protected function checkTempDir(): array
    {
        $dir = rtrim(sys_get_temp_dir(), '/') . '/ottis';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['status' => 'fail', 'detail' => 'impossible de creer ' . $dir];
        }
        if (!is_writable($dir)) {
            return ['status' => 'fail', 'detail' => $dir . ' non inscriptible'];
        }
        return ['status' => 'ok', 'detail' => $dir];
    }

    protected function checkTranslation(): array
    {
        try {
            $service = $this->createTokenConfigService();
        } catch (\RuntimeException $e) {
            return ['status' => 'fail', 'detail' => $e->getMessage()];
        }

        $translator = $service->getWhichTranslator();
        if ($translator === 'LIBRETRANSLATE') {
            return ['status' => 'ok', 'detail' => 'traducteur LIBRETRANSLATE (' . $service->getLibreTranslateServer() . ')'];
        }

        $keyByTranslator = [
            'DEEPLFREE' => 'DEEPLFREETOKENS',
            'DEEPLPRO' => 'DEEPLPROTOKENS',
            'GOOGLE' => 'GOOGLETOKENS',
        ];
        $tokens = $service->getAllTokens();
        $list = $tokens[$keyByTranslator[$translator] ?? ''] ?? [];
        $count = is_array($list) ? count($list) : 0;
        if ($count === 0) {
            return ['status' => 'fail', 'detail' => 'aucun token configure pour le traducteur ' . $translator];
        }
        return ['status' => 'ok', 'detail' => $translator . ' : ' . $count . ' token(s) configure(s)'];
    }

    /**
     * Fabrique du service de tokens : protected pour etre substituee en test
     * (injection d'un tokens.json invalide sans toucher a la configuration reelle).
     */
    protected function createTokenConfigService(): TokenConfigService
    {
        return new TokenConfigService();
    }

    protected function checkImagick(): array
    {
        if (extension_loaded('imagick')) {
            return ['status' => 'ok', 'detail' => 'extension imagick chargee'];
        }
        $convert = trim((string) @shell_exec('command -v convert 2>/dev/null'));
        if ($convert !== '') {
            return ['status' => 'ok', 'detail' => 'fallback ImageMagick CLI (' . $convert . ')'];
        }
        return ['status' => 'warn', 'detail' => 'imagick absent et binaire convert introuvable : conversion PDF -> images indisponible'];
    }

    protected function checkLibreOffice(): array
    {
        $soffice = trim((string) @shell_exec('command -v soffice 2>/dev/null'));
        if ($soffice !== '') {
            return ['status' => 'ok', 'detail' => $soffice];
        }
        return ['status' => 'warn', 'detail' => 'soffice introuvable : conversion PPTX -> PDF et ODP -> PPTX indisponible'];
    }

    protected function checkCas(): array
    {
        $enabled = filter_var(
            trim((string) ($_ENV['CAS_ENABLED'] ?? getenv('CAS_ENABLED'))) ?: false,
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$enabled) {
            return ['status' => 'ok', 'detail' => 'CAS desactive'];
        }
        $host = trim((string) ($_ENV['CAS_HOST'] ?? getenv('CAS_HOST')));
        if ($host === '') {
            return ['status' => 'warn', 'detail' => 'CAS active sans CAS_HOST : authentification non fonctionnelle'];
        }
        return ['status' => 'ok', 'detail' => 'CAS active (' . $host . ')'];
    }
}
