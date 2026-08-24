/**
 * conference-events.js
 * Centralized event listeners for conference.php
 * Dependencies: conference-func.js, common-func.js
 */

document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('classroomid')) return;

    // Language settings
    var classroomLanguage = document.getElementById('classroomlanguage');
    if (classroomLanguage) {
        classroomLanguage.addEventListener('change', function() {
            setLanguageSpeech(this.value);
        });
    }

    var subtitleLanguage = document.getElementById('subtitlelanguage');
    if (subtitleLanguage) {
        subtitleLanguage.addEventListener('change', function() {
            setLanguageSubtitle(this.value);
        });
    }

    // Text band toggle
    var useTextBand = document.getElementById('usetextband');
    if (useTextBand) {
        useTextBand.addEventListener('click', toggleTextBandDisplay);
    }

    // Subtitle toggle
    var subtitleOn = document.getElementById('subtitleon');
    if (subtitleOn) {
        subtitleOn.addEventListener('click', function() {
            var span = document.getElementById('spansubtitleon');
            if (span) {
                span.textContent = this.checked ? 'Yes !' : 'No.';
            }
        });
    }

    // Notebook downloads
    var buttonDownloadSpeechVO = document.getElementById('buttondownloadspeechVO');
    if (buttonDownloadSpeechVO) {
        buttonDownloadSpeechVO.addEventListener('click', function() {
            var classroomid = document.getElementById('classroomid');
            var notebookVO = document.getElementById('notebookVO');
            if (classroomid && notebookVO) {
                DownloadSpeechLesson('lessonVO_' + classroomid.value + '.txt', notebookVO.innerHTML);
            }
        });
    }

    var buttonDownloadSpeech = document.getElementById('buttondownloadspeech');
    if (buttonDownloadSpeech) {
        buttonDownloadSpeech.addEventListener('click', function() {
            var classroomid = document.getElementById('classroomid');
            var notebook = document.getElementById('notebook');
            if (classroomid && notebook) {
                DownloadSpeechLesson('lesson_' + classroomid.value + '.txt', notebook.innerHTML);
            }
        });
    }

    // Presentation file upload
    var presentationFile = document.getElementById('presentation_file');
    if (presentationFile) {
        presentationFile.addEventListener('change', handleFileSelect);
    }

    // PDF controls
    var pdfPrev = document.getElementById('pdf-prev');
    if (pdfPrev) pdfPrev.addEventListener('click', prevPage);

    var pdfNext = document.getElementById('pdf-next');
    if (pdfNext) pdfNext.addEventListener('click', nextPage);

    var pdfPageInput = document.getElementById('pdf-page-input');
    if (pdfPageInput) {
        pdfPageInput.addEventListener('change', function() {
            goToPage(this.value);
        });
    }

    var pdfFullscreenBtn = document.getElementById('pdf-fullscreen-btn');
    if (pdfFullscreenBtn) {
        pdfFullscreenBtn.addEventListener('click', togglePdfFullscreen);
    }

    // Microphone button
    var micon = document.getElementById('micon');
    if (micon) {
        micon.addEventListener('click', Micro);
    }

    // Compatibility & Chat Alert
    initMiconCompatibility();
    initConferenceChatAlert();
});

function initMiconCompatibility() {
    var micon = document.getElementById('micon');
    if (micon) {
        setInterval(function() {
            var icon = micon.querySelector('.blink');
            if (icon) {
                micon.classList.add('blink-active');
            } else {
                micon.classList.remove('blink-active');
            }
        }, 500);
    }
}

function initConferenceChatAlert() {
    setTimeout(function() {
        if (typeof initChatAlert === 'function') {
            var userName = 'Intervenant';
            initChatAlert(userName);
        } else {
            console.error('[Chat Alert] Conference: initChatAlert function not found!');
        }
    }, 500);
}
