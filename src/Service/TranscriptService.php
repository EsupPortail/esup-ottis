<?php

namespace App\Service;

/**
 * TranscriptService - Sous-titres de la salle : fichiers temporaires
 * FinalSub_{roomId}_{lang} (finales) et VerylastSub_{roomId} (partiel
 * en cours), ecrits par ExtractController::transcriptionSub.
 */
class TranscriptService
{
    /**
     * Historique des finales d'une salle et langue : les prefixes
     * (lineid auditeur ou C conference) sont retires et la numerotation
     * sequentielle refaite.
     */
    public function readFinalLines(string $roomid, string $lang): string
    {
        $filename = sys_get_temp_dir() . '/ottis/FinalSub_' . $roomid . '_' . $lang;

        if (!file_exists($filename) || !is_readable($filename)) {
            return '';
        }

        $content = file_get_contents($filename);
        $lines = explode("\n", trim((string) $content));
        $result = '';
        $lineNumber = 0;
        foreach ($lines as $line) {
            // Prefixe lineid (auditeur) ou C (conference) : retirer
            $sep = strpos($line, ';');
            $text = $sep !== false ? substr($line, $sep + 1) : $line;
            if (trim($text) !== '') {
                $result .= $lineNumber . ';' . $text . "\n";
                $lineNumber++;
            }
        }

        return trim($result);
    }

    /**
     * Dernier partiel publie par la conference. Le fichier grossit a
     * chaque partiel : lecture depuis la fin, seul le dernier importe.
     */
    public function readLastPartial(string $roomid): string
    {
        $filename = sys_get_temp_dir() . '/ottis/VerylastSub_' . $roomid;

        if (!file_exists($filename) || !is_readable($filename)) {
            return '';
        }

        $last = '';
        $fd = @fopen($filename, 'r');
        if ($fd !== false) {
            // Fenetre de 64 Ko depuis la fin, suffisante pour un partiel
            $size = (int) filesize($filename);
            $window = 65536;
            $start = max(0, $size - $window);
            @fseek($fd, $start);
            $chunk = (string) @fread($fd, $size - $start);
            fclose($fd);
            $lines = explode("\n", $chunk);
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                if ($line !== '') {
                    $last = $line;
                    break;
                }
            }
        }

        return $last;
    }
}
