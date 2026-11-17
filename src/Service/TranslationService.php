<?php

namespace App\Service;

use App\Utils\Logger;

/**
 * TranslationService - Pipeline de traduction : DeepL Pro/Free et
 * LibreTranslate, avec cache par langue (FinalSub_{roomId}_{lang} pour
 * les finales, PartialSub_{roomId}_{lang} pour les partiels) partage par
 * tous les clients de la meme langue pour eviter les appels API redondants.
 */
class TranslationService
{
    /**
     * Traduit un texte via le service configure et construit la reponse
     * applicative. Reponse en cache si une traduction identique existe.
     *
     * @param array{texte?: mixed, lang?: mixed, langinput?: mixed, roomId?: mixed, lineid?: mixed, forcelibre?: mixed, whichtranslator?: mixed} $params
     * @return array{status: int, body: array<string, mixed>} Statut HTTP et corps de la reponse
     */
    public function translate(array $params): array
    {
        $texte = (string) ($params['texte'] ?? '');
        $lang = (string) preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($params['lang'] ?? ''));
        $langinput = (string) preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($params['langinput'] ?? ''));
        $roomId = (string) ($params['roomId'] ?? 'UNKNOWN');
        $lineid = (string) ($params['lineid'] ?? 'NONE');
        $forcelibre = (bool) ($params['forcelibre'] ?? false);
        $whichTranslator = $params['whichtranslator'] ?? null;

        Logger::debug('[TranslationService.translate] params: ' . json_encode($params));

        // Chargement de la cle API du traducteur demande
        $apikey = '';
        $tokenConfig = null;
        try {
            $tokenConfig = TokenConfigService::create();
            if ($whichTranslator === 'DEEPLPRO' || $whichTranslator === 'DEEPL' || empty($whichTranslator)) {
                $apikey = $tokenConfig->getDeepLProApiToken();
            } elseif ($whichTranslator === 'DEEPLFREE') {
                $apikey = $tokenConfig->getDeepLFreeApiToken();
            }
        } catch (\Exception $e) {
            Logger::warning('TranslationService: Failed to load API key: ' . $e->getMessage());
        }

        // Validate ROOMID
        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomId)) {
            $roomId = 'UNKNOWN';
        }

        // Validate lineid
        if (!preg_match('/^[a-zA-Z0-9_-]{0,64}$/', $lineid)) {
            $lineid = 'NONE';
        }

        // Traducteur par defaut : configuration puis DEEPLPRO
        if (empty($whichTranslator)) {
            if ($tokenConfig !== null) {
                $whichTranslator = $tokenConfig->getWhichTranslator();
            }
            if (empty($whichTranslator)) {
                $whichTranslator = 'DEEPLPRO';
            }
        }

        if ($forcelibre) {
            $whichTranslator = 'LIBRETRANSLATE';
        }

        $response = [
            'translator' => $whichTranslator,
            'roomId' => $roomId,
            'key' => ''
        ];

        $result = '';

        // Cache : traduction deja disponible pour cette ligne ?
        if ($roomId != 'UNKNOWN') {
            $result = $this->readFromTranslationFiles($roomId, $lineid, $lang, $texte);
        }

        if (empty($result)) {
            if (empty($apikey)) {
                Logger::warning('TranslationService: ERROR - No API key available for translator: ' . $whichTranslator);
                return ['status' => 400, 'body' => ['error' => true, 'message' => 'No API key available for translator: ' . $whichTranslator]];
            }
            $result = $this->callTranslationAPI($texte, $langinput, $lang, $whichTranslator, $apikey, $lineid, $roomId);
        }

        if (!empty($result)) {
            $resultData = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($resultData['error']) && $resultData['error'] === true) {
                    $response['error'] = true;
                    $response['message'] = $resultData['message'] ?? 'Translation failed';
                    $response['data'] = [];
                } elseif (isset($resultData['message']) && !isset($resultData['translations'])) {
                    // Erreur API (ex. cle DeepL invalide)
                    $response['error'] = true;
                    $response['message'] = $resultData['message'] ?? 'Translation API error';
                    $response['data'] = [];
                } else {
                    $response['data'] = $resultData;
                }
            } else {
                $response['data'] = ['translations' => [['text' => $result]]];
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'No translation available';
            $response['data'] = [];
        }

        return ['status' => 200, 'body' => $response];
    }

    /**
     * Cache par langue : PartialSub (partiel courant, cle = empreinte md5 du
     * texte source) ou FinalSub (finales, cle = lineid).
     */
    public function readFromTranslationFiles(string $roomId, string $lineid, string $lang, string $texte = ''): string
    {
        $result = '';
        $lang = strtolower(substr($lang, 0, 2));
        $ottisDir = sys_get_temp_dir() . '/ottis/';

        if ($lineid == 'DIRECT') {
            $filename = $ottisDir . 'PartialSub_' . $roomId . '_' . $lang;
            if (is_readable($filename)) {
                $content = file_get_contents($filename);
                if ($content !== false) {
                    $sep = strpos($content, ';');
                    if ($sep !== false && substr($content, 0, $sep) === md5(trim($texte))) {
                        $result = substr($content, $sep + 1);
                    }
                }
            }
        } else {
            $filename = $ottisDir . 'FinalSub_' . $roomId . '_' . $lang;
            if (is_readable($filename) && $lineid != 'NONE' && $lineid != '') {
                $lines = file($filename, FILE_IGNORE_NEW_LINES);
                foreach ($lines as $line) {
                    $sep = strpos($line, ';');
                    $prefix = $sep !== false ? substr($line, 0, $sep) : '';
                    if ($sep !== false && ctype_digit($prefix) && $prefix === (string) $lineid) {
                        $result = substr($line, $sep + 1);
                    }
                }
            }
        }

        if ($result !== '') {
            if ($lang == 'en') {
                $result = $this->removeAccents($result);
            }
            // Meme forme de reponse que l'API, attendue par translate() et les clients
            $result = json_encode(['translations' => [['text' => $result]]], JSON_UNESCAPED_UNICODE);
        }

        return $result;
    }

    /**
     * Appel du service de traduction et mise en cache du resultat
     *
     * @return string Reponse JSON (ou JSON d'erreur si l'appel echoue)
     */
    private function callTranslationAPI(string $text, string $sourceLang, string $targetLang, string $translator, string $apikey, string $lineid, string $roomId): string
    {
        $data = [
            'text' => [$text],
            'source_lang' => strtoupper($sourceLang),
            'target_lang' => strtoupper($targetLang)
        ];
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);

        switch ($translator) {
            case 'DEEPLPRO':
            case 'DEEPL':
                $headers = [
                    'Authorization: DeepL-Auth-Key ' . $apikey,
                    'Content-Type: application/json'
                ];
                $result = $this->httpPost('https://api.deepl.com/v2/translate', $payload, $headers);
                break;

            case 'DEEPLFREE':
                $url = 'https://api-free.deepl.com/v2/translate';
                $formData = http_build_query([
                    'auth_key' => $apikey,
                    'text' => $text,
                    'source_lang' => strtoupper($sourceLang),
                    'target_lang' => strtoupper($targetLang)
                ]);
                $headers = ['Content-Type: application/x-www-form-urlencoded'];
                $result = $this->httpPost($url, $formData, $headers);

                if ($result !== false) {
                    $decoded = json_decode($result, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['translations'])) {
                        $result = json_encode(['data' => ['translations' => $decoded['translations']]], JSON_UNESCAPED_UNICODE);
                    }
                }
                break;

            case 'LIBRETRANSLATE':
            default:
                $tokenConfig = TokenConfigService::create();
                $url = rtrim($tokenConfig->getLibreTranslateServer(), '/') . '/translate';
                $formData = http_build_query([
                    'q' => $text,
                    'format' => 'text',
                    'source' => strtolower($sourceLang),
                    'target' => strtolower($targetLang)
                ]);
                $headers = ['Content-Type: application/x-www-form-urlencoded'];
                $result = $this->httpPost($url, $formData, $headers);

                if ($result !== false) {
                    $decoded = json_decode($result, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['translatedText'])) {
                        $result = json_encode(['data' => ['translations' => [['text' => $decoded['translatedText']]]]], JSON_UNESCAPED_UNICODE);
                    }
                }
                break;
        }

        // Cache par langue : finale (lineid numerique) -> FinalSub,
        // partiel (lineid DIRECT) -> PartialSub
        if ($result !== false && $roomId != 'UNKNOWN' && $lineid != 'NONE' && $lineid != '') {
            $translatedText = $this->extractTranslatedText((string) $result);
            if ($translatedText !== '') {
                $ottisDir = sys_get_temp_dir() . '/ottis/';
                if (!is_dir($ottisDir)) {
                    @mkdir($ottisDir, 0777, true);
                }
                $cacheLang = strtolower(substr($targetLang, 0, 2));
                if ($lineid === 'DIRECT') {
                    $filename = $ottisDir . 'PartialSub_' . $roomId . '_' . $cacheLang;
                    @file_put_contents($filename, md5(trim($text)) . ';' . $translatedText);
                } else {
                    $filename = $ottisDir . 'FinalSub_' . $roomId . '_' . $cacheLang;
                    @file_put_contents($filename, $lineid . ';' . $translatedText . "\n", FILE_APPEND);
                }
            }
        }

        return $result ?: json_encode(['error' => true, 'message' => 'Translation failed']);
    }

    /**
     * Extrait le texte traduit d'une reponse d'API : DeepL Pro (JSON),
     * DeepL Free / LibreTranslate (enveloppes data), LibreTranslate brut.
     */
    public function extractTranslatedText(string $result): string
    {
        $decoded = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }
        if (isset($decoded['translations'][0]['text'])) {
            return (string) $decoded['translations'][0]['text'];
        }
        if (isset($decoded['data']['translations'][0]['text'])) {
            return (string) $decoded['data']['translations'][0]['text'];
        }
        if (isset($decoded['translatedText'])) {
            return (string) $decoded['translatedText'];
        }
        return '';
    }

    /**
     * POST HTTP via curl (repli stream context si curl absent)
     *
     * @param string $url URL cible
     * @param string $payload Corps de la requete
     * @param array<int, string> $headers En-tetes de la requete ("Nom: valeur")
     * @return string|false Reponse, ou false en cas d'echec
     */
    private function httpPost(string $url, string $payload, array $headers): string|false
    {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);

            $result = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($result === false || $httpCode < 200 || $httpCode >= 300) {
                return false;
            }
            return $result;
        }

        if (!ini_get('allow_url_fopen')) {
            return false;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $payload,
                'timeout' => 15,
                'ignore_errors' => true,
            ]
        ]);

        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            return false;
        }

        return $result;
    }

    /**
     * Supprime les accents
     */
    public function removeAccents(string $str): string
    {
        $a = ['À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß'];
        $b = ['A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s'];
        return str_replace($a, $b, $str);
    }
}
