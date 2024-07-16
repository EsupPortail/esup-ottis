/**
 * ---EN---
 * @fileoverview Manager of favorites and weight (per point) systems for the carousel of scenarios.
 * 
 * @requires list element (<ul> HTML tag) class attribute to be set to "image-list".
 * @requires scenarios elements (<li> HTML tags).
 * ---FR---
 * @fileoverview Gestionnaire du système de favoris et du système par points du carrousel des scénarios.
 * 
 * @requires list (tag HTML <ul>) dont la classe doit être "image-list".
 * @requires scenarios (tags HTML <li>).
 */

/**
 * ---EN---
 * @summary General setup of each scenario's components behaviors.
 * ---FR---
 * @summary Configuration générale des différent comportements des éléments de chaque scénario.
 */
window.addEventListener('DOMContentLoaded', () => {
    const listOfScenarios = document.querySelectorAll('ul[class="image-list"] > li');
    
    setupDefaultOrder();

    for (let scenario of listOfScenarios) {
        let scenarioButton = scenario.querySelector('button[class="scenario-button"]');
        let scenarioFavOption = scenario.querySelector('button[class="favorite-button"]');

        setupScenarioBehavior(scenario);
        setupScenarioButtonBehavior(scenarioButton);
        setupScenarioButtonBehaviorForFavOption(scenarioFavOption);

        // mandatory default styling for favorite buttons.
        if (localStorage.getItem(scenarioFavOption.id)) {
            if (localStorage.getItem(scenarioFavOption.id) == 'on') {
                scenarioFavOption.innerHTML = "<i class=\"fa fa-star\"></i>";
                scenario.className += " favorite";
            }
        } else {
            localStorage.setItem(scenarioFavOption.id, 'off');
        }
    }
    setupRedirections();

    reorderScenarios();
});

/**
 * ---EN---
 * @summary Initialize the default order of scenarios in the carousel (only if missing).
 * ---FR---
 * @summary Initialise l'ordre par défaut des scénarios tel qu'ils apparaissent dans le carrousel (seulement si l'ordre n'est pas renseigné).
 */
function setupDefaultOrder() {
    const listOfScenarios = document.querySelectorAll('ul[class="image-list"] > li');
    let order = localStorage.getItem('order');

    if (!(order)) {
        let newOrder = [];

        for (scenario of listOfScenarios) {
            let number = scenario.querySelector('button[class="scenario-button"]').id.split('-')[1];
            newOrder.push('[' + number + ';' + 1 + ']');
        }
        localStorage.setItem('order', newOrder);
        order = localStorage.getItem('order');
    }
}

/**
 * ---EN---
 * @summary Display favorite button when a scenario is hovered (always displays the button if the scenario is already marked as favorite).
 * ---FR---
 * @summary Permet l'affichage du bouton favori si le scénario est survolé (le bouton sera affiché en permanance si le scénario est déjà en favori).
 * 
 * @param {*} scenario 
 */
function setupScenarioBehavior(scenario) {
    let scenarioFavOption = scenario.querySelector('button[class="favorite-button"]');
    
    scenario.addEventListener('mouseover', () => {
        scenarioFavOption.className += scenarioFavOption.classList.contains('visible') ? "" : " visible";
    });

    scenario.addEventListener('mouseleave', () => {
        if (!(document.querySelector('li:has(button[id="' + scenarioFavOption.id + '"])').classList.contains('favorite'))) {
            scenarioFavOption.className = scenarioFavOption.classList.contains('visible') ? scenarioFavOption.className.replace(' visible', '') : scenarioFavOption.className;
        }
    });
}

/**
 * ---EN---
 * @summary Adjust use count counter.
 * ---FR---
 * @summary Ajuste le compteur d'utilisation.
 * 
 * @param {*} scenarioButton 
 */
function setupScenarioButtonBehavior(scenarioButton) {
    scenarioButton.addEventListener('click', (event) => {
        addOneToUseCount(event.currentTarget.id.split('-')[1]);
    });
}

/**
 * ---EN---
 * @summary Add one point to use count (when the scenario is used).
 * ---FR---
 * @summary Ajoute un point au compteur d'utilisation (lorsqu'un scénario est utilisé).
 * 
 * @param {*} buttonNumber 
 */
