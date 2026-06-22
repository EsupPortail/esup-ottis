# Guide de Refactoring : auditeur.php → Architecture moderne

## Phase 1 : Nettoyage du code mort

### 1.1 Supprimer le code QR commenté (70 lignes)

**À supprimer dans auditeur.php (lignes ~245-314):**
```php
<!--
  <div id="tabs-4">
    <input type="button" value="Start Scanning" onclick="StartScan();">
    <input type="button" value="Stop Scanning" onclick="if (scanner) scanner.stop();">
    ...
    // Tout ce bloc
  </div>
-->
```

**Raison:** Code commenté depuis des années, fonction `StartScan()` orpheline

---

### 1.2 Nettoyer tabs Visio commentées

**À supprimer dans auditeur.php (lignes ~236-240):**
```php
<!--  	<div id="tabs-6">
    <div id="slideshow" style="border: solid 1px; height: 600px; overflow: auto;">
<iframe title="Fenêtre pour la visioconférence" id="visio" src = "" ...
    </div>
	</div>-->
```

---

### 1.3 Nettoyer Word Cloud (jamais fini)

**À supprimer ou déplacer en feature optionnelle:**
```php
<div id="tabs-13" class="wordcloud" style="display:none;">
    <!-- 40+ lignes jamais utilisées -->
</div>
```

---

### 1.4 Supprimer fonctions orphelines

**Chercher et supprimer:**
```php
<div id="showmenu" onclick="ShowMenu();" >  // ShowMenu() n'existe pas!
```

**Solution:** Vérifier si nécessaire ou utiliser popmenu existant

---

## Phase 2 : Refactoriser structure PHP

### 2.1 Adopter header.php centralisé

**AVANT (auditeur.php):**
```php
<?php 
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
?>
<!doctype html>
<html lang="fr">
<head>
    <title>Automatic translation...</title>
    ...
```

**APRÈS:**
```php
<?php 
require("./NoCache.php");
include("config.php");

$language = $_GET['lang'] ?? 'fr';
$translations = json_decode(file_get_contents("lang/{$language}.json"), true);
$pageTitle = "Omist - Student";
$pageStyles = [
    "jquery/flat_black/jquery-ui.min.css",
    "jquery/flat_black/extras.css",
    "styles/student.css"
];
$pageScripts = [
    "/js/student-def.js",
    "/js/common-func.js",
    "/js/student-func.js",
    "jquery/flat_black/external/jquery/jquery.js",
    "jquery/flat_black/jquery-ui.js"
];
$onloadFunctions = [
    "Init()",
    "loadVoicesWhenAvailable()",
];
$onloadScripts = implode(";\n    ", $onloadFunctions);

include_once __DIR__ . '/includes/header.php';
?>
```

---

### 2.2 Indexer `$T_lang` par code langue

**AVANT:**
```php
$T_lang=array();
$fd = fopen("supportedlanguages.csv","r");
while($line=fgets($fd,4096)) {
    $ligne=explode(";",$line);
    if (count($ligne)>2) 
        $T_lang[]=array(  // ❌ Index numérique!
            "name_fr"=>trim($ligne[2]),
            "name_vo"=>trim($ligne[0]),
            "code"=>trim($ligne[1]),
            "voice"=>"-1"
        );
}
fclose($fd);
```

**APRÈS:**
```php
$T_lang = array();
$fd = fopen("supportedlanguages.csv", "r");
while ($line = fgets($fd, 4096)) {
    if (trim($line) === '') {
        continue;  // Ignorer les lignes vides
    }
    $ligne = explode(";", $line);
    if (count($ligne) >= 3) {
        $code = trim($ligne[1]);  // ✅ Utiliser code comme clé
        $T_lang[$code] = [
            "name_fr" => trim($ligne[2]),
            "name_vo" => trim($ligne[0]),
            "code" => trim($ligne[1]),
            "voice" => "-1"
        ];
    }
}
fclose($fd);
```

**Bénéfice:** Accès `$T_lang['fr']` au lieu de boucle linéaire

---

## Phase 3 : Remplacer CSS/JS inline

### 3.1 Passer des echo à tableau

**AVANT:**
```php
<?php if ($theme != "") {
    echo '<link href="jquery/'.$theme.'/jquery-ui.min.css" rel="stylesheet">';
    echo '<link href="jquery/'.$theme.'/extras.css" rel="stylesheet">';
} else {
    echo '<link href="jquery/jquery-ui.min.css" rel="stylesheet">';
}
?>
```

**APRÈS:** (dans header.php template)
```php
<?php if (isset($pageStyles) && is_array($pageStyles)): ?>
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?php echo $style; ?>">
    <?php endforeach; ?>
<?php endif; ?>
```

