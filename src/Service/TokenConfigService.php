<?php

namespace App\Service;

/**
 * TokenConfigService - Service for loading and managing API tokens from configuration
 *
 * This service replaces the legacy config.php file and provides token management
 * with random rotation for load balancing across multiple API keys.
 */
class TokenConfigService
{
    /**
     * @var array All tokens loaded from tokens.json
     */
    private array $allTokens;

    /**
     * @var string The tokens file path
     */
    private string $tokensFile;

    /**
     * Constructor
     *
     * @param string $tokensFile Path to the tokens.json file
     */
    public function __construct(string $tokensFile = '')
    {
        $this->tokensFile = $tokensFile;
        $this->loadTokens();
    }

    /**
     * Load tokens from the JSON file
     *
     * @throws \RuntimeException If the tokens file is missing, unreadable, or invalid
     */
    private function loadTokens(): void
    {
        // Use APP_ROOT if tokensFile is empty or relative
        if (empty($this->tokensFile) || !file_exists($this->tokensFile)) {
            $this->tokensFile = (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/tables/tokens.json';
        }

        if (!file_exists($this->tokensFile)) {
            throw new \RuntimeException("ERREUR: Le fichier {$this->tokensFile} est introuvable.");
        }

        $content = file_get_contents($this->tokensFile);
        if ($content === false) {
            throw new \RuntimeException("ERREUR: Impossible de lire le fichier {$this->tokensFile}.");
        }

        $tokens = json_decode($content, true);
        if ($tokens === null) {
            throw new \RuntimeException("ERREUR: Le fichier {$this->tokensFile} n'est pas un JSON valide.");
        }

        $this->allTokens = $tokens;

        // Validate that we have at least DeepL Pro tokens
        if (empty($this->allTokens['DEEPLPROTOKENS'] ?? [])) {
            error_log("AVERTISSEMENT: Aucun token DeepL Pro trouvé dans {$this->tokensFile}.");
        }
    }

    /**
     * Get a random token from an array of tokens
     *
     * @param string $key The key in the tokens array (e.g., 'GOOGLETOKENS', 'DEEPLFREETOKENS', 'DEEPLPROTOKENS')
     * @return string The randomly selected token, or empty string if none available
     */
    private function getRandomToken(string $key): string
    {
        if (!isset($this->allTokens[$key]) || !is_array($this->allTokens[$key]) || count($this->allTokens[$key]) === 0) {
            return '';
        }

        return $this->allTokens[$key][array_rand($this->allTokens[$key])];
    }

    /**
     * Get a random Google API token
     *
     * @return string
     */
    public function getGoogleApiToken(): string
    {
        return $this->getRandomToken('GOOGLETOKENS');
    }

    /**
     * Get a random DeepL Free API token
     *
     * @return string
     */
    public function getDeepLFreeApiToken(): string
    {
        return $this->getRandomToken('DEEPLFREETOKENS');
    }

    /**
     * Get a random DeepL Pro API token
     *
     * @return string
     */
    public function getDeepLProApiToken(): string
    {
        return $this->getRandomToken('DEEPLPROTOKENS');
    }

    /**
     * Get the configured translator to use
     *
     * @return string The translator identifier (e.g., 'DEEPLFREE', 'DEEPLPRO', 'LIBRETRANSLATE')
     */
    public function getWhichTranslator(): string
    {
        if (isset($this->allTokens['WHICHTRANSLATOR']) && is_array($this->allTokens['WHICHTRANSLATOR']) && count($this->allTokens['WHICHTRANSLATOR']) > 0) {
            return $this->allTokens['WHICHTRANSLATOR'][array_rand($this->allTokens['WHICHTRANSLATOR'])];
        }

        return 'DEEPLFREE';
    }

    /**
     * Get the LibreTranslate server URL
     *
     * @return string
     */
    public function getLibreTranslateServer(): string
    {
        return getenv('LIBRETRANSLATESERVER') ?: 'https://translate.terraprint.co/translate';
    }

    /**
     * Get the proxy command prefix
     *
     * @return string
     */
    public function getProxy(): string
    {
        return getenv('HTTP_PROXY') ? 'curl -x ' . getenv('HTTP_PROXY') : '';
    }

    /**
     * Get all tokens (for backward compatibility)
     *
     * @return array
     */
    public function getAllTokens(): array
    {
        return $this->allTokens;
    }

    /**
     * Get supported languages for DeepL
     *
     * @return array
     */
    public function getDeepLKnownLanguages(): array
    {
        return ['ar', 'bg', 'zh', 'cs', 'da', 'en', 'fi', 'fr', 'de', 'gr', 'hu', 'nl', 'it', 'jp', 'lv', 'lt', 'po', 'pt', 'ro', 'ru', 'sk', 'sl', 'es', 'sv', 'tr', 'in', 'uk'];
    }

    /**
     * Get supported languages for LibreTranslate
     *
     * @return array
     */
    public function getLibreTranslateKnownLanguages(): array
    {
        return ['en', 'ar', 'az', 'zh', 'cs', 'nl', 'eo', 'fi', 'fr', 'de', 'el', 'hi', 'hu', 'id', 'ga', 'it', 'ja', 'ko', 'fa', 'pl', 'pt', 'ru', 'sk', 'es', 'sv', 'tr', 'uk', 'vi'];
    }

    /**
     * Get Microsoft Azure default key (for testing)
     *
     * @return string
     */
    public function getMicrosoftKey(): string
    {
        return '';
    }

    /**
     * Get Microsoft Azure default region (for testing)
     *
     * @return string
     */
    public function getMicrosoftRegion(): string
    {
        return '';
    }

    /**
     * Check if stats saving is enabled
     *
     * @return bool
     */
    public function isSaveStatsEnabled(): bool
    {
        return false;
    }

    /**
     * Static helper method to create a TokenConfigService instance
     *
     * @param string $tokensFile Path to the tokens.json file
     * @return TokenConfigService
     */
    public static function create(string $tokensFile = ''): TokenConfigService
    {
        return new self($tokensFile);
    }
}
