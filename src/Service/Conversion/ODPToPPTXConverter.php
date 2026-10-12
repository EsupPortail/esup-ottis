<?php

namespace App\Service\Conversion;

use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Style\Alignment;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * ODPToPPTXConverter - Convertit des fichiers ODP en PPTX 100% PHP
 *
 * Les fichiers ODP (OpenDocument Presentation) sont des archives ZIP contenant du XML.
 * Ce convertisseur parse le XML et recrée une présentation équivalente en PPTX
 * utilisant PHPPresentation.
 *
 * Format ODP :
 * - content.xml : Contient la structure des slides et le texte
 * - styles.xml : Contient les styles
 * - Pictures/ : Contient les images
 */
class ODPToPPTXConverter
{
    private string $tempDir;

    public function __construct(string $tempDir = null)
    {
        $this->tempDir = $tempDir ?: sys_get_temp_dir() . '/ottis_odp_convert/';
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    /**
     * Convertit un fichier ODP en PPTX
     *
     * @param string $odpPath Chemin vers le fichier ODP source
     * @param string $pptxPath Chemin vers le fichier PPTX de sortie
     * @throws RuntimeException En cas d'erreur de conversion
     * @return string Chemin vers le fichier PPTX généré
     */
    public function convert(string $odpPath, string $pptxPath): string
    {
        if (!file_exists($odpPath)) {
            throw new RuntimeException("Fichier ODP introuvable: $odpPath");
        }

        // Ouvrir l'archive ODP
        $zip = new ZipArchive();
        $zipOpenResult = $zip->open($odpPath);

        if ($zipOpenResult !== true) {
            throw new RuntimeException("Impossible d'ouvrir le fichier ODP: code erreur $zipOpenResult");
        }

        // Créer une nouvelle présentation PPTX
        $presentation = new PhpPresentation();

        // Lire content.xml
        $contentXml = $zip->getFromName('content.xml');
        if ($contentXml === false) {
            $zip->close();
            throw new RuntimeException("Fichier content.xml introuvable dans l'ODP");
        }

        // Enregistrer les namespaces OpenDocument
        $xml = simplexml_load_string($contentXml);
        if ($xml === false) {
            $zip->close();
            throw new RuntimeException('Impossible de parser content.xml');
        }

        // Namespaces pour XPath
        $xml->registerXPathNamespace('office', 'urn:oasis:names:tc:opendocument:xmlns:office:1.0');
        $xml->registerXPathNamespace('draw', 'urn:oasis:names:tc:opendocument:xmlns:drawing:1.0');
        $xml->registerXPathNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');
        $xml->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');
        $xml->registerXPathNamespace('style', 'urn:oasis:names:tc:opendocument:xmlns:style:1.0');
        $xml->registerXPathNamespace('fo', 'urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0');

        // Extraire toutes les slides (draw:page)
        $slides = $xml->xpath('//draw:page');

        if (empty($slides)) {
            $zip->close();
            throw new RuntimeException('Aucune slide trouvée dans le fichier ODP');
        }

        // Extraire les images de la présentation
        $imagePaths = $this->extractODPImages($zip, $odpPath);

        // Convertir chaque slide
        foreach ($slides as $index => $slideXml) {
            $this->convertSlide($presentation, $slideXml, $imagePaths, $zip);
        }

        $zip->close();

        // Sauvegarder la présentation en PPTX
        $writer = IOFactory::createWriter($presentation, 'PowerPoint2007');
        $writer->save($pptxPath);

        // Nettoyer les fichiers temporaires
        $this->cleanupTempFiles();

        return $pptxPath;
    }

    /**
     * Extrait les images de l'ODP et retourne un tableau [href => chemin_local]
     */
    private function extractODPImages(ZipArchive $zip, string $odpPath): array
    {
        $imagePaths = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            // Les images peuvent être dans Pictures/ ou dans d'autres répertoires
            if (preg_match('/Pictures\/.*\.(png|jpg|jpeg|gif|bmp|webp)$/i', $entryName) ||
                preg_match('/.*\.(png|jpg|jpeg|gif|bmp|webp)$/i', $entryName)) {

                $imageData = $zip->getFromName($entryName);
                if ($imageData !== false) {
                    $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp'])) {
                        $ext = 'png';
                    }

                    $localPath = $this->tempDir . 'image_' . md5($entryName) . '.' . $ext;
                    file_put_contents($localPath, $imageData);

                    $imagePaths[$entryName] = $localPath;
                }
            }
        }

