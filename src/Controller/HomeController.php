<?php

namespace App\Controller;

/**
 * HomeController - Handles the homepage and landing page requests
 */
class HomeController extends BaseController
{
    /**
     * Display the homepage
     *
     * Renders the landing page with carrousel, styles, and scripts.
     * Sets up page title, carousel items data, and session marker.
     * Configures Swiper library and custom JavaScript for the homepage.
     *
     * @return void
     */
    public function index(): void
    {
        // Set page-specific data
        $this->viewData['pageTitle'] = 'OTTIS - Outil de Transcription et de Traduction Intelligente et Synchrone';

        // Define styles for this page
        $this->viewData['pageStyles'] = [
            'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css',
            'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200',
            './styles/index.css'
        ];

        // Define scripts for this page
        $this->viewData['pageScripts'] = [
            'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js',
            '/js/home-init.js'
        ];

        // Carrousel items data
        $this->viewData['carrouselItems'] = [
            [
                'icon' => '🚀',
                'title' => 'index.conferenceTitle',
                'text' => 'index.conferenceDesc',
                'buttonText' => 'index.conferenceButton',
                'buttonLink' => '/conference'
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

        // Set session marker
        $_SESSION['from'] = 'index';

        // Render the view
        $this->render('home/index');
    }
}
