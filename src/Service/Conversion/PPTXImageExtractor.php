<?php

namespace App\Service\Conversion;

use ZipArchive;
use RuntimeException;

/**
 * PPTXImageExtractor - Extrait les images d'un fichier PPTX 100% PHP
 * 
 * Les fichiers PPTX sont des archives ZIP contenant un répertoire ppt/media/
 * avec toutes les images intégrées. Cette classe extrait directement ces images.
 */
class PPTXImageExtractor
{
    private string $tempDir;

    public function __construct(string $tempDir = null)
    {
        $this->tempDir = $tempDir ?: sys_get_temp_dir() . '/omist_pptx_images/';
        
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    /**
     * Extrait toutes les images d'un fichier PPTX
     *
     * @param string $pptxPath Chemin vers le fichier PPTX
     * @param string $outputDir Répertoire de sortie pour les images
     * @param bool $preserveNames Conserver les noms originaux des images
     * @return array Tableau des chemins des images extraites
     * @throws RuntimeException En cas d'erreur
     */
    public function extractImages(string $pptxPath, string $outputDir, bool $preserveNames = false): array
    {
        if (!file_exists($pptxPath)) {
            throw new RuntimeException("Fichier PPTX introuvable: $pptxPath");
        }

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $zip = new ZipArchive();
        $zipOpenResult = $zip->open($pptxPath);
        
        if ($zipOpenResult !== true) {
            throw new RuntimeException("Impossible d'ouvrir le fichier PPTX: code erreur $zipOpenResult");
        }

        $extractedImages = [];
        $imageIndex = 0;

        // Parcourir toutes les entrées dans l'archive
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            
            // Les images sont dans ppt/media/
            if (strpos($entryName, 'ppt/media/') === 0) {
                $fileData = $zip->getFromName($entryName);
                if ($fileData !== false) {
                    // Déterminer l'extension
                    $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
                    $validExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp', 'emf', 'wmf'];
                    
                    if (!in_array($ext, $validExtensions)) {
                        // Essayer de détecter le type à partir des données
                        $ext = $this->detectImageExtension($fileData);
                    }

                    if (empty($ext)) {
                        $ext = 'bin';
                    }

                    // Générer le nom de fichier de sortie
                    if ($preserveNames) {
                        $outputPath = $outputDir . '/' . basename($entryName);
                    } else {
                        $outputPath = $outputDir . '/image_' . sprintf('%03d', $imageIndex) . '.' . $ext;
                    }

                    // Sauvegarder l'image
                    file_put_contents($outputPath, $fileData);
                    $extractedImages[] = $outputPath;
                    $imageIndex++;
                }
            }
        }

        $zip->close();
        return $extractedImages;
    }

    /**
     * Extrait les images et les organise par slide
     * 
     * @param string $pptxPath Chemin vers le fichier PPTX
     * @param string $outputDir Répertoire de sortie
     * @return array Tableau organisé par slide : ['slide_1' => [image1, image2, ...], ...]
     */
    public function extractImagesBySlide(string $pptxPath, string $outputDir): array
    {
        if (!file_exists($pptxPath)) {
            throw new RuntimeException("Fichier PPTX introuvable: $pptxPath");
        }

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $zip = new ZipArchive();
        $zipOpenResult = $zip->open($pptxPath);
        
        if ($zipOpenResult !== true) {
            throw new RuntimeException("Impossible d'ouvrir le fichier PPTX");
        }

        // Lister tous les fichiers de slides
        $slideFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (preg_match('/ppt\/slides\/slide(\d+)\.xml/', $entryName, $matches)) {
                $slideFiles[$matches[1]] = $entryName;
            }
        }

        // Lister toutes les images
        $imageFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (strpos($entryName, 'ppt/media/') === 0) {
                $imageFiles[] = $entryName;
            }
        }

        $zip->close();

        // Pour l'instant, nous retournons toutes les images ensemble
        // L'association précise slide-image nécessite une analyse plus poussée
        // du contenu XML de chaque slide
        $allImages = $this->extractImages($pptxPath, $outputDir);
        
        return ['all_slides' => $allImages];
    }

    /**
     * Extrait les images et retourne des informations détaillées
     * 
     * @param string $pptxPath Chemin vers le fichier PPTX
     * @param string $outputDir Répertoire de sortie
     * @return array Tableau avec métadonnées : [['path' => ..., 'name' => ..., 'size' => ...], ...]
     */
    public function extractImagesWithMetadata(string $pptxPath, string $outputDir): array
    {
        $images = $this->extractImages($pptxPath, $outputDir);
        $metadata = [];

        foreach ($images as $imagePath) {
            if (file_exists($imagePath)) {
                $metadata[] = [
                    'path' => $imagePath,
                    'name' => basename($imagePath),
                    'size' => filesize($imagePath),
                    'width' => null,
                    'height' => null,
                    'mime' => null
                ];

                // Obtenir les dimensions et le type MIME
                $imageInfo = @getimagesize($imagePath);
                if ($imageInfo !== false) {
                    $metadata[count($metadata) - 1]['width'] = $imageInfo[0];
                    $metadata[count($metadata) - 1]['height'] = $imageInfo[1];
                    $metadata[count($metadata) - 1]['mime'] = $imageInfo['mime'];
                }
            }
        }

        return $metadata;
    }

    /**
     * Détecte l'extension d'une image à partir de ses données
     */
    private function detectImageExtension(string $data): string
    {
        // Signatures des formats d'image
        $signatures = [
            'png' => "\x89PNG",
            'jpg' => "\xFF\xD8\xFF",
            'jpeg' => "\xFF\xD8\xFF",
            'gif' => "GIF8",
            'bmp' => "BM",
            'webp' => "RIFF",
        ];

        foreach ($signatures as $ext => $signature) {
            if (strpos($data, $signature) === 0) {
                return $ext;
            }
        }

        return '';
    }

    /**
     * Nettoie les fichiers temporaires
     */
    public function cleanupTempFiles(): void
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
}