        return $imagePaths;
    }

    /**
     * Convertit une slide ODP en slide PPTX
     */
    private function convertSlide(PhpPresentation $presentation, SimpleXMLElement $slideXml, array $imagePaths, ZipArchive $zip): void
    {
        // Créer une nouvelle slide
        $slide = $presentation->createSlide();

        // Position de départ pour le contenu
        $y = 50;
        $maxWidth = 800;

        // Extraire le titre de la slide (si présent)
        $titleElements = $slideXml->xpath('.//draw:text-box[contains(@*, "title")] | .//text:p[@style-name="title"]');
        if (!empty($titleElements)) {
            foreach ($titleElements as $titleElement) {
                $titleText = $this->extractTextFromElement($titleElement);
                if (!empty($titleText)) {
                    $titleShape = $slide->createRichTextShape();
                    $titleShape->setOffsetX(50);
                    $titleShape->setOffsetY($y);
                    $titleShape->setWidth($maxWidth);
                    $titleShape->setHeight(60);

                    // Style du titre
                    $paragraph = $titleShape->getActiveParagraph();
                    $paragraph->getFont()->setSize(24);
                    $paragraph->getFont()->setBold(true);
                    $alignment = new Alignment();
                    $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $paragraph->setAlignment($alignment);

                    // Ajouter le texte
                    $paragraph->createTextRun($titleText);

                    $y += 70;
                }
            }
        }

        // Extraire le contenu textuel
        $textElements = $slideXml->xpath('.//text:p | .//text:h | .//text:span');
        foreach ($textElements as $textElement) {
            $text = $this->extractTextFromElement($textElement);
            if (!empty($text)) {
                $shape = $slide->createRichTextShape();
                $shape->setOffsetX(50);
                $shape->setOffsetY($y);
                $shape->setWidth($maxWidth);
                $shape->setHeight(40);

                // Ajouter le texte via un paragraph
                $paragraph = $shape->getActiveParagraph();
                $paragraph->createTextRun($text);

                $y += 50;
            }
        }

        // Extraire les images
        $imageElements = $slideXml->xpath('.//draw:image | .//draw:object');
        foreach ($imageElements as $imageElement) {
            $href = (string)($imageElement->attributes('xlink', true)->href ?? '');

            if (!empty($href) && isset($imagePaths[$href])) {
                $imagePath = $imagePaths[$href];

                if (file_exists($imagePath)) {
                    $shape = $slide->createDrawingShape();
                    $shape->setPath($imagePath);
                    $shape->setOffsetX(100);
                    $shape->setOffsetY($y);

                    // Taille par défaut pour les images
                    $shape->setWidth(400);
                    $shape->setHeight(300);

                    $y += 320;
                }
            }
        }

        // Extraire les formes et tableaux (simplifié)
        $shapes = $slideXml->xpath('.//draw:rect | .//draw:line | .//draw:ellipse | .//table:table');
        foreach ($shapes as $shape) {
            // Pour l'instant, nous sautons les formes complexes
            // Elles pourraient être ajoutées dans une version future
        }
    }

    /**
     * Extrait le texte d'un élément XML ODP
     */
    private function extractTextFromElement(SimpleXMLElement $element): string
    {
        $text = '';

        // Obtenir le texte direct
        if (isset($element->{'text:span'})) {
            foreach ($element->{'text:span'} as $span) {
                $text .= (string)$span;
            }
        } else {
            $text = trim((string)$element);
        }

        // Nettoyer le texte
        $text = str_replace(["\n", "\r", "\t"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Nettoie les fichiers temporaires
     */
    private function cleanupTempFiles(): void
    {
        if (file_exists($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < time() - 3600) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Nettoie tous les fichiers temporaires de cette session
     */
    public function cleanupAllTempFiles(): void
    {
        if (file_exists($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
    }
}
