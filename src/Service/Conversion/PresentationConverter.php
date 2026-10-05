<?php

namespace App\Service\Conversion;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;

/**
 * PresentationConverter - Service de conversion universelle 100% PHP
 * 
 * Ce service fournit une API unifiée pour convertir entre différents formats
 * de présentation en utilisant uniquement des bibliothèques PHP.
 * 
 * Formats supportés :
 * - PPTX (PowerPoint) : Lecture et écriture complète
 * - ODP (OpenDocument Presentation) : Lecture via conversion en PPTX
 * - PDF : Écriture uniquement (via conversion depuis PPTX)
 * - Images : Génération depuis PPTX
 * 
 * Dépendances :
 * - phpoffice/phppresentation : Pour la gestion des présentations
 * - tecnickcom/tcpdf : Pour la génération PDF
 * - GD ou Imagick : Pour la manipulation d'images
 */
class PresentationConverter
{
    private PPTXToPDFConverter $pptxToPdfConverter;
    private ODPToPPTXConverter $odpToPptxConverter;
    private PPTXSlideRenderer $slideRenderer;
    private string $tempDir;

    /**
     * @param string $tempDir Répertoire temporaire pour les fichiers intermédiaires
     */
    public function __construct(string $tempDir = null)
    {
        $this->tempDir = $tempDir ?: sys_get_temp_dir() . '/omist_converter/';
        
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        $this->pptxToPdfConverter = new PPTXToPDFConverter(1024, 768, 100, $this->tempDir);
        $this->odpToPptxConverter = new ODPToPPTXConverter($this->tempDir);
        $this->slideRenderer = new PPTXSlideRenderer(1024, 768, $this->tempDir);
    }

    /**
     * Convertit un fichier en PDF
     * 
     * @param string $inputPath Chemin vers le fichier d'entrée (PPTX, ODP)
     * @param string $outputPath Chemin vers le fichier PDF de sortie
     * @param string $inputFormat Format du fichier d'entrée ('pptx', 'odp', 'auto')
     * @return string Chemin vers le fichier PDF généré
     * @throws \RuntimeException En cas d'erreur de conversion
     */
    public function toPDF(string $inputPath, string $outputPath, string $inputFormat = 'auto'): string
    {
        if ($inputFormat === 'auto') {
            $inputFormat = $this->detectFormat($inputPath);
        }

        switch ($inputFormat) {
            case 'pptx':
                return $this->pptxToPdfConverter->convert($inputPath, $outputPath);

            case 'odp':
                // Convertir ODP en PPTX d'abord
                $tempPptxPath = $this->tempDir . 'temp_' . uniqid() . '.pptx';
                $this->odpToPptxConverter->convert($inputPath, $tempPptxPath);
                
                // Puis convertir PPTX en PDF
                $result = $this->pptxToPdfConverter->convert($tempPptxPath, $outputPath);
                
                // Nettoyer le fichier temporaire
                if (file_exists($tempPptxPath)) {
                    unlink($tempPptxPath);
                }
                
                return $result;

            default:
                throw new \RuntimeException("Format non supporté pour la conversion en PDF: $inputFormat");
        }
    }

    /**
     * Convertit un fichier en images (une par slide)
     * 
     * @param string $inputPath Chemin vers le fichier d'entrée (PPTX, ODP)
     * @param string $outputDir Répertoire de sortie pour les images
     * @param string $inputFormat Format du fichier d'entrée ('pptx', 'odp', 'auto')
     * @param string $prefix Préfixe pour les noms de fichiers
     * @return array Tableau des chemins des images générées
     * @throws \RuntimeException En cas d'erreur de conversion
     */
    public function toImages(string $inputPath, string $outputDir, string $inputFormat = 'auto', string $prefix = 'slide'): array
    {
        if ($inputFormat === 'auto') {
            $inputFormat = $this->detectFormat($inputPath);
        }

        switch ($inputFormat) {
            case 'pptx':
                return $this->slideRenderer->renderPresentation($inputPath, $outputDir, $prefix);

            case 'odp':
                // Convertir ODP en PPTX d'abord
                $tempPptxPath = $this->tempDir . 'temp_' . uniqid() . '.pptx';
                $this->odpToPptxConverter->convert($inputPath, $tempPptxPath);
                
                // Puis générer les images depuis PPTX
                $images = $this->slideRenderer->renderPresentation($tempPptxPath, $outputDir, $prefix);
                
                // Nettoyer le fichier temporaire
                if (file_exists($tempPptxPath)) {
                    unlink($tempPptxPath);
                }
                
                return $images;

            default:
                throw new \RuntimeException("Format non supporté pour la conversion en images: $inputFormat");
        }
    }

