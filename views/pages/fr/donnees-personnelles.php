<?php
/**
 * Données personnelles (RGPD) - version française
 * Structure alignée sur les pages "Données personnelles et cookies" de Nantes Université
 */
?>
<div class="ottis-static-page">
    <h1>Données personnelles</h1>

    <p>Dans le cadre de ses activités, Nantes Université collecte et traite des données personnelles de ses étudiants et de ses agents, dans le respect de la protection des données à caractère personnel.</p>
    <p>Conformément à la <strong>règlementation générale sur la protection des données de 2016 (RGPD)</strong> et à la <strong>loi Informatique et Libertés de 1978 modifiée</strong>, vous pouvez accéder aux données vous concernant, les rectifier, demander leur effacement, ou exercer votre droit à la limitation ou d'opposition au traitement de vos données.</p>

    <h2>Responsable de traitement</h2>
    <p>Nantes Université, 1 quai de Tourville, BP 13522, 44 035 Nantes Cedex 1.</p>

    <h2>Données collectées par OTTIS</h2>
    <p>L'application OTTIS traite les catégories de données suivantes :</p>
    <ul>
        <li><strong>Données d'authentification</strong> : identifiant institutionnel transmis par le service d'authentification centrale (CAS) de Nantes Université lors de la connexion ;</li>
        <li><strong>Contenus de session</strong> : transcriptions, traductions et notes générés pendant une intervention ;</li>
        <li><strong>Messages du chat</strong> : chaque message est diffusé et conservé accompagné du nom affiché choisi par son auteur (intervenant ou auditeur) ;</li>
        <li><strong>Fichiers pédagogiques</strong> : présentations (PowerPoint, PDF) temporairement déposées pour être diffusées et sous-titrées ;</li>
        <li><strong>Journal des dépôts de fichiers</strong> : le nom du fichier déposé, sa taille et son type sont consignés dans un journal d'exploitation ;</li>
        <?php $dpAppConfig = require (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 3)) . '/config/app.php'; ?>
        <?php if (!empty($dpAppConfig['matomo_url'])): ?>
        <li><strong>Mesure d'audience</strong> : statistiques de fréquentation anonymisées (<?php echo htmlspecialchars((string) parse_url($dpAppConfig['matomo_url'], PHP_URL_HOST)); ?>).</li>
        <?php endif; ?>
    </ul>

    <h2>Finalités et bases légales</h2>
    <p>Ces traitements sont nécessaires à la fourniture du service de sous-titrage et de traduction en temps réel d'OTTIS, dans le cadre des missions d'enseignement de Nantes Université (mission d'intérêt public).</p>

    <h2>Durées de conservation</h2>
    <p>Les contenus de session, les messages du chat, les fichiers déposés et le journal des dépôts ont un caractère temporaire : ils sont purgés automatiquement et quotidiennement des espaces de stockage temporaires. Les données d'authentification ne sont conservées que le temps de la session de travail (8 heures maximum).</p>

    <h2>Sous-traitants et transferts</h2>
    <p>La traduction automatique des contenus est assurée par le service DeepL, dont les serveurs sont situés au sein de l'Union européenne.</p>
    <p>La reconnaissance vocale utilisée pour la dictée s'appuie sur les fonctions du navigateur de l'intervenant (Web Speech API) : avec Chrome ou Edge, l'audio est transmis et traité par les serveurs de l'éditeur du navigateur (Google pour Chrome, hors Union européenne).</p>

    <h2>Vos droits</h2>
    <p>Pour exercer vos droits ou pour toute question sur le traitement de vos données, vous pouvez contacter la déléguée à la protection des données de l'établissement, Mme Laure (Prisca) KPON :</p>
    <ul>
        <li>par courriel : <a href="mailto:dpo@univ-nantes.fr">dpo@univ-nantes.fr</a> ;</li>
        <li>par courrier postal : Présidence de Nantes Université, à l'attention de la déléguée à la protection des données, 1 quai de Tourville, BP 13522, 44 035 Nantes Cedex 1.</li>
    </ul>
    <p>Une vérification de votre identité peut vous être demandée ; une réponse vous sera apportée dans un délai d'un mois à compter de la réception de votre demande.</p>
    <p>Compte tenu du caractère temporaire des données de session d'OTTIS (purge quotidienne des espaces de stockage), il se peut qu'aucune donnée ne soit encore présente au moment de votre demande : la réponse vous en informera alors.</p>
    <p>Si vous estimez, après nous avoir contactés, que vos droits ne sont pas respectés, vous pouvez adresser une réclamation à la CNIL. Pour cela, vous pouvez utiliser la plateforme de plainte en ligne de la CNIL : <a href="https://www.cnil.fr/fr/plaintes">www.cnil.fr/fr/plaintes</a>.</p>

    <p class="ottis-static-page__updated">Mis à jour le 23 novembre 2026.</p>
</div>