function addOneToUseCount(buttonNumber) {
    let order = localStorage.getItem('order').split(',');
    let currentUsageCount;

    for (element of order) {
        if (element.slice(1, element.length - 1).split(';')[0] == buttonNumber) {
            currentUsageCount = element;
        }
    }

    let [number, value] = currentUsageCount.slice(1, currentUsageCount.length - 1).split(';');

    let newUsageCount = "[" + number + ";" + (parseInt(value) + 1) + "]";

    order[order.indexOf(currentUsageCount)] = newUsageCount;

    localStorage.setItem('order', order);

    reorderScenarios();
}

/**
 * ---EN---
 * @summary Set up favorite button behavior.
 * ---FR---
 * @summary Configuration du comportement du bouton de favori.
 * 
 * @param {*} scenarioFavOption 
 */
function setupScenarioButtonBehaviorForFavOption(scenarioFavOption) {
    scenarioFavOption.addEventListener('click', (event) => {
        let button = event.currentTarget;
        let scenario = document.querySelector('li:has(button[id="' + button.id + '"])');
        let state = localStorage.getItem(button.id);

        if (state == "off") {
            localStorage.setItem(button.id, 'on');
            scenario.className += " favorite";
            button.innerHTML = "<i class=\"fa fa-star\"></i>";
            reorderScenarios();
        } else {
            localStorage.setItem(button.id, 'off');
            scenario.className = scenario.className.replace(' favorite', '');
            button.innerHTML = "<i class=\"fa fa-star-o\"></i>";
            reorderScenarios();

        }
    });
}

/**
 * ---EN---
 * @summary Reorder scenarios order based on use count points.
 * ---FR---
 * @summary Réorganise l'ordre des scénarios en fonction des points du compteur d'utilisation.
 */
function reorderScenarios() {
    let order = localStorage.getItem('order').split(',');

    order = order.sort((a, b) => {
        return b.slice(1, b.length - 1).split(';')[1] - a.slice(1, a.length - 1).split(';')[1];
    });

    localStorage.setItem('order', order);

    if (document.querySelectorAll('li[class^="image-item favorite"]').length > 0) {
        reorderFavorites();
    }

    order = localStorage.getItem('order').split(',');

    for (element of order) {
        let number = element.slice(1, element.length - 1).split(';')[0];
        let scenario = document.querySelector('li:has(button[id="scenario-' + number + '-button"]');

        scenario.style.order = order.indexOf(element) + 1;
    }
}

/**
 * ---EN---
 * @summary Reorder scenarios marked as favorites to bring them to the first positions in the order.
 * ---FR---
 * @summary Réorganise l'ordre des scénarios en favoris de façon à les amener sur les premières positions dans l'ordre des scénarios.
 */
function reorderFavorites() {
    let order = localStorage.getItem('order').split(',');
    let favorites = document.querySelectorAll('li[class^="image-item favorite"]');

    for (element of favorites) {
        let button = element.querySelector('button[class^="favorite-button"]');
        let index;

        for (element of order) {
            if (element.slice(1, element.length - 1).split(';')[0] == button.id.split('-')[1]) {
                index = order.indexOf(element);
            }
        }
        let item = order[index];
        order.splice(index, 1);
        order.unshift(item);
    }

    localStorage.setItem('order', order);
}

/**
 * @summary Opens a pop-up window and adjusts the text according to the button clicked.
 * 
 * @param {string} modal_key string to identify the text to be placed in the pop-up.
 * @param {string} linkteacher the link created for the teacher application.
 * @param {string} linkstudent the link created for the student application.
 */
