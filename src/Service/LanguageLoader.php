<?php

namespace App\Service;

/**
 * LanguageLoader - Service for loading supported languages from CSV file
 *
 * This service provides a centralized way to load and manage the list of supported
 * languages for the OMIST translation system.
 */
class LanguageLoader
{
    /**
     * @var string Path to the supported languages CSV file
     */
    private string $languagesFilePath;

    /**
     * @var array|null Cached list of languages
     */
    private ?array $languagesCache = null;

    /**
     * Constructor
     *
     * @param string $languagesFilePath Path to the supported languages CSV file
     */
    public function __construct(string $languagesFilePath = '')
    {
        if (!empty($languagesFilePath)) {
            $this->languagesFilePath = $languagesFilePath;
        } elseif (defined('APP_ROOT')) {
            $this->languagesFilePath = APP_ROOT . '/supportedlanguages.csv';
        } else {
            // Fallback: try to find the file relative to current directory
            $this->languagesFilePath = __DIR__ . '/../../supportedlanguages.csv';
        }
    }

    /**
     * Load supported languages from CSV file
     *
     * The CSV file format is: name_vo;code;name_fr
     *
     * @return array Array of languages indexed by their code, each containing:
     *               - name_fr: French name
     *               - name_vo: Original name
     *               - code: Language code
     *               - voice: Voice code (default: '-1')
     */
    public function loadLanguages(): array
    {
        // Return cached result if available
        if ($this->languagesCache !== null) {
            return $this->languagesCache;
        }

        $languages = [];
        $filePath = $this->languagesFilePath;

        if (!file_exists($filePath)) {
            $this->languagesCache = $languages;
            return $languages;
        }

        $fd = fopen($filePath, 'r');
        if ($fd) {
            while ($line = fgets($fd, 4096)) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = explode(';', $line);
                if (count($parts) >= 3) {
                    $code = trim($parts[1]);
                    $languages[$code] = [
                        'name_fr' => trim($parts[2]),
                        'name_vo' => trim($parts[0]),
                        'code' => $code,
                        'voice' => '-1'
                    ];
                }
            }
            fclose($fd);
        }

        // Cache the result
        $this->languagesCache = $languages;
        return $languages;
    }

    /**
     * Get a specific language by code
     *
     * @param string $code Language code
     * @return array|null Language data or null if not found
     */
    public function getLanguage(string $code): ?array
    {
        $languages = $this->loadLanguages();
        return $languages[$code] ?? null;
    }

    /**
     * Get all language codes
     *
     * @return array Array of language codes
     */
    public function getLanguageCodes(): array
    {
        return array_keys($this->loadLanguages());
    }

    /**
     * Clear the languages cache
     *
     * Useful when the languages file has been updated and you need to reload it
     */
    public function clearCache(): void
    {
        $this->languagesCache = null;
    }

    /**
     * Static helper method to load languages with default configuration
     *
     * @return array Array of languages
     */
    public static function load(): array
    {
        $loader = new self();
        return $loader->loadLanguages();
    }
}
