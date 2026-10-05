<?php

namespace App\Controller;

class HomeController extends BaseController
{
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
