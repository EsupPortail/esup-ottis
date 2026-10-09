/** @module conference/events */
import { setLanguageSpeech, setLanguageSubtitle } from './voices.js';
import { toggleTextBandDisplay, Micro } from './ui.js';
import { handleFileSelect } from './pdf-fullscreen.js';
import { setupPdfCloseListener, setupPdfFullscreenListeners } from './pdf-fullscreen/ui.js';
import { prevPage, nextPage, goToPage, togglePdfFullscreen } from './pdf-viewer.js';
import { DownloadSpeechLesson } from '../download.js';
import { initChatAlert, resetChatAlert } from '../chat/alert.js';
import { saveTextQ } from '../chat/main.js';
import { retranslateChatStream } from '../chat/sse.js';
import { safeSetInterval, safeSetTimeout } from '../utils/cleanup.js';
import { t } from '../i18n.js';

// Reactualiser les libelles dynamiques apres un changement de langue sans rechargement
document.addEventListener('ottis:languagechange', () => {
  const mic = document.getElementById('micon');
  if (mic) {
    const isPressed = mic.getAttribute('aria-pressed') === 'true';
    mic.setAttribute('aria-label', isPressed ? t('conference.outil.micStop') : t('conference.outil.micStart'));
  }
});

/**
 * Initialize all conference event listeners
 * This function sets up all DOM event listeners for the conference page
 */
export function initConferenceEvents() {
  // Language settings
  const classroomLanguage = document.getElementById('classroomlanguage');
  if (classroomLanguage) {
    classroomLanguage.addEventListener('change', function () {
      setLanguageSpeech(this.value);
      // Retraduire tous les messages du tchat dans la nouvelle langue
      retranslateChatStream();
    });
  }

  const subtitleLanguage = document.getElementById('subtitlelanguage');
  if (subtitleLanguage) {
    subtitleLanguage.addEventListener('change', function () {
      setLanguageSubtitle(this.value);
    });
  }

  // Text band toggle
  const useTextBand = document.getElementById('usetextband');
  if (useTextBand) {
    useTextBand.addEventListener('click', () => {
      toggleTextBandDisplay();
    });
  }

  // Theme du bloc de sous-titrage (Apparence)
  document.querySelectorAll('input[name="th"]').forEach((radio) => {
    const applyLiveTheme = () => {
      const isDark = radio.value === 'd';
      ['live', 'float-live'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
          el.style.setProperty('--sb', isDark ? '#111a2e' : '#fff');
          el.style.setProperty('--sf', isDark ? '#fff' : '#111a2e');
        }
      });
    };
    radio.addEventListener('change', applyLiveTheme);
    if (radio.checked) {
      applyLiveTheme();
    }
  });

  // Subtitle toggle
  const subtitleOn = document.getElementById('subtitleon');
  if (subtitleOn) {
    subtitleOn.addEventListener('click', function () {
      const span = document.getElementById('spansubtitleon');
      if (span) {
        span.textContent = this.checked ? 'Yes !' : 'No.';
      }
    });
  }

  // Notebook downloads
  const buttonDownloadSpeechVO = document.getElementById('buttondownloadspeechVO');
  if (buttonDownloadSpeechVO) {
    buttonDownloadSpeechVO.addEventListener('click', () => {
      const classroomid = document.getElementById('classroomid');
      const notebookVO = document.getElementById('notebookVO');
      if (classroomid && notebookVO) {
        DownloadSpeechLesson(`lessonVO_${classroomid.value}.txt`, notebookVO.innerHTML);
      }
    });
  }

  const buttonDownloadSpeech = document.getElementById('buttondownloadspeech');
  if (buttonDownloadSpeech) {
    buttonDownloadSpeech.addEventListener('click', () => {
      const classroomid = document.getElementById('classroomid');
      const notebook = document.getElementById('notebook');
      if (classroomid && notebook) {
        DownloadSpeechLesson(`lesson_${classroomid.value}.txt`, notebook.innerHTML);
      }
    });
  }

  // Presentation file upload
  const presentationFile = document.getElementById('presentation_file');
  if (presentationFile) {
    presentationFile.addEventListener('change', (event) => {
      handleFileSelect(event);
    });
  }

  // PDF controls
  const pdfPrev = document.getElementById('pdf-prev');
  if (pdfPrev) pdfPrev.addEventListener('click', () => prevPage());

  const pdfNext = document.getElementById('pdf-next');
  if (pdfNext) pdfNext.addEventListener('click', () => nextPage());

  const pdfPageInput = document.getElementById('pdf-page-input');
  if (pdfPageInput) {
    pdfPageInput.addEventListener('change', function () {
      goToPage(this.value);
    });
  }

  const pdfFullscreenBtn = document.getElementById('pdf-fullscreen-btn');
  if (pdfFullscreenBtn) {
    pdfFullscreenBtn.addEventListener('click', () => togglePdfFullscreen());
  }

  // Microphone button
  const micon = document.getElementById('micon');
  if (micon) {
    micon.addEventListener('click', () => {
      Micro();
    });
  } else {
  }

  // PDF close button
  setupPdfCloseListener();
  setupPdfFullscreenListeners();

  // Compatibility & Chat Alert
  initMiconCompatibility();
  initConferenceChatAlert();

  // Chat flottant : ouverture/fermeture (RGAA : focus gere)
  const fab = document.getElementById('fab');
  const chat = document.getElementById('chat');
  const cclose = document.getElementById('cclose');
  if (fab && chat) {
    fab.addEventListener('click', () => {
      // Bascule : re-clic sur le fab referme le tchat
      if (!chat.hidden) {
        chat.hidden = true;
        fab.setAttribute('aria-expanded', 'false');
        return;
      }
      chat.hidden = false;
      fab.setAttribute('aria-expanded', 'true');
      const badge = document.getElementById('badge');
      if (badge) {
        badge.textContent = '0';
        badge.hidden = true;
      }
      resetChatAlert();
      const chatTitle = document.getElementById('ct');
      if (chatTitle) chatTitle.focus();
    });
  }
  if (cclose && chat) {
    cclose.addEventListener('click', () => {
      chat.hidden = true;
      if (fab) {
        fab.setAttribute('aria-expanded', 'false');
        fab.focus();
      }
    });
  }
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape' || !chat || chat.hidden) return;
    chat.hidden = true;
    if (fab) fab.focus();
  });

  // Envoi de message : soumission du formulaire
  const cform = document.getElementById('cform');
  const cin = document.getElementById('cin');
  if (cform && cin) {
    cform.addEventListener('submit', (e) => {
      e.preventDefault();
      const text = cin.value.trim();
      if (text !== '') {
        saveTextQ(text);
        cin.value = '';
      }
    });
  }
}

/**
 * Initialize microphone icon compatibility (blinking effect)
 */
function initMiconCompatibility() {
  const micon = document.getElementById('micon');
  if (micon) {
    safeSetInterval(() => {
      const icon = micon.querySelector('.blink');
      if (icon) {
        micon.classList.add('blink-active');
      } else {
        micon.classList.remove('blink-active');
      }
    }, 500);
  }
}

/**
 * Initialize conference chat alert
 */
function initConferenceChatAlert() {
  safeSetTimeout(() => {
    const currentUser = globalThis.OMIST_USER || {};
    const currentUserNameEl = document.getElementById('CurrentUserDisplayName');
    const userName = currentUser.fullName || (currentUserNameEl && currentUserNameEl.value) || 'Intervenant';
    initChatAlert(userName);
  }, 500);
}
