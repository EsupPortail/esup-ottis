
import { timenew_question, setTimenewQuestion } from './state/core.js';

import { _, get } from '../dom/selector.js';
import { hide, show } from '../dom/visibility.js';
import { openDialog } from '../dom/dialog.js';
import { safeSetTimeout } from '../utils/cleanup.js';

import { setCurrentPresentation, setCurrentDiscours } from '../state/conference.js';

/** @module conference/utils */

export function myalert(ch) {
  ch = ch.replaceAll('\n', '<br/>');
  _('dialog-invite-p').innerHTML = window.DOMPurify.sanitize(ch);
  openDialog('dialog-invite');
}

export function getNotebookContent(elementId) {
  const el = get(elementId);
  return el ? `${el.innerHTML.replace(/<br\/?>$/, '').substring(0, 100)}...` : 'N/A';
}

export function b64toBlob(b64Data, contentType, sliceSize) {
  contentType = contentType || '';
  sliceSize = sliceSize || 512;

  const byteCharacters = atob(b64Data);
  const byteArrays = [];

  for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
    const slice = byteCharacters.slice(offset, offset + sliceSize);

    const byteNumbers = new Array(slice.length);
    for (let i = 0; i < slice.length; i++) {
      byteNumbers[i] = slice.charCodeAt(i);
    }

    const byteArray = new Uint8Array(byteNumbers);

    byteArrays.push(byteArray);
  }

  const blob = new Blob(byteArrays, {
    type: contentType,
  });
  return blob;
}

export function formateresponse(datatxt, editable = true) {
  let data;
  try {
    data = JSON.parse(datatxt);
  } catch (e) {
    console.error('Failed to parse JSON:', e, datatxt);
    return '<pre>Error: Invalid JSON response. Check server output.</pre>';
  }
  if (data && typeof data === 'object' && !Array.isArray(data) && data.error) {
    console.error('Server returned error object:', data);
    return `<pre>Error: ${data.error || 'Unknown error'}${data.details ? `\nDetails: ${data.details}` : ''}</pre>`;
  }
  if (!Array.isArray(data)) {
    console.error('Unexpected response format:', data);
    return '<pre>Error: Unexpected response format from server.</pre>';
  }
  const currentPresentation = [];
  setCurrentPresentation(currentPresentation);
  setCurrentDiscours(0);
  // alert(datatxt);
  let res = '';
  // console.log(data);
  for (let i = 0; i < data.length; i++) {
    const slide = data[i];
    let txtnotes = '';
    for (let j = 0; j < slide.Notes.length; j++) {
      if (slide.Notes[j].indexOf('[forward]') >= 0 || slide.Notes[j].indexOf('[backward]') >= 0 || slide.Notes[j].indexOf('[pause') >= 0 || slide.Notes[j].indexOf('[resource') >= 0 || slide.Notes[j].indexOf('[mouse') >= 0 || slide.Notes[j].indexOf('[notification') >= 0 || slide.Notes[j].indexOf('[wait') >= 0) {
        slide.Notes[j] = '';
      } else {
        txtnotes = `${txtnotes + slide.Notes[j]} <br/>`;
      }
    }
    res += '<dl>';
    if (slide.Images) {
      // alert("The presentation contains "+slide["Images"].length+" images");
      let toexec = '';
      for (let j = 1; j <= slide.Images.length; j++) {
        toexec += `_('img_${j}').src='${slide.Images[j - 1]}'; `;
      }
      safeSetTimeout(toexec, 10);
    } else {
      //      res = res + '<dt><span onclick="addToNB(\''+txtnotes+'\',1); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
      //      res = res + '<dt><span onclick="addSlideToNB(\''+txtnotes+'\'); ShowImage('+slide["Slide"]+'); currentDiscours='+currentPresentation.length+';">▶️</span>&nbsp;Slide #'+slide["Slide"]+'<img id="img_'+slide["Slide"]+'" width="50" src=""/></dt>\n';
      res = `${res}<dt>&nbsp;Slide #${slide.Slide}<img id="img_${slide.Slide}" width="50" src=""/></dt>\n`;
      let flag = false;
      for (let j = 0; j < slide.Notes.length; j++) {
        if (slide.Notes[j].trim() != '') {
          if (editable) {
            res = `${res}<dd>&nbsp;` + `<span id="SlideText${i}_${j}" contenteditable="true">${slide.Notes[j]}</span>` + '</dd>\n';
          } else {
            res = `${res}<dd><span onclick="addToNB('${slide.Notes[j]}',0); ShowImage(${slide.Slide}); currentDiscours=${currentPresentation.length};">▶️</span>&nbsp;${slide.Notes[j]}</dd>\n`;
          }
          currentPresentation.push({
            Slide: slide.Slide,
            Discours: slide.Notes[j],
          });
          flag = true;
        }
      }
      if (!flag) {
        currentPresentation.push({
          Slide: slide.Slide,
          Discours: '',
        });
      }
    }
    res += '</dl>';
  }
  //  alert(res);

  return res;
}

export function draw() {
  if (timenew_question > 0) {
    setTimenewQuestion(timenew_question - 1);
    safeSetTimeout(draw, 1000);
  } else {
    const tab = _('questionicon');
    if (tab) {
      tab.innerHTML = '<i class=\'material-icons\'>thumb_up</i>';
      tab.style.backgroundColor = 'green';
    }
  }
}

export function EventNewQuestion() {
  if (timenew_question == 0) {
    setTimenewQuestion(10);
    const tab = _('questionicon');
    if (tab) {
      tab.innerHTML = '<i class=\'material-icons blink\'>warning</i>';
      tab.style.backgroundColor = 'orange';
      draw();
    }
  } else setTimenewQuestion(10);
}

export function playlesson() {}

/**
 * @summary Resize the slides window
 *
 */

function resizeSlides() {
  const slide = _('img_slides');
  if (!slide || !slide.width || !slide.height) {
    return;
  }
  const aggrandissmentX = (window.innerWidth - 30) / slide.width;
  const aggrandissmentY = (window.innerHeight - 80) / slide.height;
  if (aggrandissmentX > aggrandissmentY) {
    /*
        slide.width=aggrandissmentY*slide.width;
        slide.style.width=aggrandissmentY*slide.width+"px";
        slide.height=aggrandissmentY*slide.height;
        slide.style.height=aggrandissmentY*slide.height+"px";
        */
    slide.style.transform = `scaleX(${aggrandissmentY}) scaleY(${aggrandissmentY})`;
    //    slide.style.transform='scaleX('+aggrandissmentX+') scaleY('+aggrandissmentY+')';
  } else {
    /*    slide.width=aggrandissmentX*slide.width;
            slide.style.width=aggrandissmentX*slide.width+"px";
            slide.height=aggrandissmentX*slide.height;
            slide.style.height=aggrandissmentX*slide.height+"px";
        */
    slide.style.transform = `scaleX(${aggrandissmentX}) scaleY(${aggrandissmentX})`;

    // slide.style.transform='scale('+aggrandissmentX+')';
  }

  // For wooclap and others
  // ["wooclap", "extratab1", "extratab2", "extratab3", "extratab4", "visio"].map(resizeWindow)
}
if (typeof window !== 'undefined') {
  window.addEventListener('resize', resizeSlides);
}
