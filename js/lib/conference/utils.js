import { builtin_language, currentscroll, scale, timenew_question, setTimenewQuestion } from './state.js';

import { _, openDialog } from './dom-helpers.js';
import { addCsrfToken, createXhrRequest } from './network.js';

import { ROOM_TOKEN } from './compat.js';



/** @module conference/utils */



export function myalert(ch) {
    ch = ch.replaceAll('\n', '<br/>');
    _('dialog-invite-p').innerHTML = DOMPurify.sanitize(ch);
    openDialog('dialog-invite');
}

export function getNotebookContent(elementId) {
    let el = get(elementId);
    return el ? el.innerHTML.replace(/<br\/?>$/, '').substring(0, 100) + '...' : 'N/A';
}

export function b64toBlob(b64Data, contentType, sliceSize) {
    contentType = contentType || '';
    sliceSize = sliceSize || 512;

    let byteCharacters = atob(b64Data);
    let byteArrays = [];

    for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        let slice = byteCharacters.slice(offset, offset + sliceSize);

        let byteNumbers = new Array(slice.length);
        for (let i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }

        let byteArray = new Uint8Array(byteNumbers);

        byteArrays.push(byteArray);
    }

    let blob = new Blob(byteArrays, {
        type: contentType
    });
    return blob;
}

export function formateresponse(datatxt,editable=true) {

    try {
        let data = JSON.parse(datatxt);
    } catch (e) {
        console.error('Failed to parse JSON:', e, datatxt);
        return '<pre>Error: Invalid JSON response. Check server output.</pre>';
    }
    if (data && typeof data === 'object' && !Array.isArray(data) && data.error) {
        console.error('Server returned error object:', data);
        return '<pre>Error: ' + (data.error || 'Unknown error') + (data.details ? '\nDetails: ' + data.details : '') + '</pre>';
    }
    if (!Array.isArray(data)) {
        console.error('Unexpected response format:', data);
        return '<pre>Error: Unexpected response format from server.</pre>';
    }
    window.currentPresentation = [];
    window.currentDiscours = 0;
    //alert(datatxt);
    let res = '';
    // console.log(data);
    for (let i = 0; i < data.length; i++) {
        slide = data[i];
        let txtnotes = '';
        for (let j = 0; j < slide['Notes'].length; j++) {
            if (slide['Notes'][j].indexOf('[forward]') >= 0 || slide['Notes'][j].indexOf('[backward]') >= 0 || slide['Notes'][j].indexOf('[pause') >= 0 || slide['Notes'][j].indexOf('[resource') >= 0 || slide['Notes'][j].indexOf('[mouse') >= 0 || slide['Notes'][j].indexOf('[notification') >= 0 || slide['Notes'][j].indexOf('[wait') >= 0) {
                slide['Notes'][j] = '';
            } else {
                txtnotes = txtnotes + slide['Notes'][j] + ' <br/>';
            }
        }
        res += '<dl>';
        if (slide['Images']) {
            //alert("The presentation contains "+slide["Images"].length+" images");
            let toexec = '';
            for (let j = 1; j <= slide['Images'].length; j++) {
                toexec += "_('img_" + j + "').src='" + slide['Images'][j - 1] + "'; ";
            }
            setTimeout(toexec, 10);
        } else {
            //      res = res + '<dt><span onclick="addToNB(\''+txtnotes+'\',1); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
            //      res = res + '<dt><span onclick="addSlideToNB(\''+txtnotes+'\'); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
            res = res + '<dt>&nbsp;Slide #' + slide['Slide'] + '<img id="img_' + slide['Slide'] + '" width="50" src=""/></dt>\n';
            let flag = false;
            for (let j = 0; j < slide['Notes'].length; j++) {
                if (slide['Notes'][j].trim() != '') {
                    if (editable) {
                        res = res + '<dd>&nbsp;' + '<span id="SlideText'+i+'_'+j+'" contenteditable="true">'+slide['Notes'][j] + '</span>'+'</dd>\n';
                    } else {
                        res = res + '<dd><span onclick="addToNB(\'' + slide['Notes'][j] + '\',0); ShowImage(' + slide['Slide'] + '); currentDiscours=' + currentPresentation.length + '; saveremotespeech();">▶️</span>&nbsp;' + slide['Notes'][j]+'</dd>\n';
                    }
                    currentPresentation.push({
                        'Slide': slide['Slide'],
                        'Discours': slide['Notes'][j]
                    });
                    flag = true;
                }
            }
            if (!flag) currentPresentation.push({
                'Slide': slide['Slide'],
                'Discours': ''
            });
        }
        res += '</dl>';
    }
    //  alert(res);

    return res;
}

