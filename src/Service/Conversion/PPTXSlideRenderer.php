<?php

namespace App\Service\Conversion;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\AbstractShape;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\Drawing\AbstractDrawing;
use PhpOffice\PhpPresentation\Shape\Group;
use PhpOffice\PhpPresentation\Shape\Graphic;
use PhpOffice\PhpPresentation\Slide\Background;
use ZipArchive;

/**
 * PPTXSlideRenderer - Rend les slides PPTX en images 100% PHP
 * 
 * Utilise GD pour dessiner les slides. Chaque élément (texte, image) est positionné
 * et dessiné sur une image PHP.
 * 
 * Limites :
 * - Les styles complexes (ombres, dégradés) ne sont pas supportés
 * - Les animations ne sont pas supportées
 * - Les formes complexes sont simplifiées
 */
class PPTXSlideRenderer
{
    private string $tempDir;
    private int $defaultWidth;
    private int $defaultHeight;
    private ?string $fontPath;

    /**
     * @param int $defaultWidth Largeur par défaut des slides en pixels
     * @param int $defaultHeight Hauteur par défaut des slides en pixels
     * @param string|null $tempDir Répertoire temporaire pour les fichiers
     * @param string|null $fontPath Chemin vers une police TrueType (optionnel)
     */
    public function __construct(
        int $defaultWidth = 1024,
        int $defaultHeight = 768,
        string $tempDir = null,
        string $fontPath = null
    ) {
        $this->tempDir = $tempDir ?: sys_get_temp_dir() . '/omist_slide_render/';
        $this->defaultWidth = $defaultWidth;
        $this->defaultHeight = $defaultHeight;
        $this->fontPath = $fontPath;

        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    /**
     * Rend une présentation complète en images
     *
     * @param PhpPresentation|string $presentation Présentation PHPPresentation ou chemin vers PPTX
     * @param string $outputDir Répertoire de sortie pour les images
     * @param string $prefix Préfixe pour les noms de fichiers
     * @return array Tableau des chemins des images générées
     */
    public function renderPresentation($presentation, string $outputDir, string $prefix = 'slide'): array
    {
        // Charger la présentation si c'est un chemin
        if (is_string($presentation)) {
            $presentation = $this->loadPresentation($presentation);
        }

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $images = [];
        $slideIndex = 0;

        // Obtenir la taille de la slide depuis la présentation
        $slideSize = null;
        if (method_exists($presentation, 'getSlideSize') && $presentation->getSlideSize() !== null) {
            $slideSize = $presentation->getSlideSize();
        }

        foreach ($presentation->getAllSlides() as $slide) {
            $imagePath = $this->renderSlide($slide, $outputDir, $prefix, $slideIndex, $slideSize);
            if ($imagePath !== null) {
                $images[] = $imagePath;
            }
            $slideIndex++;
        }

        return $images;
    }

    /**
     * Rend une slide unique en image
     *
     * @param Slide $slide Slide à rendre
     * @param string $outputDir Répertoire de sortie
     * @param string $prefix Préfixe pour le nom de fichier
     * @param int $index Index de la slide
     * @param object|null $slideSize Taille de la slide (objet avec getX() et getY())
     * @return string|null Chemin vers l'image générée, ou null en cas d'erreur
     */
    public function renderSlide(Slide $slide, string $outputDir, string $prefix, int $index, $slideSize = null): ?string
    {
        // Obtenir les dimensions de la slide
        $width = $this->defaultWidth;
        $height = $this->defaultHeight;
        
        // Priorité 1: utiliser la taille passée en paramètre
        if ($slideSize !== null && method_exists($slideSize, 'getX') && method_exists($slideSize, 'getY')) {
            $width = $slideSize->getX() ?: $this->defaultWidth;
            $height = $slideSize->getY() ?: $this->defaultHeight;
        }
        // Priorité 2: essayer d'obtenir la taille depuis le slide layout
        else {
            try {
                $slideLayout = $slide->getSlideLayout();
                if ($slideLayout !== null && method_exists($slideLayout, 'getSlideSize')) {
                    $size = $slideLayout->getSlideSize();
                    if ($size !== null) {
                        $width = $size->getX() ?: $this->defaultWidth;
                        $height = $size->getY() ?: $this->defaultHeight;
                    }
                }
            } catch (\Exception $e) {
                // Utiliser les dimensions par défaut en cas d'erreur
            }
        }

        // Créer une image avec fond blanc
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            return null;
        }

        // Remplir avec du blanc
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        // Obtenir la couleur du background de la slide
        $bgColor = $this->getSlideBackgroundColor($slide);
        if ($bgColor !== null) {
            $bgColorAllocated = imagecolorallocate($image, $bgColor['r'], $bgColor['g'], $bgColor['b']);
            imagefill($image, 0, 0, $bgColorAllocated);
        }

        // Dessiner tous les éléments de la slide
        $shapes = $slide->getShapeCollection();
        foreach ($shapes as $shape) {
            $this->renderShape($image, $shape);
        }

        // Générer le nom de fichier
        $filename = $outputDir . '/' . $prefix . '_' . sprintf('%03d', $index) . '.png';

        // Sauvegarder l'image
        imagepng($image, $filename, 9);
        imagedestroy($image);

        return file_exists($filename) ? $filename : null;
    }