**Application dans auditeur.php:**
```php
$pageStyles = [
    "jquery/flat_black/jquery-ui.min.css",
    "jquery/flat_black/extras.css",
    "styles/student.css"
];
```

---

### 3.2 Remplacer onload commentée

**AVANT:**
```html
<body onload="Init(); /*InitVoices(); setTimeout(InitVoices,1000);*/ 
             setTimeout(loadVoicesWhenAvailable,100); /*loadVoicesWhenAvailable();*/">
```

**APRÈS:**
```php
<?php
$onloadFunctions = [
    "Init()",
    "loadVoicesWhenAvailable()"
];
$onloadScripts = implode(";\n    ", $onloadFunctions);
?>
<!-- Dans header.php -->
<body onload="<?= $onloadScripts ?>">
```

---

## Phase 4 : Remplacer événements inline

### 4.1 Centraliser les onclick handlers

**AVANT:**
```html
<div id="micon" style="background-color: #EE0000;" 
     onclick="gotolive=true; CompleteSpeech(); this.style.backgroundColor='rgba(238,0,0,0.5)'; 
              setTimeout(()=> {document.getElementById('micon').style.backgroundColor='rgba(238,0,0,1)';},300);">
```

**APRÈS:** (dans student-func.js)
```js
// Attacher listener au lieu d'inline onclick
document.getElementById('micon').addEventListener('click', function() {
    gotolive = true;
    CompleteSpeech();
    this.style.backgroundColor = 'rgba(238,0,0,0.5)';
    setTimeout(() => {
        this.style.backgroundColor = 'rgba(238,0,0,1)';
    }, 300);
});
```

**HTML:**
```html
<div id="micon" title="Live translation">
    <i class='material-icons'>sensors</i>
    <span>LIVE</span>
</div>
```

---

### 4.2 Exemple de consolidation

**AVANT (multiple tags with onchange):**
```html
<select id="subtitlesfontsize" 
        onchange="document.getElementById('titre').style.fontSize = this.value;
                  document.getElementById('VOBanner').style.fontSize = this.value;">
</select>

<select id="selectopacity" 
        onchange="ChangeOpacity('titre', this.value); 
                  ChangeOpacity('VOBanner', this.value);">
</select>
```

**APRÈS (centralisé en JS):**
```js
// Dans student-func.js ou nouveau init-events.js
document.getElementById('subtitlesfontsize').addEventListener('change', function() {
    const fontSize = this.value;
    document.getElementById('titre').style.fontSize = fontSize;
    document.getElementById('VOBanner').style.fontSize = fontSize;
});

document.getElementById('selectopacity').addEventListener('change', function() {
    ChangeOpacity('titre', this.value);
    ChangeOpacity('VOBanner', this.value);
});
```

---

## Phase 5 : Migrer styles inline → classes CSS

### 5.1 Créer classes réutilisables

**Créer dans styles/student.css:**
```css
.hidden {
    display: none;
}

.visible {
    display: block;
}

.bordered {
    border: solid 1px;
}

.container-notebook {
    border: solid 1px;
    padding: 10px;
}

.settings-section {
    margin: 20px 0;
    padding: 10px;
    border-left: 4px solid #1abc9c;
}
```

**AVANT:**
```html
<span style="display:none"><label for="classroomid">...</label></span>
<span style="display:none;">Pending requests : <span id="nbrequests">0</span></span>
<div id="notebook" style="border: solid 1px;"></div>
```

**APRÈS:**
```html
<span class="hidden"><label for="classroomid">...</label></span>
<span class="hidden">Pending requests : <span id="nbrequests">0</span></span>
<div id="notebook" class="bordered"></div>
```

---

### 5.2 Utiliser media queries au lieu de styles fixes

**AVANT:**
```html
<style>
#bar2 {
    display:block;
}
#bar, #showmenu {
    display:none;
}
@media screen and (max-width: 40em) {
    #bar, #showmenu {
        display:none;
    }
    #bar2 {
        display:block;
    }
}
</style>
```

**APRÈS:** (dans styles/student.css)
```css
/* Desktop first */
#bar {
    display: flex;
}
#bar2 {
    display: none;
}
#showmenu {
    display: none;
}

/* Mobile */
@media screen and (max-width: 40em) {
    #bar {
        display: none;
    }
    #bar2 {
        display: block;
    }
    #showmenu {
        display: none;
    }
}
```

---

## Phase 6 : Vérifier variables inutilisées

### 6.1 Auditer `needSynchronize`

**Dans auditeur.php:**
```php
<?php
if ($synchronize) {
  echo "var needSynchronize=true;\n";
} else {
  echo "var needSynchronize=false;\n";
}
?>
```

