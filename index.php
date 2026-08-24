<?php require("./NoCache.php"); ?>
<?php 
// Initialiser la sécurité (optionnel mais recommandé)
// require_once __DIR__ . '/Security/init.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$_SESSION["from"]="index";
$language = $_GET['lang'] ?? 'fr'; // Langue par défaut
$translations = json_decode(file_get_contents("lang/{$language}.json"), true);
$pageTitle = "Omist";
$pageStyles = [
    'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css',
    'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200',
    './styles/index.css'
];

// Tableau des scripts spécifiques à cette page
$pageScripts = [
    'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js',
    './js/index.js',
    './js/carroussel.js'
];

include_once __DIR__ . '/includes/header.php';


?>
<!-- Bandeau d'alerte (masqué par défaut) -->

<div class="swiper-container">
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <!-- Slider main container -->
    <div class="swiper">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
            <?php
                // Exemple de données pour le carrousel
                $carrouselItems = [
                    [
                        'icon' => '🚀',
                        'title' => 'index.conferenceTitle',
                        'text' => 'index.conferenceDesc',
                        'buttonText' => 'index.conferenceButton',
                        'buttonLink' => 'conference.php'
                    ],
                    [
                        'icon' => '📱',
                        'title' => 'Titre 2',
                        'text' => 'Description 2',
                        'buttonText' => 'Découvrir',
                        'buttonLink' => '#page2'
                    ],
                    [
                        'icon' => '💡',
                        'title' => 'Titre 3',
                        'text' => 'Description 3',
                        'buttonText' => 'En savoir plus',
                        'buttonLink' => '#page3'
                    ],
                    [
                        'icon' => '🎯',
                        'title' => 'Titre 4',
                        'text' => 'Description 4',
                        'buttonText' => 'Voir',
                        'buttonLink' => '#page4'
                    ]
                ];

                // Génération dynamique des slides
                foreach ($carrouselItems as $item) {
                    echo <<<HTML
                    <div class="swiper-slide">
                        <div class="slide-icon">{$item['icon']}</div>
                        <h3 class="translate" data-key="{$item['title']}">Titre</h3>
                        <p class="translate" data-key="{$item['text']}">Description</p>
                        <a href="{$item['buttonLink']}" class="slide-button translate" data-key="{$item['buttonText']}">Texte bouton</a>
                    </div>
                    HTML;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>