<?php

namespace App\Utils;

use RuntimeException;
use ZipArchive;

/**
 * ODPToHTMLConverter - Convertit un fichier ODP (OpenDocument Presentation) en HTML.
 * Les fichiers ODP sont des archives ZIP contenant du XML (format OpenDocument).
 * Cette classe parse le XML et extrait le texte, les images et la structure des slides.
 */
class ODPToHTMLConverter
{
    /**
     * Convertit un fichier ODP en HTML.
     *
     * @param string $odpPath Chemin absolu vers le fichier ODP
     * @throws RuntimeException Si le fichier ne peut pas être lu ou parse
     * @return string Contenu HTML généré
     */
    public static function convert(string $odpPath): string
    {
        if (!file_exists($odpPath)) {
            throw new RuntimeException("Fichier ODP introuvable: $odpPath");
        }

        $zip = new ZipArchive();
        $zipOpenResult = $zip->open($odpPath);
        if ($zipOpenResult !== true) {
            throw new RuntimeException("Impossible d'ouvrir le fichier ODP: code erreur $zipOpenResult");
        }

        // Lire content.xml (contient les slides et leur contenu)
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
        $xml->registerXPathNamespace('draw', 'urn:oasis:names:tc:opendocument:xmlns:drawing:1.0');
        $xml->registerXPathNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');
        $xml->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');

        $html = '<div class="odp-presentation" style="font-family: Arial, sans-serif; width: 100%;">';

        // Extraire toutes les slides (draw:page)
        $slides = $xml->xpath('//draw:page');
        if (empty($slides)) {
            $html .= '<p>Aucune slide trouvée dans le fichier ODP.</p>';
        } else {
            foreach ($slides as $index => $slide) {
                $html .= '<div class="odp-slide" style="page-break-after: always; margin-bottom: 20px; padding: 20px; border: 1px solid #eee;" data-slide="' . ($index + 1) . '">';

                // --- Extraire le texte ---
                $texts = $slide->xpath('.//text:p | .//text:span | .//text:h');
                foreach ($texts as $text) {
                    $style = '';
                    // Détecter les styles de base (gras, italique)
                    $textStyle = $text->xpath('.//text:span');
                    if (!empty($textStyle)) {
                        foreach ($textStyle as $span) {
                            $styleAttr = (string)($span->attributes('text', true)->style ?? '');
                            if (strpos($styleAttr, 'bold') !== false) {
                                $style .= 'font-weight: bold;';
                            }
                            if (strpos($styleAttr, 'italic') !== false) {
                                $style .= 'font-style: italic;';
                            }
                        }
                    }
                    $textContent = (string)$text;
                    if (!empty($textContent)) {
                        $html .= '<p style="' . $style . '">' . htmlspecialchars($textContent, ENT_QUOTES, 'UTF-8') . '</p>';
                    }
                }

                // --- Extraire les images ---
                $images = $slide->xpath('.//draw:image | .//draw:object');
                foreach ($images as $image) {
                    $href = (string)($image->attributes('xlink', true)->href ?? '');
                    if (!empty($href) && $zip->locateName($href) !== false) {
                        $imageData = $zip->getFromName($href);
                        if ($imageData !== false) {
                            $base64 = base64_encode($imageData);
                            $mimeType = self::getImageMimeType($imageData);
                            $html .= '<img src="data:' . $mimeType . ';base64,' . $base64 . '" style="max-width: 100%; height: auto; margin: 10px 0;" />';
                        }
                    }
                }

                $html .= '</div>'; // Fin de la slide
            }
        }

        $html .= '</div>'; // Fin de la présentation
        $zip->close();
        return $html;
    }

    /**
     * Détecte le type MIME d'une image à partir de ses premiers octets.
     *
     * @param string $imageData Données binaires de l'image
     * @return string Type MIME (ex: 'image/png', 'image/jpeg')
     */
    private static function getImageMimeType(string $imageData): string
    {
        $signature = substr($imageData, 0, 8);
        if (strpos($signature, "\x89PNG") === 0) {
            return 'image/png';
        } elseif (strpos($signature, "\xFF\xD8\xFF") === 0) {
            return 'image/jpeg';
        } elseif (strpos($signature, 'GIF8') === 0) {
            return 'image/gif';
        }
        return 'image/png'; // Default fallback
    }
}