    /**
     * Extrait les notes de toutes les slides
     * 
     * @param string $inputPath Chemin vers le fichier d'entrée (PPTX, ODP)
     * @param string $inputFormat Format du fichier d'entrée ('pptx', 'odp', 'auto')
     * @return array Tableau des slides avec leurs notes
     * @throws \RuntimeException En cas d'erreur
     */
    public function extractNotes(string $inputPath, string $inputFormat = 'auto'): array
    {
        if ($inputFormat === 'auto') {
            $inputFormat = $this->detectFormat($inputPath);
        }

        switch ($inputFormat) {
            case 'pptx':
                return \App\Utils\PPTXNotesExtractor::extract($inputPath);

            case 'odp':
                // Convertir ODP en PPTX d'abord
                $tempPptxPath = $this->tempDir . 'temp_' . uniqid() . '.pptx';
                $this->odpToPptxConverter->convert($inputPath, $tempPptxPath);
                
                // Puis extraire les notes depuis PPTX
                $notes = \App\Utils\PPTXNotesExtractor::extract($tempPptxPath);
                
                // Nettoyer le fichier temporaire
                if (file_exists($tempPptxPath)) {
                    unlink($tempPptxPath);
                }
                
                return $notes;

            default:
                throw new \RuntimeException("Format non supporté pour l'extraction de notes: $inputFormat");
        }
    }

    /**
     * Convertit ODP en PPTX
     * 
     * @param string $odpPath Chemin vers le fichier ODP
     * @param string $pptxPath Chemin vers le fichier PPTX de sortie
     * @return string Chemin vers le fichier PPTX généré
     */
    public function odpToPptx(string $odpPath, string $pptxPath): string
    {
        return $this->odpToPptxConverter->convert($odpPath, $pptxPath);
    }

    /**
     * Convertit PPTX en PDF
     * 
     * @param string $pptxPath Chemin vers le fichier PPTX
     * @param string $pdfPath Chemin vers le fichier PDF de sortie
     * @return string Chemin vers le fichier PDF généré
     */
    public function pptxToPdf(string $pptxPath, string $pdfPath): string
    {
        return $this->pptxToPdfConverter->convert($pptxPath, $pdfPath);
    }

    /**
     * Rend les slides d'une présentation en images
     * 
     * @param string $pptxPath Chemin vers le fichier PPTX
     * @param string $outputDir Répertoire de sortie
     * @param string $prefix Préfixe pour les noms de fichiers
     * @return array Tableau des chemins des images
     */
    public function pptxToImages(string $pptxPath, string $outputDir, string $prefix = 'slide'): array
    {
        return $this->slideRenderer->renderPresentation($pptxPath, $outputDir, $prefix);
    }

    /**
     * Détecte le format d'un fichier à partir de son extension
     */
    private function detectFormat(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'pptx':
                return 'pptx';
            case 'odp':
                return 'odp';
            case 'pdf':
                return 'pdf';
            default:
                // Détection par MIME type
                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $filePath);
                    finfo_close($finfo);
                    
                    if (strpos($mime, 'powerpoint') !== false || strpos($mime, 'pptx') !== false) {
                        return 'pptx';
                    }
                    if (strpos($mime, 'opendocument.presentation') !== false) {
                        return 'odp';
                    }
                }
                
                throw new \RuntimeException("Impossible de détecter le format du fichier: $filePath");
        }
    }

    /**
     * Nettoie tous les fichiers temporaires
     */
    public function cleanupTempFiles(): void
    {
        $this->pptxToPdfConverter->cleanupAllTempFiles();
        $this->odpToPptxConverter->cleanupAllTempFiles();
        $this->slideRenderer->cleanupTempFiles();

        // Nettoyer le répertoire principal
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
     * Définit les dimensions des images générées
     * 
     * @param int $width Largeur en pixels
     * @param int $height Hauteur en pixels
     */
    public function setImageDimensions(int $width = 1024, int $height = 768): void
    {
        $this->pptxToPdfConverter->setImageDimensions($width, $height);
        $this->slideRenderer = new PPTXSlideRenderer($width, $height, $this->tempDir);
    }

    /**
     * Définit le DPI pour la conversion PDF
     * 
     * @param int $dpi DPI (100 par défaut)
     */
    public function setPdfDpi(int $dpi = 100): void
    {
        $this->pptxToPdfConverter->setPdfDpi($dpi);
    }
}
