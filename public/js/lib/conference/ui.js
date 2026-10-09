import { _ } from '../dom/selector.js';
import { hide, show } from '../dom/visibility.js';
import { openDialog } from '../dom/dialog.js';
import { getBuiltinLanguage } from '../state/auditor.js';
import { builtin_recognition, builtin_recognitionon, setBuiltinRecognitionon } from './config.js';
import { isBuiltinMicrosoftTranscriptionOn } from '../state/conference.js';
import { MicrosoftKey, MicrosoftRegion, doContinuousRecognition, stopRecognizer } from './microsoft.js';
import { StartSpeechRecognition } from './speech.js';
import { Traduction } from './translation.js';
import { t } from '../i18n.js';
/** @module conference/ui */

export function FullScreen() {
  const mybox = _('micon');
  const currentCheckedState = _('recognitionon').checked;
  const newCheckedState = !currentCheckedState;

  // Mettre à jour l'état AVANT d'arrêter la reconnaissance pour éviter les race conditions
  _('recognitionon').checked = newCheckedState;
  setBuiltinRecognitionon(newCheckedState);

  // Toggle l'état aria-pressed
  const isPressed = mybox.getAttribute('aria-pressed') === 'true';
  const newPressedState = !isPressed;
  mybox.setAttribute('aria-pressed', newPressedState);
  
  // Mettre à jour le label
  const newLabel = newPressedState ? t('conference.outil.micStop') : t('conference.outil.micStart');
  mybox.setAttribute('aria-label', newLabel);

  // Si on désactive le micro, arrêter la reconnaissance
  if (currentCheckedState && !newCheckedState) {
    if (!isBuiltinMicrosoftTranscriptionOn()) {
      builtin_recognition.abort();
    }
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

  // Toggle l'état aria-pressed
  const isPressed = mybox.getAttribute('aria-pressed') === 'true';
  const newPressedState = !isPressed;
  mybox.setAttribute('aria-pressed', newPressedState);
  
  // Mettre à jour le label
  const newLabel = newPressedState ? t('conference.outil.micStop') : t('conference.outil.micStart');
  mybox.setAttribute('aria-label', newLabel);

  // Si on désactive le micro, arrêter la reconnaissance
  if (currentCheckedState && !newCheckedState) {
    if (!isBuiltinMicrosoftTranscriptionOn()) {
      builtin_recognition.abort();
    }
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

export function toggleTextBandDisplay(forceFixed = false) {
  const useTextBandCheckbox = _('usetextband');
  // En plein écran, le bloc flottant est remplace par le bloc fixe (etat case decochee)
  const useTextBand = Boolean(useTextBandCheckbox && useTextBandCheckbox.checked)
    && !forceFixed
    && !document.fullscreenElement;
  const live = _('live');
  const floatLive = _('float-live');
  const textfixe = _('textfixe');
  const partialtitre = _('partialtitre');
  const mic = _('micon');

  if (useTextBand) {
    // Mode flottant : masquer la zone fixe, afficher le bloc flottant avec le micro
    const liveWidth = live ? live.offsetWidth : 0;
    if (partialtitre && textfixe) {
      partialtitre.innerHTML = window.DOMPurify.sanitize(textfixe.innerHTML);
    }
    if (live) {
      live.style.display = 'none';
    }
    if (floatLive) {
      floatLive.hidden = false;
      if (mic) {
        floatLive.insertBefore(mic, floatLive.firstChild);
      }
      // Largeur identique au bloc fixe (mesuree avant masquage)
      if (liveWidth > 0) {
        floatLive.style.width = `${Math.min(liveWidth, window.innerWidth - 16)}px`;
      }
      // Centrage horizontal a l'ouverture (position haute)
      floatLive.style.left = `${Math.max(8, (window.innerWidth - floatLive.offsetWidth) / 2)}px`;
      floatLive.style.top = '1rem';
    }
  } else {
    // Mode fixe : masquer le bloc flottant, retablir la zone fixe
    if (textfixe && partialtitre) {
      textfixe.innerHTML = window.DOMPurify.sanitize(partialtitre.innerHTML);
    }
    if (floatLive) {
      floatLive.hidden = true;
    }
    if (live) {
      live.style.display = 'flex';
    }
    if (mic && live) {
      live.insertBefore(mic, live.firstChild);
    }
  }
}