    /**
     * Charge une présentation depuis un fichier
     */
    private function loadPresentation(string $path): PhpPresentation
    {
        return \PhpOffice\PhpPresentation\IOFactory::load($path);
    }

    /**
     * Obtient la couleur de fond de la slide
     */
    private function getSlideBackgroundColor(Slide $slide): ?array
    {
        $background = $slide->getBackground();
        if ($background && method_exists($background, 'getFill') && $background->getFill()) {
            $fill = $background->getFill();
            if (method_exists($fill, 'getColor') && $fill->getColor()) {
                $color = $fill->getColor();
                if (method_exists($color, 'getRGB')) {
                    return $color->getRGB();
                }
            }
        }
        return null;
    }

    /**
     * Rend une forme sur une image
     */
    private function renderShape($image, AbstractShape $shape): void
    {
        // Shape de texte
        if ($shape instanceof RichText || method_exists($shape, 'getText')) {
            $this->renderTextShape($image, $shape);
        }
        // Shape d'image
        elseif ($shape instanceof Graphic || (class_exists('\PhpOffice\PhpPresentation\Shape\Drawing\AbstractDrawing') && $shape instanceof \PhpOffice\PhpPresentation\Shape\Drawing\AbstractDrawing)) {
            $this->renderImageShape($image, $shape);
        }
        // Groupe de shapes
        elseif ($shape instanceof Group) {
            $this->renderGroupShape($image, $shape);
        }
        // Autres formes (rectangle, ligne, etc.)
        elseif (method_exists($shape, 'getOffsetX') && method_exists($shape, 'getOffsetY')) {
            $this->renderGenericShape($image, $shape);
        }
    }

    /**
     * Rend une forme de texte
     */
    private function renderTextShape($image, $shape): void
    {
        // Obtenir le texte depuis un RichText (parcourir paragraphs et text runs)
        $text = '';
        if (method_exists($shape, 'getParagraphs')) {
            foreach ($shape->getParagraphs() as $paragraph) {
                if (method_exists($paragraph, 'getTextRuns')) {
                    foreach ($paragraph->getTextRuns() as $textRun) {
                        if (method_exists($textRun, 'getText')) {
                            $text .= $textRun->getText() . ' ';
                        }
                    }
                }
            }
            $text = trim($text);
        } elseif (method_exists($shape, 'getText')) {
            $text = $shape->getText();
        }
        
        if (empty($text)) {
            return;
        }

        $x = $shape->getOffsetX();
        $y = $shape->getOffsetY();
        $width = $shape->getWidth();
        $height = $shape->getHeight();

        // Obtenir les propriétés de style
        $font = $shape->getFont() ?? null;
        $fontSize = $font ? $font->getSize() : 12;
        $fontColor = $this->getColorFromFont($font);
        $bold = $font ? $font->getBold() : false;
        $italic = $font ? $font->getItalic() : false;
        $underline = $font ? $font->getUnderline() : false;

        // Couleur de texte
        $textColor = imagecolorallocate($image, $fontColor['r'], $fontColor['g'], $fontColor['b']);
        
        // Couleur blanche pour comparaison
        $white = imagecolorallocate($image, 255, 255, 255);

        // Déterminer la position du texte (approximative)
        // En GD, les coordonnées y commencent en haut à gauche
        $textY = $y + ($fontSize * 1.5);

        // Dessiner un rectangle autour du texte (pour le débogage)
        $rectColor = imagecolorallocate($image, 200, 0, 0); // Rouge pour mieux voir
        imagerectangle($image, $x, $y, $x + $width, $y + $height, $rectColor);

        // Forcer le texte à être visible - utiliser le noir si la couleur est trop claire
        if ($textColor === $white) {
            $textColor = imagecolorallocate($image, 0, 0, 0);
        }

        // Dessiner le texte - position centrale si les coordonnées sont hors limites
        $textY = $y + ($fontSize * 1.5);
        if ($textY < 0 || $textY > $height || $x < 0 || $x > $width) {
            // Coordonnées hors limites, centrer le texte
            $textY = $height / 2;
            $x = 10;
        }

        // Dessiner le texte
        if ($this->fontPath && file_exists($this->fontPath)) {
            imagettftext($image, $fontSize, 0, $x, $textY, $textColor, $this->fontPath, $text);
        } else {
            // Utiliser une police par défaut
            $gdFont = 5;
            $lines = explode("\n", wordwrap($text, 60));
            $currentY = $textY;
            
            foreach ($lines as $line) {
                imagestring($image, $gdFont, $x, $currentY, $line, $textColor);
                $currentY += imagefontheight($gdFont) + 2;
            }
        }
    }

