
import { _, openDialog } from './dom-helpers.js';
import { builtin_language } from './state.js';
import { builtin_recognition, builtin_recognitionon, builtin_microsofttranscritionon, setBuiltinRecognitionon } from './config.js';
import { MicrosoftKey, MicrosoftRegion, doContinuousRecognition, stopRecognizer } from './microsoft.js';
/** @module conference/ui */



export function FullScreen() {
    let mybox = _('micon');
    const currentCheckedState = _('recognitionon').checked;
    const newCheckedState = !currentCheckedState;
    
    // Mettre à jour l'état AVANT d'arrêter la reconnaissance pour éviter les race conditions
    _('recognitionon').checked = newCheckedState;
    setBuiltinRecognitionon(newCheckedState);
    
    // Si on désactive le micro, arrêter la reconnaissance
    if (currentCheckedState && !newCheckedState) {
        if (!builtin_microsofttranscritionon) {
            builtin_recognition.abort();
        }
    }
    if (_('recognitionon').checked) {
        mybox.innerHTML = '<i class=\'material-icons blink\'>record_voice_over</i>';
    } else {
        mybox.innerHTML = '<i class=\'material-icons\'>voice_over_off</i>';
    }
    if (builtin_microsofttranscritionon) {
        if (builtin_recognitionon) {
            doContinuousRecognition(MicrosoftKey, MicrosoftRegion, builtin_language);
        } else {
            stopRecognizer();
        }
    }
    if (_('recognitionon').checked && !builtin_microsofttranscritionon) {
        StartSpeechRecognition(Traduction);
    }
    /* else {
          builtin_recognition.abort();
        }*/

}

export function SubWindow(){
    let sublink = _('sublink');
    if (!sublink) return; // Éléments supprimés
    event.preventDefault();
    let window_width = window.screen.width;
    let windowFeatures = 'status=no,location=no,toolbar=no,menubar=no,width=1200,height=110';
    let link = sublink.innerHTML.replaceAll('&amp;','&');
    window.open(link,'_blank',windowFeatures);
}

export function showET(obj, num) {
    if (obj.checked) {
        let url = _('extraurl' + num).value;
        if (url != '') {
            _('spanembedET' + num).innerHTML = 'Yes !';
            show('titleextratab' + num);
            _('extratab' + num).src = url;
        } else {
            alert('Enter your url first !');
            obj.checked = false;
            _('spanembedET' + num).innerHTML = 'No.';
            hide('titleextratab' + num);
        }
    } else {
        _('spanembedET' + num).innerHTML = 'No.';
        hide('titleextratab' + num);
    }
}

export function Micro() {
    let mybox = _('micon');
    const currentCheckedState = _('recognitionon').checked;
    const newCheckedState = !currentCheckedState;
    
    // Mettre à jour l'état AVANT d'arrêter la reconnaissance pour éviter les race conditions
    _('recognitionon').checked = newCheckedState;
    setBuiltinRecognitionon(newCheckedState);
    
    // Si on désactive le micro, arrêter la reconnaissance
    if (currentCheckedState && !newCheckedState) {
        if (!builtin_microsofttranscritionon) {
            builtin_recognition.abort();
        }
    }
    if (_('recognitionon').checked) {
        mybox.innerHTML = '<i class=\'material-icons blink\'>record_voice_over</i>';
    } else {
        mybox.innerHTML = '<i class=\'material-icons\'>voice_over_off</i>';
    }
    if (builtin_microsofttranscritionon) {
        if (builtin_recognitionon) {
            doContinuousRecognition(MicrosoftKey, MicrosoftRegion, builtin_language);
        } else {
            stopRecognizer();
            
        }
    }
    if (_('recognitionon').checked && !builtin_microsofttranscritionon) {
        //LaunchStudentReadText();
        StartSpeechRecognition(Traduction);
    }
    /*else {
            StopStudentReadText();
        }*/

}

export function Embedcode() {
    let url = _('chatlink').href;
    //  navigator.clipboard.writeText('<iframe style="width:100%; overflow: auto; height: 500px" src="'+url+'"></iframe>');
    //  alert("HTML code copied to clipboard !");
    _('embedcontent').value = '<iframe style="width:100%; overflow: auto; height: 500px" src="' + url + '"></iframe>';
}

export function sendnotebook() {
    _('dialog_MailFrom').value = 'bourdon-j@univ-nantes.fr';
    _('dialog_MailTo').value = 'your.mail@univ-nantes.fr';
    _('dialog_MailSubject').value = 'Content of the lesson ' + _('classroomid').value;
    _('dialog_MailBody').innerHTML = DOMPurify.sanitize(_('notebook').innerHTML);
    openDialog('dialog-mail');
}

export function opentab(e) {
    let url = e.innerHTML;
    let tabnumber = 1;
    while (tabnumber < 4 && _('extraurl' + tabnumber).value != '') tabnumber++;
    _('extraurl' + tabnumber).value = url;
    _('embedET' + tabnumber).checked = true;
    showET(_('embedET' + tabnumber), tabnumber);
}

export function toggleTextBandDisplay() {
    let useTextBand = _('usetextband').checked;
    let pdfViewer = _('pdf-viewer');
    let hasPresentation = pdfViewer && pdfViewer.style.visibility === 'visible';
    
    if (useTextBand) {
        // Masquer textBand et textfixe, afficher partialtitre
        _('textBand').style.display = 'none';
        _('partialtitre').style.display = 'block';
        if (!hasPresentation) {
            _('textfixe').style.display = 'none';
        }
        // Synchroniser le contenu
        _('partialtitre').innerHTML = DOMPurify.sanitize(_('textBand').innerHTML);
    } else {
        // Masquer partialtitre, afficher textBand et textfixe (si pas de presentation)
        _('partialtitre').style.display = 'none';
        _('textBand').style.display = 'block';
        if (!hasPresentation) {
            _('textfixe').style.display = 'block';
            // Synchroniser le contenu de textfixe avec partialtitre
            _('textfixe').innerHTML = DOMPurify.sanitize(_('partialtitre').innerHTML);
        }
    }
}