/** @module conference/events */
import { setLanguageSpeech, setLanguageSubtitle } from './voices.js';
import { toggleTextBandDisplay, Micro } from './ui.js';
import { handleFileSelect } from './pdf-fullscreen.js';
import { prevPage, nextPage, goToPage, togglePdfFullscreen } from './pdf-viewer.js';
import { DownloadSpeechLesson } from '../download.js';
import { initChatAlert } from '../chat/alert.js';

/**
 * Initialize all conference event listeners
 * This function sets up all DOM event listeners for the conference page
 */
export function initConferenceEvents() {
    // Language settings
    let classroomLanguage = document.getElementById('classroomlanguage');
    if (classroomLanguage) {
        classroomLanguage.addEventListener('change', function() {
            setLanguageSpeech(this.value);
        });
    }

    let subtitleLanguage = document.getElementById('subtitlelanguage');
    if (subtitleLanguage) {
        subtitleLanguage.addEventListener('change', function() {
            setLanguageSubtitle(this.value);
        });
    }

    // Text band toggle
    let useTextBand = document.getElementById('usetextband');
    if (useTextBand) {
        useTextBand.addEventListener('click', () => {
            toggleTextBandDisplay();
        });
    }

    // Subtitle toggle
    let subtitleOn = document.getElementById('subtitleon');
    if (subtitleOn) {
        subtitleOn.addEventListener('click', function() {
            let span = document.getElementById('spansubtitleon');
            if (span) {
                span.textContent = this.checked ? 'Yes !' : 'No.';
            }
        });
    }

    // Notebook downloads
    let buttonDownloadSpeechVO = document.getElementById('buttondownloadspeechVO');
    if (buttonDownloadSpeechVO) {
        buttonDownloadSpeechVO.addEventListener('click', function() {
            let classroomid = document.getElementById('classroomid');
            let notebookVO = document.getElementById('notebookVO');
            if (classroomid && notebookVO) {
                DownloadSpeechLesson('lessonVO_' + classroomid.value + '.txt', notebookVO.innerHTML);
            }
        });
    }

    let buttonDownloadSpeech = document.getElementById('buttondownloadspeech');
    if (buttonDownloadSpeech) {
        buttonDownloadSpeech.addEventListener('click', function() {
            let classroomid = document.getElementById('classroomid');
            let notebook = document.getElementById('notebook');
            if (classroomid && notebook) {
                DownloadSpeechLesson('lesson_' + classroomid.value + '.txt', notebook.innerHTML);
            }
        });
    }

    // Presentation file upload
    let presentationFile = document.getElementById('presentation_file');
    if (presentationFile) {
        presentationFile.addEventListener('change', (event) => {
            handleFileSelect(event);
        });
    }

    // PDF controls
    let pdfPrev = document.getElementById('pdf-prev');
    if (pdfPrev) pdfPrev.addEventListener('click', () => prevPage());

    let pdfNext = document.getElementById('pdf-next');
    if (pdfNext) pdfNext.addEventListener('click', () => nextPage());

    let pdfPageInput = document.getElementById('pdf-page-input');
    if (pdfPageInput) {
        pdfPageInput.addEventListener('change', function() {
            goToPage(this.value);
        });
    }

    let pdfFullscreenBtn = document.getElementById('pdf-fullscreen-btn');
    if (pdfFullscreenBtn) {
        pdfFullscreenBtn.addEventListener('click', () => togglePdfFullscreen());
    }

    // Microphone button
    let micon = document.getElementById('micon');
    if (micon) {
        micon.addEventListener('click', () => Micro());
    }

    // Compatibility & Chat Alert
    initMiconCompatibility();
    initConferenceChatAlert();
}

/**
 * Initialize microphone icon compatibility (blinking effect)
 */
function initMiconCompatibility() {
    let micon = document.getElementById('micon');
    if (micon) {
        setInterval(function() {
            let icon = micon.querySelector('.blink');
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
    setTimeout(function() {
        let userName = 'Intervenant';
        initChatAlert(userName);
    }, 500);
}
