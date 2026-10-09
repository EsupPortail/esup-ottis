<?php

namespace App\Controller;

/**
 * PageController - Pages statiques (mentions légales, données personnelles,
 * accessibilité, plan du site)
 *
 * Les pages sont disponibles uniquement en français pour l'instant,
 * rendues depuis views/pages/fr/.
 */
class PageController extends BaseController
{
    public function mentionsLegales(): void
    {
        $this->renderStaticPage('mentions-legales', 'Mentions légales - OTTIS');
    }

    public function donneesPersonnelles(): void
    {
        $this->renderStaticPage('donnees-personnelles', 'Données personnelles - OTTIS');
    }

    public function accessibilite(): void
    {
        $this->renderStaticPage('accessibilite', 'Accessibilité - OTTIS');
    }

    public function planDuSite(): void
    {
        $this->renderStaticPage('plan-du-site', 'Plan du site - OTTIS');
    }

    /**
     * Prépare et rend une page statique en français.
     *
     * @param string $slug Nom de la vue (sans extension) sous views/pages/fr/
     * @param string $title Titre de page
     */
    private function renderStaticPage(string $slug, string $title): void
    {
        $this->viewData['pageTitle'] = $title;
        $this->viewData['pageStyles'] = ['./styles/pages.css'];
        $this->viewData['pageScripts'] = [];

        $this->render('pages/fr/' . $slug);
    }
}
