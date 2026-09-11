/** @module conference/events */
import { setLanguageSpeech, setLanguageSubtitle } from './voices.js';
import { toggleTextBandDisplay, Micro } from './ui.js';
import { handleFileSelect } from './pdf-fullscreen.js';
import { prevPage, nextPage, goToPage, togglePdfFullscreen } from './pdf-viewer.js';
import { DownloadSpeechLesson } from '../download.js';
import { initChatAlert } from '../chat/alert.js';
import { safeSetInterval, safeSetTimeout } from '../utils/cleanup.js';

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

  // Compatibility & Chat Alert
  initMiconCompatibility();
  initConferenceChatAlert();
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
    const userName = 'Intervenant';
    initChatAlert(userName);
  }, 500);
}
