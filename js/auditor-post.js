//$( "#tabs" ).tabs();
//$( "#enregistrer" ).button();
/*
  $( function() {
    $( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
  } );
*/



function retourOK(e) {
    var res = '';
    for (var p in e) res += p + '=' + e[p] + '\n';

}

function retourErreur() {}

window.onbeforeunload = function(event) {
    event.returnValue = "Quit";
};

function fixTitle() {
    var e = document.getElementById("titre");
    e.style.top = (window.innerHeight - e.style.height - 100 - 10) + 'px';

    e = document.getElementById("VOBanner");
    e.style.top = (window.innerHeight - e.style.height - 100 - 10) + 'px';
}

function MinTitlePos() {
    var e = document.getElementById("titre");
    var postop = 1 * (e.style.top.replace('px', ''));
    if (postop > window.innerHeight - 50) {
        e.style.top = (window.innerHeight - 50) + 'px';
    }
    var e = document.getElementById("VOBanner");
    var postop = 1 * (e.style.top.replace('px', ''));
    if (postop > window.innerHeight - 50) {
        e.style.top = (window.innerHeight - 50) + 'px';
    }

}

/**
 * Native Draggable - Vanilla JS implementation
 * Replaces jQuery draggableTouch plugin
 */
function makeDraggable(element) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    
    // Get the element's current position
    function getPosition(el) {
        const rect = el.getBoundingClientRect();
        return {
            left: rect.left + window.scrollX,
            top: rect.top + window.scrollY
        };
    }

    // Mouse events
    element.onmousedown = dragMouseDown;

    // Touch events
    element.ontouchstart = dragTouchStart;

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        const pos = getPosition(element);
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDrag;
        document.onmousemove = elementDrag;
    }

    function dragTouchStart(e) {
        e = e || window.event;
        e.preventDefault();
        const pos = getPosition(element);
        pos3 = e.touches[0].clientX;
        pos4 = e.touches[0].clientY;
        document.ontouchend = closeDrag;
        document.ontouchmove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;

        pos1 = pos3 - clientX;
        pos2 = pos4 - clientY;
        pos3 = clientX;
        pos4 = clientY;

        element.style.top = (element.offsetTop - pos2) + "px";
        element.style.left = (element.offsetLeft - pos1) + "px";
    }

    function closeDrag() {
        document.onmouseup = null;
        document.onmousemove = null;
        document.ontouchend = null;
        document.ontouchmove = null;
    }
}

// Initialize draggable elements when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    fixTitle();
    const titre = document.getElementById('titre');
    const voBanner = document.getElementById('VOBanner');
    if (titre) makeDraggable(titre);
    if (voBanner) makeDraggable(voBanner);

    // When language changes, force a refresh of the chat to re-translate with new language
    const classroomLanguage = document.getElementById('classroomlanguage');
    if (classroomLanguage) {
        classroomLanguage.addEventListener('change', function() {
            // Wait a bit for the language to be set, then refresh the chat
            setTimeout(function() {
                if (typeof readTextQ !== 'undefined') {
                    readTextQ();
                }
            }, 500);
        });
    }
});


// Word Cloud (DISABLED - tabs commented out)



/*
var bufferWC = [];
var sizebufferWC = 15;
var placebufferWC = 0;
var buffer_has_changed = true;

for (var i = 0; i < sizebufferWC; i++) bufferWC[i] = "";

function removeTags(str) {
    if ((str === null) || (str === ''))
        return false;
    else
        str = str.toString();
    return str.replace(/(<([^>]+)>)/ig, ' ');
}

function CreateString() {
    var previous = (placebufferWC - 1 + sizebufferWC) % sizebufferWC;
    var pprevious = (placebufferWC - 2 + sizebufferWC) % sizebufferWC;
    var ch = bufferWC.join(' ') + ' ' + bufferWC[previous] + ' ' + bufferWC[previous] + ' ' + bufferWC[pprevious];
    return ch;
}

function updateWC() {
    //if (buffer_has_changed) parseText(removeTags(bufferWC.join(' ')));
    if (buffer_has_changed) {
        parseText(CreateString());

    }
    buffer_has_changed = false;
}

function LaunchWC() {
    //setInterval(updateWC, 1000);
}
//setTimeout(LaunchWC, 3000);
*/




// Native implementations for dialogs and slider

// Global variables
var builtin_discoursrate = 1.0;

// Initialize dialogs and slider when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Get references to dialog elements
    var dialogMail = _('dialog-mail');
    var dialogChoose = _('dialog-choose');
    var sliderSpeed = _('slider_speed');
    var speedValue = 100; // Default value

    // Initialize slider if it exists
    if (sliderSpeed) {
        sliderSpeed.value = 100;
        sliderSpeed.addEventListener('input', refreshSpeed);
        sliderSpeed.addEventListener('change', refreshSpeed);
    }

    function refreshSpeed() {
        builtin_discoursrate = (sliderSpeed ? parseInt(sliderSpeed.value) : speedValue) / 100;
    }
    // Set initial value
    refreshSpeed();

    // Initialize dialog-choose close handler
    var dialogChooseForm = dialogChoose.querySelector('form');
    if (dialogChooseForm) {
        dialogChooseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            firsttime = false;
            firstclick = false;
            _('VoiceOutput').value = _('VoiceOutputchoose').value;
            setLanguageOutputAndRetranslate(_('VoiceOutput').value);
            _('synthesison').checked = false;
            _('arrowlanguage').style.visibility = 'hidden';
            _('arrowlanguage').style.display = 'none';
            _('lilanguage').style.animation = 'none';
            firstclick = true;
            dialogChoose.close();
        });
    }

    // Initialize dialog-mail cancel button
    var cancelBtn = dialogMail.querySelector('button[type="reset"]');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            dialogMail.close();
        });
    }
});

// Fonction pour ouvrir le dialog de choix de langue
window.openLanguageDialog = function() {
    var dialogChoose = _('dialog-choose');
    if (dialogChoose) {
        dialogChoose.showModal();
    }
};