<?php

namespace App\Utils;

use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

/**
 * PPTXImageExtractor - Extrait les images d'un fichier PPTX.
 *
 * Les fichiers PPTX sont des archives ZIP contenant :
 * - ppt/media/ : images (image1.png, image2.jpeg, etc.)
 * - ppt/slides/ : slides (slide1.xml, slide2.xml, etc.)
 * - ppt/slideMasters/ : masters
 *
 * Cette classe extrait directement les images depuis ppt/media/ sans conversion.
 */
class PPTXImageExtractor
{
    /**
     * Extrait toutes les images d'un fichier PPTX.
     *
     * @param string $filePath Chemin vers le fichier PPTX
     * @param string $outputDir Dossier de sortie (doit exister et être accessible en écriture)
     * @throws RuntimeException Si le fichier ne peut pas être lu
     * @return array Tableau des chemins des images extraites
     */
    public static function extract(string $filePath, string $outputDir): array
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Fichier PPTX introuvable: $filePath");
        }

        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true)) {
                throw new RuntimeException("Impossible de créer le répertoire: $outputDir");
            }
        }

        if (!is_writable($outputDir)) {
            throw new RuntimeException("Répertoire non accessible en écriture: $outputDir");
        }

        $zip = new ZipArchive();
        $zipOpenResult = $zip->open($filePath);

        if ($zipOpenResult !== true) {
            throw new RuntimeException("Impossible d'ouvrir le fichier PPTX (code: $zipOpenResult)");
        }

        $images = [];
        $imageIndex = 0;

        // Parcourir tous les fichiers dans l'archive
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            // Filtrer les images dans ppt/media/
            if (strpos($entryName, 'ppt/media/') === 0) {
                $imageName = basename($entryName);
                $extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

                // Extensions d'image supportées
                $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'svg', 'webp'];

                if (in_array($extension, $imageExtensions)) {
                    // Extraire le contenu de l'image directement
                    $imageData = $zip->getFromName($entryName);
                    if ($imageData !== false) {
                        $outputPath = $outputDir . '/slide_' . sprintf('%03d', $imageIndex) . '.' . $extension;
                        if (file_put_contents($outputPath, $imageData) !== false) {
                            $images[] = $outputPath;
                            $imageIndex++;
                        }
                    }
                }
            }
        }

        $zip->close();
        return $images;
    }

    /**
     * Extrait les images et retourne leur contenu en base64.
     * Utile pour les API qui retournent du JSON.
     *
     * @param string $filePath Chemin vers le fichier PPTX
     * @return array Tableau de {name: string, data: string, mime: string}
     */
    public static function extractAsBase64(string $filePath): array
    {
        $images = [];
        $zip = new ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("Impossible d'ouvrir le fichier PPTX");
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            if (strpos($entryName, 'ppt/media/') === 0) {
                $imageName = basename($entryName);
                $extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

                $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'svg', 'webp'];

                if (in_array($extension, $imageExtensions)) {
                    $imageData = $zip->getFromName($entryName);
                    if ($imageData !== false) {
                        $base64 = base64_encode($imageData);
                        $mimeType = self::getMimeTypeFromExtension($extension);
                        $images[] = [
                            'name' => 'slide_' . sprintf('%03d', count($images)) . '.' . $extension,
                            'data' => $base64,
                            'mime' => $mimeType
                        ];
                    }
                }
            }
        }

        $zip->close();
        return $images;
    }

    /**
     * Détermine le type MIME à partir de l'extension.
     *
     * @param string $extension Extension du fichier (sans le point)
     * @return string Type MIME
     */
    private static function getMimeTypeFromExtension(string $extension): string
    {
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp'
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
}
