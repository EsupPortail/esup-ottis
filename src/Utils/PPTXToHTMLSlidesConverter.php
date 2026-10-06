<?php

namespace App\Utils;

use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\Group;
use PhpOffice\PhpPresentation\AbstractShape;
use ZipArchive;
use RuntimeException;
use InvalidArgumentException;

/**
 * PPTXToHTMLSlidesConverter - Convertit un PPTX en HTML (1 slide = 1 div structuré)
 *
 * Génère du HTML avec :
 * - Texte positionné (titre, contenu, puces)
 * - Images extraites et positionnées
 * - Mise en page fidèle (via CSS position: absolute)
 *
 * PHP pur, utilise PHPPresentation + ZipArchive
 */
class PPTXToHTMLSlidesConverter
{
    /**
     * Convertit un PPTX en fichiers HTML (1 par slide)
     *
     * @param string $pptxPath Chemin du fichier PPTX
     * @param string $outputDir Dossier de sortie
     * @param array $options Options (largeur, hauteur, qualité)
     * @return array Tableau des chemins des fichiers HTML générés
     * @throws RuntimeException En cas d'erreur
     */
    public static function convert(string $pptxPath, string $outputDir, array $options = []): array
    {
        // Options par défaut
        $options = array_merge([
            'width' => 960,
            'height' => 720,
            'bg_color' => '#FFFFFF',
            'extract_images' => true,
        ], $options);

        // Validation
        if (!file_exists($pptxPath)) {
            throw new InvalidArgumentException("Fichier PPTX introuvable: $pptxPath");
        }

        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true)) {
                throw new RuntimeException("Impossible de créer le répertoire: $outputDir");
            }
        }

        if (!is_writable($outputDir)) {
            throw new RuntimeException("Répertoire non accessible en écriture: $outputDir");
        }

        // Extraire les images d'abord (si demandé)
        $imageMap = [];
        if ($options['extract_images']) {
            $imageMap = self::extractImagesFromPPTX($pptxPath, $outputDir);
        }

        // Charger la présentation
        try {
            $presentation = IOFactory::load($pptxPath);
        } catch (\Exception $e) {
            throw new RuntimeException("Erreur de chargement PPTX: " . $e->getMessage());
        }

        $htmlFiles = [];
        $slideIndex = 0;

        // Générer HTML pour chaque slide
        foreach ($presentation->getAllSlides() as $slide) {
            if (!$slide instanceof Slide) {
                continue;
            }

            $html = self::renderSlide($slide, $imageMap, $options);
            $outputPath = $outputDir . '/slide_' . sprintf('%03d', $slideIndex) . '.html';
            file_put_contents($outputPath, $html);
            $htmlFiles[] = $outputPath;
            $slideIndex++;
        }

        if (empty($htmlFiles)) {
            throw new RuntimeException("Aucune slide valide trouvée");
        }

        // Créer un fichier HTML combiné (optionnel)
        $combinedPath = $outputDir . '/presentation.html';
        $combinedHtml = self::createCombinedHTML($htmlFiles);
        file_put_contents($combinedPath, $combinedHtml);

        return [$combinedPath, ...$htmlFiles];
    }

    /**
     * Extrait les images du PPTX et retourne une map [shape_id => image_path]
     */
    private static function extractImagesFromPPTX(string $pptxPath, string $outputDir): array
    {
        $imageMap = [];
        $zip = new ZipArchive();

        if ($zip->open($pptxPath) !== true) {
            return $imageMap;
        }

        $imageDir = $outputDir . '/images/';
        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        $imageIndex = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (strpos($entryName, 'ppt/media/') === 0) {
                $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp'])) {
                    $imageData = $zip->getFromName($entryName);
                    if ($imageData !== false) {
                        $outputPath = $imageDir . 'media_' . sprintf('%03d', $imageIndex) . '.' . $ext;
                        file_put_contents($outputPath, $imageData);
                        $imageMap['media_' . sprintf('%03d', $imageIndex)] = 'images/media_' . sprintf('%03d', $imageIndex) . '.' . $ext;
                        $imageIndex++;
                    }
                }
            }
        }
        $zip->close();
        return $imageMap;
    }

    /**
     * Rend une slide en HTML
     */
    private static function renderSlide(Slide $slide, array $imageMap, array $options): string
    {
        $width = $options['width'];
        $height = $options['height'];
        $bgColor = $options['bg_color'];

        $html = '<!DOCTYPE html>';
        $html .= '<html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<style>';
        $html .= '.slide { width: ' . $width . 'px; height: ' . $height . 'px; ';
        $html .= 'position: relative; overflow: hidden; ';
        $html .= 'background-color: ' . $bgColor . '; font-family: Arial, sans-serif; }';
        $html .= '.slide-text { position: absolute; white-space: pre-wrap; }';
        $html .= '.slide-image { position: absolute; max-width: 100%; max-height: 100%; }';
        $html .= '</style>';
        $html .= '</head><body>';
        $html .= '<div class="slide">';

        // Rendre chaque shape
        foreach ($slide->getShapeCollection() as $shape) {
            $html .= self::renderShape($shape, $imageMap);
        }

        $html .= '</div></body></html>';
        return $html;
    }

    /**
     * Rend une shape en HTML
     */
    private static function renderShape($shape, array $imageMap): string
    {
        // Shape de texte (a getText method)
        if (method_exists($shape, 'getText') && is_object($shape)) {
            $text = $shape->getText();
            if ($text === null || $text === '') {
                return '';
            }

            $x = (int) $shape->getOffsetX();
            $y = (int) $shape->getOffsetY();
            $w = (int) $shape->getWidth();
            $h = (int) $shape->getHeight();

            // Récupérer le style de texte
            $font = null;
            if (method_exists($shape, 'getFont')) {
                $font = $shape->getFont();
            }
            $fontSize = $font ? $font->getSize() : 12;
            $fontColor = '#000000';
            if ($font && method_exists($font, 'getColor') && $font->getColor()) {
                $colorObj = $font->getColor();
                if (method_exists($colorObj, 'getRGB')) {
                    $fontColor = $colorObj->getRGB();
                } elseif (method_exists($colorObj, 'getHex')) {
                    $fontColor = $colorObj->getHex();
                }
            }
            $bold = $font ? $font->getBold() : false;
            $italic = $font ? $font->getItalic() : false;

            $style = sprintf(
                'left: %dpx; top: %dpx; width: %dpx; height: %dpx; ',
                $x, $y, $w, $h
            );
            $style .= sprintf(
                'font-size: %dpt; color: %s; ',
                $fontSize, $fontColor
            );
            if ($bold) $style .= 'font-weight: bold; ';
            if ($italic) $style .= 'font-style: italic; ';

            return sprintf(
                '<div class="slide-text" style="%s">%s</div>',
                $style,
                htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
            );
        }

        // Shape d'image (Drawing - a path or image data)
        if (is_a($shape, '\PhpOffice\PhpPresentation\Shape\Drawing\AbstractDrawingAdapter') ||
            is_a($shape, '\PhpOffice\PhpPresentation\Shape\Drawing') ||
            (class_exists('\PhpOffice\PhpPresentation\AbstractGraphic') && is_a($shape, '\PhpOffice\PhpPresentation\AbstractGraphic'))) {
            $x = (int) $shape->getOffsetX();
            $y = (int) $shape->getOffsetY();
            $w = (int) $shape->getWidth();
            $h = (int) $shape->getHeight();

            // Trouver l'image correspondante (utiliser la première disponible pour l'instant)
            if (!empty($imageMap)) {
                $firstImage = reset($imageMap);
                $style = sprintf(
                    'left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
                    $x, $y, $w, $h
                );
                return sprintf(
                    '<img class="slide-image" src="%s" style="%s" />',
                    $firstImage,
                    $style
                );
            }
            return '';
        }

        // Groupe de shapes (a getShapeCollection method)
        if (method_exists($shape, 'getShapeCollection')) {
            $html = '';
            foreach ($shape->getShapeCollection() as $childShape) {
                $html .= self::renderShape($childShape, $imageMap);
            }
            return $html;
        }

        return '';
    }

    /**
     * Crée un HTML combiné avec toutes les slides
     */
    private static function createCombinedHTML(array $htmlFiles): string
    {
        $html = '<!DOCTYPE html>';
        $html .= '<html><head><meta charset="UTF-8">';
        $html .= '<style>';
        $html .= '.presentation { display: flex; flex-direction: column; gap: 20px; }';
        $html .= '.slide-container { break-inside: avoid; page-break-inside: avoid; }';
        $html .= '</style>';
        $html .= '</head><body>';
        $html .= '<div class="presentation">';

        foreach ($htmlFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match('/<div class="slide">(.*?)<\/div>/s', $content, $matches)) {
                    $html .= '<div class="slide-container">' . $matches[1] . '</div>';
                }
            }
        }

        $html .= '</div></body></html>';
        return $html;
    }
}
