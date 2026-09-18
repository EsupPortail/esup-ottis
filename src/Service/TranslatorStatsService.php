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
    /**
     * @var string The proxy command prefix (empty string if not configured)
     */
    private string $proxy;

    /**
     * Constructor
     *
     * @param string|null $proxy Proxy command prefix (e.g., 'proxy' or 'curl -x proxy.server:port')
     */
    public function __construct(?string $proxy = null)
    {
        $this->proxy = $proxy ?? (getenv('HTTP_PROXY') ? 'curl -x ' . getenv('HTTP_PROXY') : '');
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

        $command = $this->proxy . ' curl -q https://api.deepl.com/v2/usage?auth_key=' . $apiToken;
        $output = [];
        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || empty($output[0])) {
            return [];
        }

        try {
            $stats = json_decode($output[0], true, 512, JSON_THROW_ON_ERROR);
            return is_array($stats) ? $stats : [];
        } catch (\JsonException) {
            return [];
        }
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

        $command = $this->proxy . ' curl -q https://api-free.deepl.com/v2/usage?auth_key=' . $apiToken;
        $output = [];
        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || empty($output[0])) {
            return [];
        }

        try {
            $stats = json_decode($output[0], true, 512, JSON_THROW_ON_ERROR);
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