export function saveremotespeech() {
    if (currentPresentation.length > 0) {
        let currentDiscoursB = (currentDiscours - 1 + currentPresentation.length) % currentPresentation.length;
        let currentDiscoursA = (currentDiscours + 1) % currentPresentation.length;
        let txt = currentPresentation[currentDiscoursB]['Discours'] + ':|:' + currentPresentation[currentDiscours]['Discours'] + ':|:' + currentPresentation[currentDiscoursA]['Discours'];
        if (_('classroomid').value != '') {
            let f = new FormData();
            f.append('ROOMID', _('classroomid').value);
            f.append('SPEECH', txt);
            f.append('room_token', ROOM_TOKEN);
            addCsrfToken(f);

            let request = createXhrRequest('POST', 'savespeechforremote.php', {
                onSuccess: function(xhr) {
                    let data = xhr.responseText;
                },
                data: f,
                timeout: 10000,
                context: 'saveremotespeech'
            });
            request.send(f);
        }
    }
}

export function draw() {
    if (timenew_question > 0) {
        setTimenewQuestion(timenew_question - 1);
        setTimeout(draw, 1000);
    } else {
        let tab = _('questionicon');
        if (tab) {
            tab.innerHTML = '<i class=\'material-icons\'>thumb_up</i>';
            tab.style.backgroundColor = 'green';
        }
    }
}

export function EventNewQuestion() {
    if (timenew_question == 0) {
        setTimenewQuestion(10);
        let tab = _('questionicon');
        if (tab) {
            tab.innerHTML = '<i class=\'material-icons blink\'>warning</i>';
            tab.style.backgroundColor = 'orange';
            draw();
        }
    } else setTimenewQuestion(10);
}

export function playlesson() {}

/**
 * @summary Switch the fullscreen mode on and off
 * 
 */

function GoFS() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        //_('bar').style.transform = "scaleY(0.1)";
        //_('bar').style.bottom="10px";
        //resizeSlides();
    } else {
        document.exitFullscreen();
        _('bar').style.transform = 'scaleY(1)';
        //resizeSlides();
    }
}

/**
 * @summary Minimize toolbar when in full screen mode
 * 
 */

function reduceToolbar() {
    if (document.fullscreenElement) {
        //_('bar').style.transform = "scaleY(0.1)";
    } else {
        _('bar').style.transform = 'scaleY(1)';
    }
}

/**
 * @summary Resize the slides window
 * 
 */

function resizeSlides() {

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
window.onresize = resizeSlides;

/**
 * @summary (Pincode system removed - using QR code/direct link instead)
 * 
 */


/**
 * @summary Switch a given extratab visible or invisible
 * 
 * @param {HTMLInput checkbox} obj a checkbox.
 * @param {number} num the tab number.
 * @returns
 */

function showET(obj, num) {
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





/**
 * @summary Update the scroll bar value
 * 
 * @param {number} l current value.
 * @returns
 */

function setScrollvalues(l) {
    currentscroll = 1;
    let txtlength = Math.max(20, l);
    //txtlength=Math.min(200,txtlength);
    let duration = 55 * txtlength; // estimated duration in ms
    currentindent = scrollinterval / duration;
}