/**
 * @summary Update the progress bar when a lesson is loaded
 * 
 * @param {event} event an event.
 */

function progressHandler(event) {
    var percent = (event.loaded / event.total) * 100;
    _("progressBar").value = Math.round(percent);
    //  _("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}

/**
 * @summary A function that is fired when a complete lesson is loaded.
 * 
 * @param {event} event an event.
 */

function completeHandler(event) {
    _("notebookcompleteformatted").innerHTML = formateresponse(event.target.responseText);
    _("progressBar").value = 0;

    // Re-initialize the presentation
    previousimage = -1;
    currentDiscours = 0;
    if (currentPresentation.length > 0) {
        setTimeout(function() {
            ShowImage(currentPresentation[0]['Slide']);
        }, 1000);
    }
}

/**
 * @summary The function that is executed for reading a new presentation
 * 
 */

function SlideNotesTools() {
    var file = _("ppt_presentation").files[0];
    var scriptfile = "";
    //  if (file.name.endsWith('pptx')) scriptfile = "ExtractSlidesNotes.php";
    if (file.name.endsWith('pptx'))
        if (LoadPPTX) scriptfile = "ExtractSlidesNotes_PPTX_images.php";
        else scriptfile = "ExtractSlidesNotes.php";
    if (file.name.endsWith('odp')) scriptfile = "ExtractSlidesNotes.php";
    if (file.name.endsWith('txt')) scriptfile = "ExtractSlidesNotesTxt.php";
    if (file.name.endsWith('html')) scriptfile = "ExtractSlidesNotesAppHTML.php";
    if (scriptfile == "") {
        alert('Only pptx, odp, html and txt files are allowed !');
        return -1;
    }
    var formdata = new FormData();
    formdata.append("ppt_presentation", file);
    var xhr = new XMLHttpRequest();

    xhr.open("POST", scriptfile);
    xhr.upload.addEventListener("progress", progressHandler, false);
    //  xhr.addEventListener("load", completeHandler, false);
    xhr.addEventListener("load", completeHandler, false);
    /*  xhr.onreadystatechange = function() { // Call a function when the state changes.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
          var response = JSON.parse(this.responseText);
          _('notebookcompleteformatted').innerHTML = "<pre>"+response+"</pre>";
          alert(response);
        }
      };
      */
    xhr.timeout = 30000;
    xhr.ontimeout = (e) => {
        console.log("Erreur Timeout !");
        alert("Your PPTX presentation cannot be converted directly within the online tool. Please convert it first with the OMIST Toolbox and use the generated html file instead.")
    };
    xhr.send(formdata);
    //AbortTimeout(xhr);
}
