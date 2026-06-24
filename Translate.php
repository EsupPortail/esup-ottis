<?php
// Désactiver l'affichage des erreurs et activer les logs
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Chemin vers ton fichier de log (à adapter)
$DEBUG = true; // Mettre à false en production !!!!
$logFile = __DIR__ . '/tmp/translate_deepl_debug.log';

function safe_log($message) {
    global $DEBUG, $logFile;
    if (!$DEBUG) {
        return;
    }
    $dir = dirname($logFile);
    if (!is_dir($dir) || !is_writable($dir)) {
        return;
    }
    file_put_contents($logFile, $message, FILE_APPEND);
}

function http_post($url, $payload, $headers, &$httpCode, &$errorMsg) {
    $errorMsg = '';
    $httpCode = null;

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
        $errorMsg = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($result === false) {
            return false;
        }
        return $result;
    }

    if (!ini_get('allow_url_fopen')) {
        $errorMsg = 'Neither cURL nor allow_url_fopen is available.';
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
        $errorMsg = 'file_get_contents failed';
        return false;
    }

    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $headerLine) {
            if (preg_match('#HTTP/[0-9\.]+\s+(\d+)#', $headerLine, $matches)) {
                $httpCode = intval($matches[1]);
                break;
            }
        }
    }

    return $result;
}

function respond_json(array $response, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Security Phase 1: Input validation and XSS prevention

// Récupérer les paramètres avec validation et limites de sécurité
$texte = $_GET["texte"] ?? '';

// Security: Limit text length to prevent DoS (10KB max)
if (strlen($texte) > 10240) {
    $texte = substr($texte, 0, 10240);
}

// Security: Sanitize text to prevent XSS - strip all HTML tags and encode
$texte = strip_tags($texte);
$texte = htmlspecialchars($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
$texte = html_entity_decode($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8');

// Validate and sanitize language codes
$lang = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['lang'] ?? '');
if (strlen($lang) > 16) {
    $lang = substr($lang, 0, 16);
}

$langinput = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['langinput'] ?? '');
if (strlen($langinput) > 16) {
    $langinput = substr($langinput, 0, 16);
}

// Validate ROOMID using regex (alphanumeric, underscore, hyphen only)
$roomId = $_GET['ROOMID'] ?? 'UNKNOWN';
if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $roomId)) {
    $roomId = 'UNKNOWN';
} else {
    $roomId = preg_replace('/[^a-zA-Z0-9_-]/', '', $roomId);
}

// Validate lineid
$lineid = $_GET['lineid'] ?? 'NONE';
if (!preg_match('/^[a-zA-Z0-9_-]{0,64}$/', $lineid)) {
    $lineid = 'NONE';
} else {
    $lineid = preg_replace('/[^a-zA-Z0-9_-]/', '', $lineid);
}

$forcelibre = isset($_GET['forcelibre']);
$WhichTranslator = $_GET['whichtranslator'] ?? null; // may be set later
if ($forcelibre) {
    $WhichTranslator = "LIBRETRANSLATE";
}
$apikey = $_GET["apikey"] ?? '';

// Log des paramètres reçus
safe_log("Paramètres reçus:\n" .
    "texte: $texte\n" .
    "lang: $lang\n" .
    "langinput: $langinput\n" .
    "ROOMID: $roomId\n" .
    "lineid: $lineid\n" .
    "forcelibre: " . ($forcelibre ? 'true' : 'false') . "\n" .
    "WhichTranslator: $WhichTranslator\n");

// Définir l'en-tête JSON
header('Content-Type: application/json');

include "config.php";

// Si l'utilisateur n'a pas forcé un traducteur via GET, utiliser DEEPLPRO
if (empty($WhichTranslator)) {
    $WhichTranslator = 'DEEPLPRO';
}

safe_log("Clé DeepL Pro: " . (isset($DEEPLAPITOKEN) ? "*** (masquée) ***" : "NON DÉFINIE") . "\n");

if ($apikey != "") {
    switch ($WhichTranslator) {
        case "DEEPL":
        case "DEEPLPRO":
            $DEEPLAPITOKEN = $apikey;
            break;
        case "DEEPLFREE":
            $DEEPLFREEAPITOKEN = $apikey;
            break;
    }
}

$deepl_usable = "true";
$proxy = "";

if ($WhichTranslator == "DEEPL" || $WhichTranslator == "DEEPLPRO") {
    if ($deepl_usable == "false") {
        $WhichTranslator = "LIBRETRANSLATE";
    }
}

function removeAccents($str) {
  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
  return str_replace($a, $b, $str);
}

// Préparer la réponse JSON
$response = [
    'translator' => $WhichTranslator,
    'roomId' => $roomId,
    'key' => $apikey ?? '',
    'data' => []
];