function openModal(modal_key, linkteacher="", linkstudent="") {
    const title_to_fill = document.querySelector('h2[id="mention-title"]');
    const content_to_fill = document.querySelector('div[id="mention-content"]');
    if (modal_key == 'access') {
        title_to_fill.innerHTML = "Accessibilité";
        content_to_fill.innerHTML = "<h3>Rappel de la loi</h3>"+
        "<p>L'article 47 de la loi n° 2005-102 du 11 février 2005 pour l'égalité des droits et des chances, la participation et la citoyenneté des personnes handicapées, fait de l'accessibilité une exigence pour tous les services de communication publique en ligne de l'État, des collectivités territoriales et des établissements publics qui en dépendent. Il stipule que les informations diffusées par ces services doivent être accessibles à tous.</p>"+
        "<p>Nantes Université s'efforce de respecter les principaux critères permettant de rendre son site accessible (recommandation du Référentiel Général d'Accessibilité pour les Administrations- RGAA 2.2.1).</p>"+
        "<hr><h3>Modification de la taille d'affichage</h3>"+
        "<p>Vous pouvez rétrécir ou agrandir l'affichage du site.<ul>"+
        "<li>pour agrandir la taille d'affichage du site, dans les navigateurs Google Chrome et Microsoft Edge, dérouler le menu en haut à droite du navigateur (3 points ou 3 barres) &gt; Zoom, puis choisir la taille avec le - (moins) et le + (plus). Vous pouvez aussi appuyer simultanément sur la touche Ctrl et bouger la molette de la souris.</li>"+
        "<li>pour revenir à la taille normal d'affichage, dans les navigateurs Google Chrome et Microsoft Edge, cliquer sur la loupe dans la barre d'URL en haut, puis sur \"réinitialiser\". </li>"+
        "</ul><p></p><hr>"+
        "<h3>Traductions</h3>"+
        "<p>Le site peut être consulté sous différentes langues. Il suffit de sélectionner la langue désirée dans le menu en haut à droite.</p>"+
        "<hr><h3>Comptatibilité navigateurs Internet</h3>"+
        "<p>OMIST a été testé et est compatible avec :</p><ul><li>Google Chrome : version 126 et supérieures</li><li>Microsoft Edge : version 126 et supérieures</li></ul>";
    } else if (modal_key == 'cookies') {
        title_to_fill.innerHTML = "Cookies";
        content_to_fill.innerHTML = "Ce site utilise des cookies internes nécessaires pour fonctionner. Ils permettent aux services principaux du site de fonctionner de manière optimale. Vous pouvez techniquement les bloquer en utilisant les paramètres de votre navigateur mais votre expérience sur le site risque d'être dégradée.";
    }else if (modal_key == 'generate_link'){
        title_to_fill.innerHTML = "Lien de connexion";
        content_to_fill.innerHTML = '<p>Voici le lien de connexion, à utiliser n\'importe quand : <br>'+linkteacher+'</p><p>'+linkstudent+'</p>';
    }

    document.querySelector(".modal-overlay").classList.remove("hide");
}

/**
 * @summary Close pop-up window.
 * 
 * @param {event} e an event.
 * @param {boolean} clickedOutside true if clicked outside the pop-up window.
 */
function closeModal(e, clickedOutside) {
    if (clickedOutside) {
        if (e.target.classList.contains("modal-overlay"))
            document.querySelector(".modal-overlay").classList.add("hide");
    } else document.querySelector(".modal-overlay").classList.add("hide");
}
 
/**
 * @summary Manages the carousel and navigation between scenarios.
 * 
 */
const initSlider = () => {
    const imageList = document.querySelector(".slider-wrapper .image-list");
    const imageItem = document.querySelector(".slider-wrapper .image-list .image-item");
    const slideButtons = document.querySelectorAll(".slider-wrapper .slide-button");
    const sliderScrollbar = document.querySelector(".container .slider-scrollbar");
    const scrollbarThumb = sliderScrollbar.querySelector(".scrollbar-thumb");
    const maxScrollLeft = imageList.scrollWidth - imageList.clientWidth;

    // Slide images according to the slide button clicks
    slideButtons.forEach(button => {
        button.addEventListener("click", () => {
            const direction = button.id === "prev-slide" ? -1 : 1;
            const scrollAmount = imageItem.clientWidth * direction;
            imageList.scrollBy({ left: scrollAmount, behavior: "smooth" });
        });
    });

     // Show or hide slide buttons based on scroll position
    const handleSlideButtons = () => {
        slideButtons[0].style.display = imageList.scrollLeft <= 0 ? "none" : "flex";
        slideButtons[1].style.display = imageList.scrollLeft >= maxScrollLeft ? "none" : "flex";
    }

    // Update scrollbar thumb position based on image scroll
    const updateScrollThumbPosition = () => {
        const scrollPosition = imageList.scrollLeft;
        const thumbPosition = (scrollPosition / maxScrollLeft) * (sliderScrollbar.clientWidth - scrollbarThumb.offsetWidth);
        scrollbarThumb.style.left = `${thumbPosition}px`;
    }

    // Call these two functions when image list scrolls
    imageList.addEventListener("scroll", () => {
        updateScrollThumbPosition();
        handleSlideButtons();
    });
}

window.addEventListener("resize", initSlider);
window.addEventListener("load", initSlider);
