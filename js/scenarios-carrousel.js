/**
 * ---EN---
 * @fileoverview Manager of favorites and weight (per point) systems for the carousel of scenarios.
 * 
 * @requires list element (<ul> HTML tag) class attribute to be set to "scenarios".
 * @requires scenarios elements (<li> HTML tags).
 * ---FR---
 * @fileoverview Gestionnaire du système de favoris et du système par points du carrousel des scénarios.
 * 
 * @requires list (tag HTML <ul>) dont la classe doit être "scenarios".
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
 * ---EN---
 * @summary Set up all redirections (per scenario).
 * ---FR---
 * @summary Configuration des redirections (par scénario).
 */
function setupRedirections() {
    document.querySelector('[id="scenario-1-button"]').addEventListener('click', () => {
        window.open(root + "/index.php?myconfig=AANAYKCYNCNNNYNY");
    });

    document.querySelector('[id="scenario-2-button"]').addEventListener('click', () => {
        window.open(root + "/index.php?myconfig=ABYANKCNYCNNNYNY");
    });
    
    document.querySelector('[id="scenario-3-button"]').addEventListener('click', () => {
        let inputValue = document.querySelector('[id="scenario-3-input"]').value;

        window.open(root + "/index.php?myconfig=ABYANACNYCNNNYYY&tab=4" + (inputValue) ? "/index.php?myconfig=ABYANACNYCNNNYYY&tab=4&link=" + inputValue : "");
    });

    document.querySelector('[id="scenario-4-button"]').addEventListener('click', () => {
        let inputValue = document.querySelector('[id="scenario-4-input"]').value;

        window.open(root + "/index.php?myconfig=ABYANACNYCNNNYYY&tab=4" + (inputValue) ? "/index.php?myconfig=ABYANACNYCNNNYYY&tab=4&htmllink=" + inputValue : "");
    });

    document.querySelector('[id="scenario-5-button"]').addEventListener('click', () => {
        window.open(root + "/index.php?" + CreateLink());
    });

    document.querySelector('[id="direct-access-teacher"]').addEventListener('click', () => {
        window.open(root + "/index.php")
    });

    document.querySelector('[id="direct-access-tools"]').addEventListener('click', () => {
        window.open(root + "/OMIST_Tools/index.php")
    });

    document.querySelector('[id="direct-access-conv"]').addEventListener('click', () => {
        window.open(root + "/Translator2.html");
    });
}








const initSlider = () => {
    const imageList = document.querySelector(".slider-wrapper .image-list");
    const imageItem = document.querySelector(".slider-wrapper .image-list .image-item");
    const slideButtons = document.querySelectorAll(".slider-wrapper .slide-button");
    const sliderScrollbar = document.querySelector(".container .slider-scrollbar");
    const scrollbarThumb = sliderScrollbar.querySelector(".scrollbar-thumb");
    const maxScrollLeft = imageList.scrollWidth - imageList.clientWidth;
    
    // // Handle scrollbar thumb drag
    // scrollbarThumb.addEventListener("mousedown", (e) => {
    //     const startX = e.clientX;
    //     const thumbPosition = scrollbarThumb.offsetLeft;
    //     const maxThumbPosition = sliderScrollbar.getBoundingClientRect().width - scrollbarThumb.offsetWidth;
        
    //     // Update thumb position on mouse move
    //     const handleMouseMove = (e) => {
    //         const deltaX = e.clientX - startX;
    //         const newThumbPosition = thumbPosition + deltaX;

    //         // Ensure the scrollbar thumb stays within bounds
    //         const boundedPosition = Math.max(0, Math.min(maxThumbPosition, newThumbPosition));
    //         const scrollPosition = (boundedPosition / maxThumbPosition) * maxScrollLeft;
            
    //         scrollbarThumb.style.left = `${boundedPosition}px`;
    //         imageList.scrollLeft = scrollPosition;
    //     }

    //     // Remove event listeners on mouse up
    //     const handleMouseUp = () => {
    //         document.removeEventListener("mousemove", handleMouseMove);
    //         document.removeEventListener("mouseup", handleMouseUp);
    //     }

    //     // Add event listeners for drag interaction
    //     document.addEventListener("mousemove", handleMouseMove);
    //     document.addEventListener("mouseup", handleMouseUp);
    // });

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





(function ($) {
	$.fn.scrollList = function (numVisibleItems) {
		var visibleItems = numVisibleItems.visibleItems;
    var $carousel = $(this);
    var $slideItems = $("li", this);
    var $firstItem;
    var $width = $('ul').width();

    var setCurrentItems = function () {
      
         
      
    }
    
    

    var nextItem = function () {
    	 
       $('ul').animate({
         left: $('ul').position().left - $('ul').width() + "px"
       }, 200, function() {
         $('ul').css({
         		"left": "auto",
          	"right": "auto"
         });
       });

      $firstItem = $("li:nth-child(-n+" + visibleItems + ")", $carousel);
      $carousel.append($firstItem);
            
    }

    var previousItem = function () {
    	
      $('ul').animate({
        right: $('ul').position().left - $('ul').width() + "px"
      }, 200, function() {
        $('ul').css({
        	"left": "auto",
          "right": "auto"
        });
      });
            
			$lastItem = $("li:nth-last-child(-n+" + visibleItems + ")", $carousel);
      $carousel.prepend($lastItem);
      setCurrentItems();
    }
		
		function addArrows(){
			if ($slideItems.length > visibleItems) {
      	if($($carousel).parent().find(".next").length == 0 && 				
        
        	$($carousel).parent().find(".previous").length == 0){
						$carousel.parent().append('<span class="carousel-btn previous">&#10148;</span>');
         		$carousel.parent().append('<span class="carousel-btn next">&#10148;</span>');
        	}
          
					$($carousel).siblings(".next").click(nextItem);
          
          
					$($carousel).siblings(".previous").click(previousItem);
        }
		}

    setCurrentItems();
		addArrows();
	};
})(jQuery);

$(document).ready(function () {
	$(".num-list").scrollList({
		visibleItems: $(".num-list").data("numitems")
	});
});
