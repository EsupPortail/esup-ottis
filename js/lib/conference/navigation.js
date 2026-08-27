import { currentscroll, previousimage, scale } from './state.js';

import { formateresponse } from './utils.js';
import { ShowImage, nextPage, prevPage } from './pdf-viewer.js';

import { _ } from './dom-helpers.js';
import { addCsrfToken, createXhrRequest } from './network.js';



/** @module conference/navigation */



export function SkipForward() {
    if (window.currentPresentation.length > 0) {
        window.currentDiscours = (window.currentDiscours + 1) % window.currentPresentation.length;
        ShowImage(window.currentPresentation[window.currentDiscours]['Slide']);
        saveremotespeech();
    }
}

export function SkipBackward() {
    if (window.currentPresentation.length > 0) {
        window.currentDiscours = (window.currentDiscours - 1 + window.currentPresentation.length) % window.currentPresentation.length;
        ShowImage(window.currentPresentation[window.currentDiscours]['Slide']);
        saveremotespeech();
    }
}

export function Forward() {
    if (window.currentPresentation.length > 0) {
        window.currentDiscours = (window.currentDiscours + 1) % window.currentPresentation.length;
        addToNB(window.currentPresentation[window.currentDiscours]['Discours'], 0);
        ShowImage(window.currentPresentation[window.currentDiscours]['Slide']);
        saveremotespeech();
    }
}

export function Backward() {
    if (window.currentPresentation.length > 0) {
        window.currentDiscours = (window.currentDiscours - 1 + window.currentPresentation.length) % window.currentPresentation.length;
        addToNB(window.currentPresentation[window.currentDiscours]['Discours'], 0);
        ShowImage(window.currentPresentation[window.currentDiscours]['Slide']);
        saveremotespeech();
    }
}

export function SlideForward() {
    if (!window.currentPresentation || window.currentPresentation.length === 0) {
        // Fallback to PDF navigation if a PDF is loaded
        if (window.pdfDoc) {
            nextPage();
        } else {
            console.log('[NAV] SlideForward: No presentation or PDF loaded');
        }
        return;
    }
    let slide = window.currentPresentation[window.currentDiscours];
    if (slide && slide['Images'] && slide['Images'].length > 1) {
        let currentSlide = slide['Slide'];
        while (window.currentDiscours < window.currentPresentation.length - 1 && window.currentPresentation[window.currentDiscours]['Slide'] == currentSlide) {
            window.currentDiscours++;
        }
        ShowImage(slide['Slide']);
        saveremotespeech();
    }
}

export function SlideBackward() {
    if (!window.currentPresentation || window.currentPresentation.length === 0) {
        // Fallback to PDF navigation if a PDF is loaded
        if (window.pdfDoc) {
            prevPage();
        } else {
            console.log('[NAV] SlideBackward: No presentation or PDF loaded');
        }
        return;
    }
    let slide = window.currentPresentation[window.currentDiscours];
    if (slide && slide['Images'] && slide['Images'].length > 1) {
        let currentSlide = slide['Slide'];
        while (window.currentDiscours > 0 && window.currentPresentation[window.currentDiscours]['Slide'] == currentSlide) {
            window.currentDiscours--;
        }
        ShowImage(slide['Slide']);
        saveremotespeech();
    }
}

export function completeHandler(event,editable=false) {

    _('notebookcompleteformatted').innerHTML = DOMPurify.sanitize(formateresponse(event.target.responseText,editable));
    _('progressBar').value = 0;

    // Re-initialize the presentation
    previousimage = -1;
    window.currentDiscours = 0;
    if (window.currentPresentation.length > 0) {
        setTimeout(function() {
            _('stillwaiting').style.display='none';
            ShowImage(window.currentPresentation[0]['Slide']);
        }, 1000);
    }
}

export function SlideNotes() {

    let fileInput = _('ppt_presentation');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        alert('Please select a file first!');
        return;
    }
    let file = fileInput.files[0];
    let scriptfile = '';
    let time = 30000;

    // Determine the script based on file extension
    if (file.name.endsWith('pptx')) {
        scriptfile = typeof LoadPPTX !== 'undefined' && LoadPPTX
            ? 'ExtractSlidesNotes_PPTX_images.php'
            : 'ExtractSlidesNotes.php';
    } else if (file.name.endsWith('odp')) {
        scriptfile = 'ExtractSlidesNotes.php';
    } else if (file.name.endsWith('txt')) {
        scriptfile = 'ExtractSlidesNotesTxt.php';
    } else if (file.name.endsWith('html')) {
        scriptfile = 'ExtractSlidesNotesAppHTML.php';
        time = 300000;
    }

    if (scriptfile === '') {
        alert('Only pptx, odp, html, and txt files are allowed!');
        return;
    }

    _('stillwaiting').style.display = 'block';
    _('spinnertxt').innerHTML = 'Your presentation is not ready yet....<br/>Still working on it...';

    let formdata = new FormData();
    formdata.append('ppt_presentation', file);
    formdata.append('ACCESS', _('classroomid').value);
    addCsrfToken(formdata);

    let xhr = createXhrRequest('POST', scriptfile, {
        data: formdata,
        timeout: time,
        context: 'SlideNotes',
        onUploadProgress: progressHandler,
        onSuccess: completeHandler
    });
    xhr.ontimeout = (e) => {
        _('spinnertxt').innerHTML = 'Your PPTX presentation is too long to be imported directly.<br/>Please convert it first with the OMIST Toolbox and use the generated html file instead.';
        alert('Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.');
    };
    xhr.send(formdata);
}

export function reduceToolbar() {
    if (document.fullscreenElement) {
        //_('bar').style.transform = "scaleY(0.1)";
    } else {
        _('bar').style.transform = 'scaleY(1)';
    }
}

export function resizeSlides() {

    let slide = _('img_slides');
    if (!slide || !slide.width || !slide.height) {
        return;
    }
    let ratio = slide.width / slide.height;
    let aggrandissmentX = (window.innerWidth - 30) / slide.width;
    let aggrandissmentY = (window.innerHeight - 80) / slide.height;
    if (aggrandissmentX > aggrandissmentY) {
        /*
        slide.width=aggrandissmentY*slide.width;
        slide.style.width=aggrandissmentY*slide.width+"px";
        slide.height=aggrandissmentY*slide.height;
        slide.style.height=aggrandissmentY*slide.height+"px";
        */
        slide.style.transform = 'scaleX(' + aggrandissmentY + ') scaleY(' + aggrandissmentY + ')';
        //    slide.style.transform='scaleX('+aggrandissmentX+') scaleY('+aggrandissmentY+')';
    } else {
        /*    slide.width=aggrandissmentX*slide.width;
            slide.style.width=aggrandissmentX*slide.width+"px";
            slide.height=aggrandissmentX*slide.height;
            slide.style.height=aggrandissmentX*slide.height+"px";
        */
        slide.style.transform = 'scaleX(' + aggrandissmentX + ') scaleY(' + aggrandissmentX + ')';

        //slide.style.transform='scale('+aggrandissmentX+')';

    }

    // For wooclap and others
    //["wooclap", "extratab1", "extratab2", "extratab3", "extratab4", "visio"].map(resizeWindow)
}

export function setScrollvalues(l) {
    currentscroll = 1;
    let txtlength = Math.max(20, l);
    //txtlength=Math.min(200,txtlength);
    let duration = 55 * txtlength; // estimated duration in ms
    currentindent = scrollinterval / duration;
}