<?php

namespace App\Utils;

use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

/**
 * PPTXNotesExtractor - Extrait les notes des slides d'un fichier PPTX
 *
 * Cette classe permet d'extraire les notes des slides d'une présentation PowerPoint
 * en parsant directement le XML contenu dans le fichier PPTX (qui est une archive ZIP).
 * Elle inclut un fallback vers PHPPresentation si nécessaire.
 */
class PPTXNotesExtractor
{
    /**
     * Extrait les notes de toutes les slides d'un fichier PPTX
     *
     * @param string $filePath Chemin vers le fichier PPTX
     * @throws \Exception En cas d'erreur de lecture
     * @return array Tableau de slides avec leurs notes
     */
    public static function extract(string $filePath): array
    {
        // Essayer d'abord le parsing XML direct (plus fiable pour les notes)
        try {
            return self::extractNotesFromXML($filePath);
        } catch (\Exception $e) {
            // Fallback vers PHPPresentation si disponible
            error_log('Parsing XML échoué, fallback vers PHPPresentation: ' . $e->getMessage());
        }

        // Si PHPPresentation n'est pas disponible ou échoue aussi
        try {
            return self::extractWithPHPPresentation($filePath);
        } catch (\Exception $e) {
            error_log('PHPPresentation échoué: ' . $e->getMessage());
        }

        throw new RuntimeException("Impossible d'extraire les notes du fichier PPTX");
    }

    /**
     * Extrait les notes en parsant directement le XML du PPTX
     *
     * @param string $filePath Chemin vers le fichier PPTX
     * @return array Tableau de slides avec leurs notes
     */
    private static function extractNotesFromXML(string $filePath): array
    {
        $notes = [];
        $zip = new ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("Impossible d'ouvrir le fichier PPTX comme archive ZIP");
        }

        // Lister les fichiers de notes (ppt/notesSlides/notesSlide{num}.xml)
        for ($i = 1; $i <= 1000; $i++) {
            $notesFile = "ppt/notesSlides/notesSlide{$i}.xml";
            if ($zip->locateName($notesFile) === false) {
                break; // Plus de slides
            }

            $xmlContent = $zip->getFromName($notesFile);
            if ($xmlContent === false) {
                continue;
            }

            $notesText = self::parseNotesXML($xmlContent);
            $notes[] = [
                'Slide' => $i,
                'Notes' => $notesText
            ];
        }

        $zip->close();
        return $notes;
    }

    /**
     * Parse le contenu XML d'une slide de notes
     *
     * @param string $xmlContent Contenu XML
     * @return array Tableau de lignes de texte
     */
    private static function parseNotesXML(string $xmlContent): array
    {
        $result = [];
        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            return $result;
        }

        // Les notes sont dans <a:t> (text runs)
        $textNodes = $xml->xpath('//a:t');
        if ($textNodes === false) {
            return $result;
        }

        $currentParagraph = '';
        foreach ($textNodes as $textNode) {
            $text = trim((string)$textNode);
            if (!empty($text)) {
                if (!empty($currentParagraph)) {
                    $currentParagraph .= ' ' . $text;
                } else {
                    $currentParagraph = $text;
                }
            }
        }

        if (!empty($currentParagraph)) {
            $result[] = $currentParagraph;
        }

        return $result;
    }

    /**
     * Fallback: Extraction via PHPPresentation (si disponible)
     *
     * @param string $filePath Chemin vers le fichier PPTX
     * @return array Tableau de slides avec leurs notes
     */
    private static function extractWithPHPPresentation(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Fichier non trouvé : $filePath");
        }

        // Charger PHPPresentation si disponible
        if (!class_exists('PhpOffice\PhpPresentation\IOFactory')) {
            throw new RuntimeException('PHPPresentation non disponible');
        }

        $presentation = \PhpOffice\PhpPresentation\IOFactory::load($filePath);
        $notes = [];

        foreach ($presentation->getAllSlides() as $index => $slide) {
            $noteObj = $slide->getNote();
            $notesText = [];

            foreach ($noteObj->getShapeCollection() as $shape) {
                if (method_exists($shape, 'getText')) {
                    $notesText[] = trim($shape->getText());
                }
            }

            $notes[] = [
                'Slide' => $index + 1,
                'Notes' => $notesText
            ];
        }

        return $notes;
    }
}
