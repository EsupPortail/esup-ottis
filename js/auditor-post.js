function retourOK(e) {
    let res = '';
    for (let p in e) res += p + '=' + e[p] + '\n';

}

function retourErreur() {}

window.onbeforeunload = function(event) {
    event.returnValue = "Quit";
};

function fixTitle() {
    let e = get("titre");
    e.style.top = (window.innerHeight - e.style.height - 100 - 10) + 'px';

    e = get("VOBanner");
    e.style.top = (window.innerHeight - e.style.height - 100 - 10) + 'px';
}

function MinTitlePos() {
    let e = get("titre");
    let postop = 1 * (e.style.top.replace('px', ''));
    if (postop > window.innerHeight - 50) {
        e.style.top = (window.innerHeight - 50) + 'px';
    }
    e = get("VOBanner");
    postop = 1 * (e.style.top.replace('px', ''));
    if (postop > window.innerHeight - 50) {
        e.style.top = (window.innerHeight - 50) + 'px';
    }

}

// Initialize draggable elements when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    fixTitle();
    const titre = get('titre');
    const voBanner = get('VOBanner');
    if (titre) makeDraggable(titre);
    if (voBanner) makeDraggable(voBanner);

    // When language changes, force a refresh of the chat to re-translate with new language
    const classroomLanguage = get('classroomlanguage');
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
let bufferWC = [];
let sizebufferWC = 15;
let placebufferWC = 0;
let buffer_has_changed = true;

for (let i = 0; i < sizebufferWC; i++) bufferWC[i] = "";

function removeTags(str) {
    if ((str === null) || (str === ''))
        return false;
    else
        str = str.toString();
    return str.replace(/(<([^>]+)>)/ig, ' ');
}

function CreateString() {
    let previous = (placebufferWC - 1 + sizebufferWC) % sizebufferWC;
    let pprevious = (placebufferWC - 2 + sizebufferWC) % sizebufferWC;
    let ch = bufferWC.join(' ') + ' ' + bufferWC[previous] + ' ' + bufferWC[previous] + ' ' + bufferWC[pprevious];
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
builtin_discoursrate = 1.0;

// Initialize dialogs and slider when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Get references to dialog elements
    let dialogMail = _('dialog-mail');
    let dialogChoose = _('dialog-choose');
    let sliderSpeed = _('slider_speed');
    let speedValue = 100; // Default value

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
    let dialogChooseForm = dialogChoose.querySelector('form');
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
            
            // Start polling now that language is selected
            if (typeof window.startAuditorPolling === 'function') {
                window.startAuditorPolling();
            }
        });
    }

    // Initialize dialog-mail cancel button
    let cancelBtn = dialogMail.querySelector('button[type="reset"]');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            dialogMail.close();
        });
    }
});

// Fonction pour ouvrir le dialog de choix de langue
window.openLanguageDialog = function() {
    let dialogChoose = _('dialog-choose');
    if (dialogChoose) {
        dialogChoose.showModal();
    }
};