document.addEventListener('DOMContentLoaded', function() {
new QRCode(get("sharedqr"), {
    text: get("sharedlink").href,
    width: 256,
    height: 256,
    colorDark: "#0000ff",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.L
});

get("sharedqr").title = "";
get("sharedqr").onclick = function() {
    get("sharedqr").firstChild.toBlob(function(blob) {
        var url = URL.createObjectURL(blob);
        window.open(url);
    });
};

new QRCode(get("remoteqr"), {
    text: get("remotelink").href,
    width: 128,
    height: 128,
    colorDark: "#0000ff",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.L
});

get("remoteqr").title = "";
get("remoteqr").onclick = function() {
    get("remoteqr").firstChild.toBlob(function(blob) {
        var url = URL.createObjectURL(blob);
        window.open(url);
    });
};


on('bar', 'keydown keyup keypress', function(e) {
    //console.log(e.keyCode);
    e.preventDefault();
});
});


// Initialize drag-and-drop for partialtitre
// Using DOMContentLoaded to ensure all scripts are loaded
if (typeof makeDraggable === 'function') {
    document.addEventListener('DOMContentLoaded', function() {
        const partialTitreEl = document.getElementById('partialtitre');
        if (partialTitreEl) {
            makeDraggable(partialTitreEl);
        }
    });
}

ready(() => {
    // Dialog #dialog-invite will be auto-upgraded by upgradeLegacyDialogs()
    // or can be manually initialized with createDialog()
});



window.onbeforeunload = function(event) {
    event.returnValue = "Quit";
};





ready(() => {
    // Mail dialog functionality - will be refactored to use native dialog
    // var dialog, form;

    function EnvoiMail() {
        var FD = new FormData();
        FD.append('from', get('dialog_MailFrom').value);
        FD.append('to', get('dialog_MailTo').value.replaceAll(' - ', ','));
        FD.append('subject', get('dialog_MailSubject').value);
        //FD.append('body', get('dialog_MailBody').innerHTML);
        FD.append('body', get('dialog_MailBody').innerHTML);
        //FD.append('body', quillmail.getText());
        
        // Ajouter le token CSRF
        addCsrfToken(FD);
        
        var XHR = createXhrRequest('POST', 'sendamail.php', {
            onSuccess: function(xhr) {
                alert('Mail sent.');
            },
            onError: function(xhr, context) {
                alert('Error, the mail cannot be sent.');
            },
            data: FD,
            timeout: 10000,
            context: 'EnvoiMail'
        });
        XHR.addEventListener('error', function(event) {
            alert('Error, the mail cannot be sent.');
        });
        XHR.send(FD);
        closeDialog('dialog-mail');
    }

    // dialog = $("#dialog-mail").dialog({ ... });
    // Replaced by native dialog - will be auto-upgraded

    // form = dialog.find("form").on("submit", function(event) { ... });
    // Replaced by native event listener

});

ready(() => {
    // $( ".jqueryselect" ).selectmenu(); - Removed, no longer needed
});

document.addEventListener('submit', event => {
    event.preventDefault();
    // actual logic, e.g. validate the form
    //alert('Form submission cancelled.');
});

function FixTabOrderIssue() {
	var l = document.getElementsByClassName("tabbed");
    res="";
    for (var i in l) {
        if (l[i]) l[i].tabIndex=0;
        res+=i+"\n";
    }
}