<?php

namespace App\Service\Conversion;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;

/**
 * PPTXToPDFConverter - Convertit des fichiers PPTX en PDF 100% PHP
 * 
 * Stratégie :
 * 1. Charger la présentation avec PHPPresentation
 * 2. Rendre chaque slide en image avec PPTXSlideRenderer
 * 3. Créer un PDF avec TCPDF contenant toutes les images
 * 
 * Dépendances :
 * - phpoffice/phppresentation : Pour lire PPTX
 * - tecnickcom/tcpdf : Pour générer PDF
 */
class PPTXToPDFConverter
{
    private PPTXSlideRenderer $slideRenderer;
    private string $tempDir;
    private int $imageWidth;
    private int $imageHeight;
    private int $pdfDpi;

    /**
     * @param int $imageWidth Largeur des images de slides en pixels
     * @param int $imageHeight Hauteur des images de slides en pixels
     * @param int $pdfDpi DPI pour la conversion PDF (affecte la qualité)
     * @param string $tempDir Répertoire temporaire
     */
    public function __construct(
        int $imageWidth = 1024,
        int $imageHeight = 768,
        int $pdfDpi = 100,
        string $tempDir = null
    ) {
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;
        $this->pdfDpi = $pdfDpi;
        $this->tempDir = $tempDir ?: sys_get_temp_dir() . '/omist_pptx2pdf/';
        
        // Vérifier que TCPDF est disponible
        $this->verifyTCPDF();
        
        $this->slideRenderer = new PPTXSlideRenderer(
            $this->imageWidth,
            $this->imageHeight,
            $this->tempDir
        );

        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    /**
     * Vérifie que TCPDF est disponible
     */
    private function verifyTCPDF(): void
    {
        if (!class_exists('TCPDF', false)) {
            // Essayer de charger depuis vendor
            $tcpdfPath = __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php';
            if (file_exists($tcpdfPath)) {
                require_once $tcpdfPath;
            } elseif (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
                require_once __DIR__ . '/../../../vendor/autoload.php';
            }
        }
        
        if (!class_exists('TCPDF', false)) {
            throw new \RuntimeException(
                'TCPDF library is not installed. Please run: composer require tecnickcom/tcpdf:^6.6'
            );
        }
    }

    /**
     * Convertit un fichier PPTX en PDF
     *
     * @param string $pptxPath Chemin vers le fichier PPTX source
     * @param string $pdfPath Chemin vers le fichier PDF de sortie
     * @return string Chemin vers le fichier PDF généré
     * @throws \RuntimeException En cas d'erreur de conversion
     */
    public function convert(string $pptxPath, string $pdfPath): string
    {
        if (!file_exists($pptxPath)) {
            throw new \RuntimeException("Fichier PPTX introuvable: $pptxPath");
        }

        // Charger la présentation
        $presentation = IOFactory::load($pptxPath);

        // Créer un répertoire temporaire pour les images
        $tempImagesDir = $this->tempDir . 'images_' . uniqid() . '/';
        if (!file_exists($tempImagesDir)) {
            mkdir($tempImagesDir, 0777, true);
        }

        try {
            // Rendre toutes les slides en images
            $slideImages = $this->slideRenderer->renderPresentation(
                $presentation,
                $tempImagesDir,
                'slide'
            );

            if (empty($slideImages)) {
                throw new \RuntimeException("Aucune image de slide générée");
            }

            // Créer le PDF avec TCPDF
            $pdf = $this->createPDFFromImages($slideImages, $pdfPath);

            // Nettoyer les images temporaires
            $this->cleanupTempFiles($tempImagesDir);

            return $pdfPath;
        } catch (\Exception $e) {
            // Nettoyer en cas d'erreur
            $this->cleanupTempFiles($tempImagesDir);
            throw new \RuntimeException("Erreur de conversion PPTX → PDF: " . $e->getMessage());
        }
    }

    /**
     * Crée un PDF à partir d'une liste d'images
     * Charge TCPDF dynamiquement pour éviter les erreurs si non installé
     */
    private function createPDFFromImages(array $imagePaths, string $outputPath): string
    {
        // Charger TCPDF dynamiquement
        $tcpdfLoaded = false;
        try {
            // Vérifier si TCPDF est déjà chargé
            if (!class_exists('TCPDF', false)) {
                // Essayer de charger depuis vendor
                $tcpdfPath = __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php';
                if (file_exists($tcpdfPath)) {
                    require_once $tcpdfPath;
                    $tcpdfLoaded = class_exists('TCPDF', false);
                } else {
                    // Essayer via autoload
                    if (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
                        require_once __DIR__ . '/../../../vendor/autoload.php';
                        $tcpdfLoaded = class_exists('TCPDF', false);
                    }
                }
            } else {
                $tcpdfLoaded = true;
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to load TCPDF: ' . $e->getMessage());
        }
        
        if (!$tcpdfLoaded) {
            throw new \RuntimeException(
                'TCPDF library is not installed. Please run: composer require tecnickcom/tcpdf:^6.6'
            );
        }
        
        // Créer un nouveau document PDF
        $pdf = new \TCPDF('P', 'pt');
        
        // Désactiver les en-têtes et pieds de page
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        
        // Définir les marges à 0
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        // Définir la compression (pour réduire la taille)
        $pdf->SetCompression(true);

        // Pour chaque image, créer une page PDF
        foreach ($imagePaths as $imagePath) {
            if (!file_exists($imagePath)) {
                continue;
            }

            // Obtenir les dimensions de l'image
            $imageInfo = @getimagesize($imagePath);
            if ($imageInfo === false) {
                continue;
            }

            $imgWidth = $imageInfo[0];
            $imgHeight = $imageInfo[1];

            // Calculer les dimensions en points (1 inch = 72 points)
            // Conversion : pixels → points (en utilisant le DPI)
            $dpi = $this->pdfDpi;
            $widthPt = ($imgWidth / $dpi) * 72;
            $heightPt = ($imgHeight / $dpi) * 72;

            // Ajouter une nouvelle page avec les dimensions de la slide
            $pdf->AddPage('P', [$widthPt, $heightPt]);

            // Placer l'image sur toute la page
            $pdf->Image($imagePath, 0, 0, $widthPt, $heightPt, '', '', '', false, 300, '', false, false, 0);
        }

        // Sauvegarder le PDF
        $pdf->Output($outputPath, 'F');

        return $outputPath;
    }

    /**
     * Convertit directement depuis une présentation PHPPresentation
     */
    public function convertFromPresentation(PhpPresentation $presentation, string $pdfPath): string
    {
        // Créer un répertoire temporaire pour les images
        $tempImagesDir = $this->tempDir . 'images_' . uniqid() . '/';
        if (!file_exists($tempImagesDir)) {
            mkdir($tempImagesDir, 0777, true);
        }

        try {
            // Rendre toutes les slides en images
            $slideImages = $this->slideRenderer->renderPresentation(
                $presentation,
                $tempImagesDir,
                'slide'
            );

            if (empty($slideImages)) {
                throw new \RuntimeException("Aucune image de slide générée");
            }

            // Créer le PDF
            $this->createPDFFromImages($slideImages, $pdfPath);

            // Nettoyer
            $this->cleanupTempFiles($tempImagesDir);

            return $pdfPath;
        } catch (\Exception $e) {
            $this->cleanupTempFiles($tempImagesDir);
            throw new \RuntimeException("Erreur de conversion: " . $e->getMessage());
        }
    }

    /**
     * Nettoie les fichiers temporaires dans un répertoire
     */
    private function cleanupTempFiles(string $dir): void
    {
        if (file_exists($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Nettoie tous les fichiers temporaires
     */
    public function cleanupAllTempFiles(): void
    {
        $this->slideRenderer->cleanupTempFiles();
        $this->cleanupTempFiles($this->tempDir);
    }

    /**
     * Définit les dimensions des images
     */
    public function setImageDimensions(int $width, int $height): void
    {
        $this->imageWidth = $width;
        $this->imageHeight = $height;
        $this->slideRenderer = new PPTXSlideRenderer(
            $this->imageWidth,
            $this->imageHeight,
            $this->tempDir
        );
    }

    /**
     * Définit le DPI pour la conversion PDF
     */
    public function setPdfDpi(int $dpi): void
    {
        $this->pdfDpi = $dpi;
    }
}