    /**
     * Extrait la couleur depuis une police
     */
    private function getColorFromFont($font): array
    {
        if ($font === null) {
            return ['r' => 0, 'g' => 0, 'b' => 0];
        }

        if (method_exists($font, 'getColor') && $font->getColor()) {
            $color = $font->getColor();
            if (method_exists($color, 'getRGB')) {
                return $color->getRGB();
            }
        }

        return ['r' => 0, 'g' => 0, 'b' => 0];
    }

    /**
     * Rend une forme d'image
     */
    private function renderImageShape($image, $shape): void
    {
        // Obtenir le chemin de l'image
        $path = null;
        
        if (method_exists($shape, 'getPath')) {
            $path = $shape->getPath();
        }
        elseif (property_exists($shape, 'path')) {
            $path = $shape->path;
        }

        if (empty($path) || !file_exists($path)) {
            // L'image peut être intégrée dans le PPTX
            // On doit extraire l'image depuis l'archive
            return;
        }

        $x = $shape->getOffsetX();
        $y = $shape->getOffsetY();
        $width = $shape->getWidth();
        $height = $shape->getHeight();

        // Charger l'image source
        $imageData = $this->loadImage($path);
        if ($imageData === false) {
            return;
        }

        // Redimensionner et positionner l'image
        $this->drawImageOnCanvas($image, $imageData, $x, $y, $width, $height);
    }

    /**
     * Charge une image depuis un fichier
     */
    private function loadImage(string $path)
    {
        if (!file_exists($path)) {
            return false;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        switch ($ext) {
            case 'png':
                return imagecreatefrompng($path);
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($path);
            case 'gif':
                return imagecreatefromgif($path);
            case 'webp':
                return imagecreatefromwebp($path);
            default:
                return false;
        }
    }

    /**
     * Dessine une image sur le canvas
     */
    private function drawImageOnCanvas($canvas, $sourceImage, int $x, int $y, int $width, int $height): void
    {
        if ($sourceImage === false) {
            return;
        }

        // Calculer les dimensions actuelles de l'image source
        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);

        // Calculer les ratios
        $ratioX = $width / $srcWidth;
        $ratioY = $height / $srcHeight;
        $ratio = min($ratioX, $ratioY);

        // Calculer les nouvelles dimensions
        $newWidth = (int)($srcWidth * $ratio);
        $newHeight = (int)($srcHeight * $ratio);

        // Calculer la position pour centrer
        $offsetX = $x + (int)(($width - $newWidth) / 2);
        $offsetY = $y + (int)(($height - $newHeight) / 2);

        // Copier l'image avec redimensionnement
        imagecopyresampled(
            $canvas, $sourceImage,
            $offsetX, $offsetY, 0, 0,
            $newWidth, $newHeight,
            $srcWidth, $srcHeight
        );

        // Libérer la mémoire
        imagedestroy($sourceImage);
    }

    /**
     * Rend un groupe de formes
     */
    private function renderGroupShape($image, Group $group): void
    {
        $shapes = $group->getShapeCollection();
        foreach ($shapes as $shape) {
            $this->renderShape($image, $shape);
        }
    }

    /**
     * Rend une forme générique (rectangle, ligne, etc.)
     */
    private function renderGenericShape($image, $shape): void
    {
        $x = $shape->getOffsetX();
        $y = $shape->getOffsetY();
        $width = $shape->getWidth();
        $height = $shape->getHeight();

        // Obtenir la couleur de remplissage
        $fillColor = $this->getFillColor($shape);
        $lineColor = $this->getLineColor($shape);

        // Dessiner un rectangle (simplification)
        if ($width > 0 && $height > 0) {
            $color = imagecolorallocate($image, $fillColor['r'], $fillColor['g'], $fillColor['b']);
            imagefilledrectangle($image, $x, $y, $x + $width, $y + $height, $color);

            // Dessiner la bordure
            $borderColor = imagecolorallocate($image, $lineColor['r'], $lineColor['g'], $lineColor['b']);
            imagerectangle($image, $x, $y, $x + $width, $y + $height, $borderColor);
        }
    }

    /**
     * Obtient la couleur de remplissage d'une forme
     */
    private function getFillColor($shape): array
    {
        if (method_exists($shape, 'getFill') && $shape->getFill()) {
            $fill = $shape->getFill();
            if (method_exists($fill, 'getColor') && $fill->getColor()) {
                $color = $fill->getColor();
                if (method_exists($color, 'getRGB')) {
                    return $color->getRGB();
                }
            }
        }
        return ['r' => 255, 'g' => 255, 'b' => 255];
    }

    /**
     * Obtient la couleur de la ligne d'une forme
     */
    private function getLineColor($shape): array
    {
        if (method_exists($shape, 'getLine') && $shape->getLine()) {
            $line = $shape->getLine();
            if (method_exists($line, 'getColor') && $line->getColor()) {
                $color = $line->getColor();
                if (method_exists($color, 'getRGB')) {
                    return $color->getRGB();
                }
            }
        }
        return ['r' => 0, 'g' => 0, 'b' => 0];
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
