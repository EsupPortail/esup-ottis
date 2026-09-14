// Basic functions

/**
 * @summary shortcut for document.getElementById
 * 
 * @param {string} el identifiant of an HTML DOM element.
 * @returns the object whose name is el
 */

function _(el) {
    return document.getElementById(el);
}

/**
 * @summary Hide an HTML object by changing its "display" parameter.
 * 
 * @param {HTML div} e an HTML DOM object
 */

function hide(e) {
    _(e).style.display = 'none';
}

/**
 * @summary Show an HTML object by changing its "display" parameter.
 * 
 * @param {HTML div} e an HTML DOM object
 */

function show(e) {
    _(e).style.display = 'block';
}

/**
 * @summary Array to string RGB color
 * 
 * @param {array} T an array of 3 8bits integers
 * @returns a string description of a color
 */

function rgb(T) {
    return "rgb(" + T[0] + "," + T[1] + "," + T[2] + ")";
}

/**
 * @summary Abort a given timeout. Utility function
 * 
 * @param {object} r a timeout object.
 */

function AbortTimeout(r,s) {
    r.timeout = 10000;

    r.ontimeout = (e) => {
        console.log("Timeout problem in "+s+" !")
        r.abort();
    };

    //setTimeout(function() {
    //    console.log("Timeout problem in "+s+" !")
    //    r.abort();
    //}, 10000);
}





/**
 * @summary setter for the input language
 * 
 * @param {number} i Language index.
 */

function setLanguageInput(i) {
    builtin_language = TLangInput[i];
}

/**
 * @summary setter for the output language
 * 
 * @param {number} i Language index.
 */

function setLanguageOutput(i) {
    builtin_voice = AllOutputVoices[i];
}




/**
 * @summary Clear the classroom div
 * 
 */

function ClearCR() {
    _('classroom').innerHTML = '';
    ScreenCR = [];
}

/**
 * @summary Reload the classroom queue
 * 
 */

function recharger() {
    ClearCR();
    inputnumberQ = 0;
    readTextQ();

}

/**
 * @summary Refresh the classroom queue
 * 
 */

function RefreshCR() {
    _('classroom').innerHTML = "";
    for (var i = 0; i < ScreenCR.length; i++) {
        if (ScreenCR[i] != undefined) {
            if (_('classroom').innerHTML == "") {
                _('classroom').innerHTML = ScreenCR[i] + '\n';
            } else {
                _('classroom').innerHTML = _('classroom').innerHTML + "<br/><br/>" + ScreenCR[i] + '\n';
            }
        }
    }
}

/**
 * @summary Write something to the classroom queue
 * 
 * @param {string} who surname of the person who writes.
 * @param {string} ch text that is to be written.
 * @param {number} order synchronisation parameter.
 */

function EcrireCR(who, ch, order) {
    if (!ColorsForCR[who]) {
        var r = Math.floor(Math.random() * 200 + 55);
        var g = Math.floor(Math.random() * 200 + 55);
        var b = Math.floor(Math.random() * 200 + 55);
        var bg = [r, g, b];
        var fg = [0, 0, 0];
        ColorsForCR[who] = [bg, fg, 20];
    }

    /*
      if (_('classroom').innerHTML == "")
        _('classroom').innerHTML='<span style="color:'+rgb(ColorsForCR[who][1])+'; background-color:'+rgb(ColorsForCR[who][0])+'; margin-left: '+ColorsForCR[who][2]+'px; ">'+(who+' : '+ch)+'<span>\n';
      else _('classroom').innerHTML=_('classroom').innerHTML+"<br/><br/>"+'<span style="display:inline;">'+order+'</span>\t<span style="color:'+rgb(ColorsForCR[who][1])+'; background-color:'+rgb(ColorsForCR[who][0])+'; margin-left: '+ColorsForCR[who][2]+'px;">'+(who+' : '+ch)+'<span>\n';
      */
    ScreenCR[order] = '<span style="color:' + rgb(ColorsForCR[who][1]) + '; background-color:' + rgb(ColorsForCR[who][0]) + '; margin-left: ' + ColorsForCR[who][2] + 'px; ">' + (who + ' : ' + ch) + '<span>';
    RefreshCR();
}

/**
 * @summary Computes the number of questions in the classroom queue
 * 
 * @returns Tthe number of questions in the classroom queue.
 */

function TailleCR() {
    return _('classroom').innerHTML.split('<br/>').length;
}



/**
 * @summary Resize the main windows
 * 
 * @param {string} wname an HTML DOM identifier
 */

function resizeWindow(wname) {
    var wc = _(wname);
    wc.style.width = (window.innerWidth - 20) + "px";
    wc.style.height = (window.innerHeight - 48) + "px";
    wc.style.top = "48px";
    wc.style.left = "0px";
}


/**
 * @summary Change the color of a HTML div
 * 
 * @param {string} boxname The div identifier.
 * @param {string} color Three colors separated by |.
 */

function ChangeColor(boxname, color) {
    var [bgcolor, bordercolor, textcolor] = color.split('|');
    var e = _(boxname);
    var opacity = _("selectopacity").value
    if (textcolor) e.style.color = 'rgb(' + textcolor + ')';
    else e.style.color = 'rgb(255,255,255)';
    e.style.backgroundColor = 'rgba(' + bgcolor + ',' + opacity + ')';
    e.style.boxShadow = '2px 2px 5px rgb(' + bgcolor + ')';
    e.style.textShadow = '2px 0 0 rgb(' + bordercolor + '), -2px 0 0 rgb(' + bordercolor + '), 0 2px 0 rgb(' + bordercolor + '), 0 -2px 0 rgb(' + bordercolor + '), 1px 1px rgb(' + bordercolor + '), -1px -1px 0 rgb(' + bordercolor + '), 1px -1px 0 rgb(' + bordercolor + '), -1px 1px 0 rgb(' + bordercolor + ')';
}

/**
 * @summary Change the opacity of a HTML div
 * 
 * @param {string} boxname The div identifier.
 * @param {string} opacity a [0,1] real number.
 */

function ChangeOpacity(boxname, opacity) {
    var e = _(boxname);
    var color = e.style.backgroundColor;
    var [red, green, blue] = color.substring(color.indexOf('(') + 1, color.lastIndexOf(')')).split(/,\s*/);
    var bgcolor = [red, green, blue];
    e.style.backgroundColor = 'rgba(' + bgcolor + ',' + opacity + ')';
}

/**
 * @summary Detect the browser type
 * 
 * @returns The comprehensive name of the browser
 */

function whichBrowser() {
    var ua = navigator.userAgent;
    var browsers = ["Chrome","Firefox","Safari","Edge","Opera"];
    for(var i=0; i<=browsers.length; i++) {
        if (ua.indexOf(browsers[i]) > 0) return browsers[i];
    }
    return "Unknown";
}

/**
 * @summary The function that is executed for downloading a file with the animation editor.
 * 
 * @param {string} filename a text value contained the file name.
 * @param {string} elText a text value contained the text to download.
 * @param {string} mimeType a text value contained the MIME type.
 */

function DownloadSpeechLesson(filename, elText, mimeType) {
    elText = elText.replaceAll("<br>", "\n");
    var link = document.createElement('a');
    mimeType = mimeType || 'text/plain';
    link.setAttribute('download', filename);
    link.setAttribute('href', 'data:' + mimeType  +  ';charset=utf-8,' + encodeURIComponent(elText));
    link.click();
}