<?php

namespace App\Controller;

class HomeController extends BaseController
{
    /**
     * Keep-alive : rafraichit la session PHP sans contenu de reponse (204).
     * Appele periodiquement par la page conference pour eviter l'expiration
     * de session pendant une intervention.
     */
    public function keepalive(): void
    {
        http_response_code(204);
        exit;
    }

    public function index(): void
    {
        $this->viewData['pageTitle'] = 'OTTIS - Outil de Transcription et de Traduction Intelligente et Synchrone';

        $this->viewData['pageStyles'] = [
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
            './styles/index.css'
        ];

        $this->viewData['pageScripts'] = [];

        $_SESSION['from'] = 'index';

        $this->render('home/index');
    }
}