**Question à résoudre:**
- Est-ce utilisé dans student-func.js?
- Search: `needSynchronize` dans tous les JS
- Si non trouvé: **SUPPRIMER**

### 6.2 Vérifier ShowMenu()

**Issue:** Fonction `ShowMenu()` appelée mais non trouvée

```html
<div id="showmenu" onclick="ShowMenu();">
```

**À faire:**
```bash
grep -r "ShowMenu" js/
```

**Si non trouvé:**
- Supprimer onclick
- Utiliser popmenu existant au lieu

---

## Phase 7 : Consolidation finale

### 7.1 Checklist de validation

```
AVANT LIVRAISON:
☐ Aucune ligne commentée mort (grep pour "<!--" / "// OLD")
☐ Aucun onclick inline
☐ Aucun style inline (except pour dynamique)
☐ Header.php utilisé
☐ $T_lang indexée par code
☐ Toutes variables JS utilisées
☐ Pas de console.log de débogage
☐ Tests: Student app functional
☐ Tests: Synchronization avec teacher app
☐ Tests: Multi-language select
```

### 7.2 Validation HTML/CSS

```bash
# Valider HTML
npx html-validate auditeur.php

# Vérifier CSS orphelins
grep -o 'style="[^"]*"' auditeur.php | sort | uniq

# Trouver tous les onclick
grep 'onclick=' auditeur.php | wc -l
```

---

## Ordre de priorité recommandé

1. **Jour 1:** Nettoyer code mort (QR, Visio, WordCloud)
2. **Jour 2:** Refactoriser structure PHP + header.php
3. **Jour 3:** Indexer $T_lang + remplacer CSS echo
4. **Jour 4:** Remplacer events inline + styles inline
5. **Jour 5:** Tests complets + validation

---

## Testing après refactoring

```php
<?php
// Test: Vérifier que $T_lang est bien indexée
assert(isset($T_lang['fr']), "French not in T_lang");
assert(isset($T_lang['en']), "English not in T_lang");

// Test: Aucune ligne n'a de code commenté
$content = file_get_contents('auditeur.php');
assert(strpos($content, '<!-- COMMENTED') === false, "Found commented code blocks");

// Test: Aucun onclick= inline
assert(substr_count($content, 'onclick=') < 5, "Too many inline onclick handlers");
?>
```

---

## Fichiers d'aide supplémentaires à créer

**À créer:**
1. `js/student-events.js` - Tous les event listeners centralisés
2. `styles/student-clean.css` - Remplace les styles inline
3. `php/language-loader.php` - Centralise CSV parsing
4. `.linting-rules.json` - Prévient regréssion

---

## Exemple migration complète (snippet)

**AVANT (auditeur.php complet):**
```php
<?php 
header('Pragma: no-cache');
// ... 20 lignes headers

if (isset($_GET["SYNC"])) { $synchronize = true; }
// ... parsing duplicate code

$theme="flat";
if (isset($_GET["theme"])) $theme=$_GET["theme"];

$T_lang=array();
// ... parsing CSV en boucle non indexée
?>
<!doctype html>
<html>
<head>
<link href="jquery/<?=$theme?>/...">  <!-- Echo dynamique -->
<!-- Centaines de styles inline -->
</head>
<body onload="Init(); /*InitVoices();*/ ">  <!-- Commenté -->
    <!-- onchange= onclick= partout -->
</body>
```

**APRÈS (auditeur.php moderne):**
```php
<?php 
require("./NoCache.php");
include("config.php");
$language = $_GET['lang'] ?? 'fr';

$pageTitle = "Omist - Student View";
$pageStyles = [
    "jquery/flat_black/jquery-ui.min.css",
    "styles/student.css"
];
$pageScripts = ["/js/student-def.js", "/js/student-func.js"];
$onloadFunctions = ["Init()", "loadVoicesWhenAvailable()"];
$onloadScripts = implode(";\n    ", $onloadFunctions);

// Charger langues indexées
$T_lang = loadLanguages();  // Nouvellz fonction

// Variables JS
$jsVars = ["supportedlanguages" => $T_lang];

include_once __DIR__ . '/includes/header.php';
?>

<!-- Vue simplifiée, pas de styles inline -->
<div id="titre"></div>
<div id="notebook" class="bordered"></div>
<!-- Events via listeners, pas onclick= -->
```

**Avec student-events.js:**
```js
document.getElementById('micon').addEventListener('click', handleMicrophone);
document.getElementById('synthesison').addEventListener('change', handleSynthesis);
// ... centralisé, testable, maintenable
```

---

## Conclusion

Ce guide permet une **migration progressive sans rupture de service**. La priorité est le nettoyage du code mort (qui ne casse rien) suivi de la consolidation architecture (qui améliore maintenabilité).
