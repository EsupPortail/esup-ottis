import { _ } from '../dom/selector.js';
import { hide, show } from '../dom/visibility.js';
import { openDialog } from '../dom/dialog.js';
import { getBuiltinLanguage } from '../state/auditor.js';
import { builtin_recognition, builtin_recognitionon, setBuiltinRecognitionon } from './config.js';
import { isBuiltinMicrosoftTranscriptionOn, isBuiltinExternalTranscriptionOn } from '../state/conference.js';
import { MicrosoftKey, MicrosoftRegion, doContinuousRecognition, stopRecognizer } from './microsoft.js';
import { StartSpeechRecognition } from './speech.js';
import { Traduction } from './translation.js';
/** @module conference/ui */

export function FullScreen() {
  const mybox = _('micon');
  const currentCheckedState = _('recognitionon').checked;
  const newCheckedState = !currentCheckedState;

  // Mettre à jour l'état AVANT d'arrêter la reconnaissance pour éviter les race conditions
  _('recognitionon').checked = newCheckedState;
  setBuiltinRecognitionon(newCheckedState);

  // Si on désactive le micro, arrêter la reconnaissance
  if (currentCheckedState && !newCheckedState) {
    if (!isBuiltinMicrosoftTranscriptionOn()) {
      builtin_recognition.abort();
    }
  }
  if (_('recognitionon').checked) {
    mybox.innerHTML = '<i class=\'material-icons blink\'>record_voice_over</i>';
  } else {
    mybox.innerHTML = '<i class=\'material-icons\'>voice_over_off</i>';
  }
  if (isBuiltinMicrosoftTranscriptionOn()) {
    if (builtin_recognitionon) {
      doContinuousRecognition(MicrosoftKey, MicrosoftRegion, getBuiltinLanguage());
    } else {
      stopRecognizer();
    }
  }
  if (_('recognitionon').checked && !isBuiltinMicrosoftTranscriptionOn()) {
    StartSpeechRecognition(Traduction);
  }
  /* else {
          builtin_recognition.abort();
        } */
}

export function SubWindow() {
  const sublink = _('sublink');
  if (!sublink) return; // Éléments supprimés
  event.preventDefault();
  const window_width = window.screen.width;
  const windowFeatures = 'status=no,location=no,toolbar=no,menubar=no,width=1200,height=110';
  const link = sublink.innerHTML.replaceAll('&amp;', '&');
  window.open(link, '_blank', windowFeatures);
}

export function showET(obj, num) {
  if (obj.checked) {
    const url = _(`extraurl${num}`).value;
    if (url != '') {
      _(`spanembedET${num}`).innerHTML = 'Yes !';
      show(`titleextratab${num}`);
      _(`extratab${num}`).src = url;
    } else {
      alert('Enter your url first !');
      obj.checked = false;
      _(`spanembedET${num}`).innerHTML = 'No.';
      hide(`titleextratab${num}`);
    }
  } else {
    _(`spanembedET${num}`).innerHTML = 'No.';
    hide(`titleextratab${num}`);
  }
}

export function Micro() {
  
  const mybox = _('micon');
  const recognitiononEl = _('recognitionon');
  
  if (!recognitiononEl) {
    return;
  }
  
  const currentCheckedState = recognitiononEl.checked;
  const newCheckedState = !currentCheckedState;

  // Mettre à jour l'état AVANT d'arrêter la reconnaissance pour éviter les race conditions
  try {
    _('recognitionon').checked = newCheckedState;
  } catch (e) {
    return;
  }
  
  try {
    setBuiltinRecognitionon(newCheckedState);
  } catch (e) {
    return;
  }

  // Si on désactive le micro, arrêter la reconnaissance
  if (currentCheckedState && !newCheckedState) {
    if (!isBuiltinMicrosoftTranscriptionOn()) {
      builtin_recognition.abort();
    }
  }
  if (_('recognitionon').checked) {
    mybox.innerHTML = '<i class=\'material-icons blink\'>record_voice_over</i>';
  } else {
    mybox.innerHTML = '<i class=\'material-icons\'>voice_over_off</i>';
  }
  if (isBuiltinMicrosoftTranscriptionOn()) {
    if (builtin_recognitionon) {
      doContinuousRecognition(MicrosoftKey, MicrosoftRegion, getBuiltinLanguage());
    } else {
      stopRecognizer();
    }
  }
  
  if (_('recognitionon').checked && !isBuiltinMicrosoftTranscriptionOn()) {
    try {
      StartSpeechRecognition(Traduction);
    } catch (e) {
    }
  } else {
  }
  /* else {
            StopStudentReadText();
        } */
}

export function Embedcode() {
  const url = _('chatlink').href;
  //  navigator.clipboard.writeText('<iframe style="width:100%; overflow: auto; height: 500px" src="'+url+'"></iframe>');
  //  alert("HTML code copied to clipboard !");
  _('embedcontent').value = `<iframe style="width:100%; overflow: auto; height: 500px" src="${url}"></iframe>`;
}

export function sendnotebook() {
  _('dialog_MailFrom').value = 'bourdon-j@univ-nantes.fr';
  _('dialog_MailTo').value = 'your.mail@univ-nantes.fr';
  _('dialog_MailSubject').value = `Content of the lesson ${_('classroomid').value}`;
  _('dialog_MailBody').innerHTML = window.DOMPurify.sanitize(_('notebook').innerHTML);
  openDialog('dialog-mail');
}

export function opentab(e) {
  const url = e.innerHTML;
  let tabnumber = 1;
  while (tabnumber < 4 && _(`extraurl${tabnumber}`).value != '') tabnumber++;
  _(`extraurl${tabnumber}`).value = url;
  _(`embedET${tabnumber}`).checked = true;
  showET(_(`embedET${tabnumber}`), tabnumber);
}

export function toggleTextBandDisplay() {
  const useTextBand = _('usetextband').checked;
  const pdfViewer = _('pdf-viewer');
  const hasPresentation = pdfViewer && pdfViewer.style.visibility === 'visible';

  if (useTextBand) {
    // Masquer textBand et textfixe, afficher partialtitre
    _('textBand').style.display = 'none';
    const partialtitre = _('partialtitre');
    if (partialtitre) {
      partialtitre.style.display = 'block';
      partialtitre.classList.remove('hidden');
    }
    if (!hasPresentation) {
      _('textfixe').style.display = 'none';
    }
    // Synchroniser le contenu
    if (partialtitre) {
      partialtitre.innerHTML = window.DOMPurify.sanitize(_('textBand').innerHTML);
    }
  } else {
    // Masquer partialtitre, afficher textBand et textfixe (si pas de presentation)
    const partialtitre = _('partialtitre');
    if (partialtitre) {
      partialtitre.style.display = 'none';
      partialtitre.classList.add('hidden');
    }
    _('textBand').style.display = 'block';
    if (!hasPresentation) {
      _('textfixe').style.display = 'block';
      // Synchroniser le contenu de textfixe avec partialtitre
      if (partialtitre) {
        _('textfixe').innerHTML = window.DOMPurify.sanitize(partialtitre.innerHTML);
      }
    }
  }
}
