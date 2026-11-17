<?php

namespace App\Service;

use App\Utils\Logger;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

/**
 * NotebookService - Transcript de la salle : fichiers tmp/Link_{roomId}
 * Une ligne par finale, format etendu : id;lang;texte;lang_cible;traduction
 */
class NotebookService
{
    /**
     * Lignes du transcript d'identifiant >= $ligneID, prefixees par $order
     */
    public function readLines(string $roomid, int $ligneID, int $order): string
    {
        $filePath = APP_ROOT . '/tmp/Link_' . $roomid;

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return '';
        }

        $fd = @fopen($filePath, 'r');
        if ($fd === false) {
            return '';
        }

        $output = '';
        while ($ligne = fgets($fd, 4096)) {
            $T = explode(';', $ligne);
            // phpstan-ignore-next-line - explode always returns at least one element
            if ((int) $T[0] >= $ligneID) {
                Logger::debug('readtext - Ligne: ' . trim($ligne));
                $output .= $order . ';' . $ligne;
            }
        }
        fclose($fd);

        return $output;
    }

    /**
     * Ajoute une finale (format etendu 5 champs), ne conserve que les
     * $lastlines dernieres lignes, retourne l'id de la nouvelle ligne.
     *
     * @return array{lineid: int, saved: int}
     */
    public function saveLine(
        string $roomid,
        string $lang,
        string $text,
        string $targetlanguage,
        string $translatedtext,
        int $lastlines
    ): array {
        $ligneID = 0;
        $TLines = [];
        $filePath = APP_ROOT . '/tmp/Link_' . $roomid;

        if (file_exists($filePath)) {
            $fd = fopen($filePath, 'r');
            if ($fd) {
                while ($ligne = trim(fgets($fd, 4096))) {
                    if ($ligne != '') {
                        $TLines[] = $ligne;
                        $ligneID++;
                    }
                }
                fclose($fd);
                $lastLine = end($TLines);
                if ($lastLine !== false) {
                    $parts = explode(';', $lastLine);
                    $ligneID = (int) $parts[0] + 1;
                }
            }
        }

        if (empty($targetlanguage)) {
            $targetlanguage = $lang;
        }
        if (empty($translatedtext)) {
            $translatedtext = $text;
        }

        $TLines[] = $ligneID . ';' . $lang . ';' . $text . ';' . $targetlanguage . ';' . $translatedtext;

        if (count($TLines) > $lastlines) {
            $TLines = array_slice($TLines, count($TLines) - $lastlines);
        }

        $fd = fopen($filePath, 'c+');
        if ($fd && flock($fd, LOCK_EX)) {
            ftruncate($fd, 0);
            fputs($fd, implode("\n", $TLines) . "\n");
            fflush($fd);
            flock($fd, LOCK_UN);
        }
        if ($fd) {
            fclose($fd);
        }

        return ['lineid' => $ligneID, 'saved' => strlen($text)];
    }

    /**
     * Historique VO : "id;texte_source" de chaque finale
     */
    public function readVoiceOriginal(string $roomid): string
    {
        $filename = APP_ROOT . '/tmp/Link_' . $roomid;

        if (!file_exists($filename) || !is_readable($filename)) {
            return '';
        }

        $content = file_get_contents($filename);
        $lines = explode("\n", trim((string) $content));
        $result = '';
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $T = explode(';', $line);
            if (count($T) > 3) {
                // Format Link_ : id;lang;texte;lang_cible;traduction
                $result .= $T[0] . ';' . $T[2] . "\n";
            }
        }

        return trim($result);
    }
}