$result = "";
if ($roomId != "UNKNOWN") {
    if ($lineid == "DIRECT") {
        if (file_exists("tmp/Verylast_" . $roomId)) {
            $fd = fopen("tmp/Verylast_" . $roomId, "r");
            while ($T = fgetcsv($fd, 4096, ";")) {
                if (count($T) > 2) {
                    if ($lineid == $T[0] && $lang == $T[1]) {
                        $result = $T[2];
                        if ($lang == "en") $result = removeAccents($result);
                    }
                }
            }
            fclose($fd);
        }
    } else {
        if (file_exists("tmp/Translated_" . $roomId)) {
            $fd = fopen("tmp/Translated_" . $roomId, "r");
            if ($lineid != "DIRECT" && $lineid != "NONE" && $lineid != "") {
                while ($T = fgetcsv($fd, 4096, ";")) {
                    if (count($T) > 2) {
                        if ($lineid == $T[0] && $lang == $T[1]) {
                            $result = $T[2];
                            if ($lang == "en") $result = removeAccents($result);
                        }
                    }
                }
            }
            fclose($fd);
        }
    }

    if ($result == "") {
        // Appels aux APIs de traduction (inchangé)
        $data = [
            'text' => [$texte],  // $texte n'a pas besoin d'être modifié manuellement
            'source_lang' => strtoupper($langinput),
            'target_lang' => strtoupper($lang)
        ];
        $payload = json_encode($data);

        // Utiliser cURL natif PHP pour DeepL Pro
        if ($WhichTranslator == "DEEPL" || $WhichTranslator == "DEEPLPRO") {
            safe_log("Appel DeepL payload: " . $payload . "\n");

            $headers = [
                'Authorization: DeepL-Auth-Key ' . $DEEPLAPITOKEN,
                'Content-Type: application/json'
            ];
            $resultjson = http_post('https://api.deepl.com/v2/translate', $payload, $headers, $httpcode, $curlerr);

            safe_log("DeepL HTTP code: $httpcode, error: $curlerr\nResponse: $resultjson\n");
            if ($resultjson === false || $httpcode === null || $httpcode < 200 || $httpcode >= 300) {
                $resultjson = json_encode(['error' => true, 'message' => 'DeepL request failed', 'http_code' => $httpcode, 'error' => $curlerr]);
            }
            $result = $resultjson;
        }

        if ($WhichTranslator == "DEEPLFREE") {
            $url = 'https://api-free.deepl.com/v2/translate';
            $payload = http_build_query([
                'auth_key' => $DEEPLFREEAPITOKEN,
                'text' => $texte,
                'source_lang' => strtoupper($langinput),
                'target_lang' => strtoupper($lang)
            ]);
            $headers = ['Content-Type: application/x-www-form-urlencoded'];

            $resultjson = http_post($url, $payload, $headers, $httpCode, $errorMsg);
            safe_log("DeepL Free HTTP code: $httpCode, error: $errorMsg\nResponse: $resultjson\n");
            if ($resultjson === false || $httpCode === null || $httpCode < 200 || $httpCode >= 300) {
                $resultjson = json_encode(['error' => true, 'message' => 'DeepL Free request failed', 'http_code' => $httpCode, 'error' => $errorMsg]);
            } else {
                $decoded = json_decode($resultjson, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['translations'])) {
                    $resultjson = json_encode(['data' => ['translations' => $decoded['translations']]]);
                } else {
                    $resultjson = json_encode(['error' => true, 'message' => 'Invalid DeepL Free response', 'raw' => $resultjson]);
                }
            }
            $result = $resultjson;
        }

        if ($WhichTranslator == "LIBRETRANSLATE") {
            $url = $LIBRETRANSLATESERVER;
            $payload = http_build_query([
                'q' => $texte,
                'format' => 'text',
                'source' => strtolower($langinput),
                'target' => strtolower($lang)
            ]);
            $headers = ['Content-Type: application/x-www-form-urlencoded'];

            $resultjson = http_post($url, $payload, $headers, $httpCode, $errorMsg);
            safe_log("LibreTranslate HTTP code: $httpCode, error: $errorMsg\nResponse: $resultjson\n");
            if ($resultjson === false || $httpCode === null || $httpCode < 200 || $httpCode >= 300) {
                $resultjson = json_encode(['error' => true, 'message' => 'LibreTranslate request failed', 'http_code' => $httpCode, 'error' => $errorMsg]);
            } else {
                $decoded = json_decode($resultjson, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['translatedText'])) {
                    $resultjson = json_encode(['data' => ['translations' => [['translatedText' => $decoded['translatedText']]]]]);
                } elseif (json_last_error() === JSON_ERROR_NONE && isset($decoded[0]['translation'])) {
                    $resultjson = json_encode(['data' => ['translations' => [['translatedText' => $decoded[0]['translation']]]]]);
                } else {
                    $resultjson = json_encode(['error' => true, 'message' => 'Invalid LibreTranslate response', 'raw' => $resultjson]);
                }
            }
            $result = $resultjson;
        }

        // Sauvegarder le résultat dans un fichier
        $translatedFile = "tmp/Translated_" . $roomId;
        $fd = @fopen($translatedFile, "a");
        if ($fd !== false) {
            if ($lineid != "NONE" && $lineid != "") {
                fputcsv($fd, [$lineid, $lang, $result], ';');
            }
            fclose($fd);
        } else {
            safe_log("Impossible d'ouvrir '$translatedFile' en écriture\n");
        }

        if ($lineid == "NONE") {
            $verylastFile = "tmp/Verylast_" . $roomId;
            $fd = @fopen($verylastFile, "w");
            if ($fd !== false) {
                fputcsv($fd, [$lineid, $lang, $result], ';');
                fclose($fd);
            } else {
                safe_log("Impossible d'ouvrir '$verylastFile' en écriture\n");
            }
        }
    } else {
        $result = str_replace('"translatedText":"', '"translatedText":"', $result);
    }
} else {
    // Cas où ROOMID n'est pas défini (ne devrait pas arriver)
    $response['error'] = true;
    $response['message'] = 'ROOMID non défini';
}

// Ajouter le résultat à la réponse
if (!empty($result)) {
    try {
        $resultData = json_decode($result, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $response['data'] = $resultData;
        } else {
            // Si $result n'est pas un JSON valide, le mettre dans translatedText
            $response['data'] = [
                'translations' => [
                    ['translatedText' => $result]
                ]
            ];
        }
    } catch (Exception $e) {
        $response['data'] = [
            'translations' => [
                ['translatedText' => $result]
            ]
        ];
    }
}

// Retourner la réponse JSON
echo json_encode($response);
?>