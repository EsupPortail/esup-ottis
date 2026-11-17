<?php

namespace App\Service;

/**
 * TranslatorStatsService - Service for retrieving translator API statistics
 *
 * This service handles fetching usage statistics from various translation APIs
 * such as DeepL (both Pro and Free versions).
 */
class TranslatorStatsService
{
    /** Timeout curl pour les appels de statistiques (secondes) */
    private const CURL_TIMEOUT_SECONDS = 10;

    /**
     * @var string The proxy URL (empty string if not configured)
     */
    private string $proxy;

    /**
     * Constructor
     *
     * @param string|null $proxy Proxy URL, or legacy 'curl -x <url>' command prefix
     */
    public function __construct(?string $proxy = null)
    {
        $this->proxy = self::normalizeProxy($proxy ?? (getenv('HTTP_PROXY') ?: ''));
    }

    /**
     * Extract a proxy URL from the forms used around the codebase:
     * '' (no proxy), a bare proxy URL, or the legacy 'curl -x <url>' prefix.
     *
     * @return string Proxy URL, or empty string if none
     */
    public static function normalizeProxy(string $proxy): string
    {
        $proxy = trim($proxy);
        if (str_starts_with($proxy, 'curl -x ')) {
            $proxy = trim(substr($proxy, strlen('curl -x ')));
        }

        return $proxy;
    }

    /**
     * Build the curl command line. Takes no token on purpose: credentials
     * travel via stdin (-K -), never on the command line (/proc/<pid>/cmdline).
     *
     * @param string $endpoint DeepL usage endpoint URL
     * @param string $proxy Proxy URL, or empty string for a direct call
     * @return string Shell command line
     */
    public static function buildCurlCommand(string $endpoint, string $proxy = ''): string
    {
        $command = 'curl -q -sS -m ' . self::CURL_TIMEOUT_SECONDS . ' -K - ' . escapeshellarg($endpoint);
        if ($proxy !== '') {
            $command .= ' -x ' . escapeshellarg($proxy);
        }

        return $command;
    }

    /**
     * Build the curl configuration sent on stdin. Quotes and backslashes are
     * stripped from the token: a corrupted value cannot inject another
     * curl directive (e.g. url or output).
     *
     * @param string $apiToken DeepL API token
     * @return string curl configuration (header directive)
     */
    public static function buildAuthConfig(string $apiToken): string
    {
        $safeToken = str_replace(['\\', '"'], '', $apiToken);

        return 'header = "Authorization: DeepL-Auth-Key ' . $safeToken . '"' . "\n";
    }

    /**
     * Get DeepL Pro API usage statistics
     *
     * @param string $apiToken DeepL Pro API token
     * @return array Usage statistics or empty array on error
     */
    public function getDeepLProStats(string $apiToken): array
    {
        if (empty($apiToken)) {
            return [];
        }

        return $this->fetchUsage('https://api.deepl.com/v2/usage', $apiToken);
    }

    /**
     * Get DeepL Free API usage statistics
     *
     * @param string $apiToken DeepL Free API token
     * @return array Usage statistics or empty array on error
     */
    public function getDeepLFreeStats(string $apiToken): array
    {
        if (empty($apiToken)) {
            return [];
        }

        return $this->fetchUsage('https://api-free.deepl.com/v2/usage', $apiToken);
    }

    /**
     * Fetch a DeepL usage endpoint with the credentials sent to curl on
     * stdin: the token never appears in the process arguments nor in a URL.
     *
     * @return array Decoded statistics, or empty array on error
     */
    private function fetchUsage(string $endpoint, string $apiToken): array
    {
        $process = proc_open(self::buildCurlCommand($endpoint, $this->proxy), [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (!is_resource($process)) {
            return [];
        }

        fwrite($pipes[0], self::buildAuthConfig($apiToken));
        fclose($pipes[0]);

        $output = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        if (proc_close($process) !== 0 || trim($output) === '') {
            return [];
        }

        try {
            $stats = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
            return is_array($stats) ? $stats : [];
        } catch (\JsonException) {
            return [];
        }
    }

    /**
     * Get translator statistics based on configuration
     *
     * @param string $whichTranslator Which translator to use ('DEEPLPRO' or 'DEEPLFREE')
     * @param string $apiToken API token for the selected translator
     * @return array Statistics including character_count, character_limit, and percentage_used
     */
    public function getTranslatorStats(string $whichTranslator, string $apiToken): array
    {
        $stats = [];
        $percentage = 0;

        if ($whichTranslator === 'DEEPLPRO') {
            $stats = $this->getDeepLProStats($apiToken);
        } elseif ($whichTranslator === 'DEEPLFREE') {
            $stats = $this->getDeepLFreeStats($apiToken);
        }

        if (isset($stats['character_count'], $stats['character_limit'])) {
            $percentage = ceil(10000 * $stats['character_count'] / ($stats['character_limit'] + 0.00000001)) / 100;
        }

        return [
            'stats' => $stats,
            'percentage_used' => $percentage
        ];
    }

    /**
     * Static helper method to get translator stats with default configuration
     *
     * @param string $whichTranslator Which translator to use
     * @param string $apiToken API token
     * @param string|null $proxy Proxy command prefix
     * @return array Statistics
     */
    public static function getStats(string $whichTranslator, string $apiToken, ?string $proxy = null): array
    {
        $service = new self($proxy);
        return $service->getTranslatorStats($whichTranslator, $apiToken);
    }
}
