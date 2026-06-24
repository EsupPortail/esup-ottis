window.onresize = resizeSlides;

$("body").tooltip();
$("#tabs").tabs();
$("#tabs-0").accordion({
    heightStyle: "panel"
});
$("#tabs-4").accordion({
    heightStyle: "panel"
});


//$( "#savenb" ).button();

new QRCode(document.getElementById("sharedqr"), {
    text: document.getElementById("sharedlink").href,
    width: 256,
    height: 256,
    colorDark: "#0000ff",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.L
});

document.getElementById("sharedqr").title = "";
document.getElementById("sharedqr").onclick = function() {
    document.getElementById("sharedqr").firstChild.toBlob(function(blob) {
        var url = URL.createObjectURL(blob);
        window.open(url);
    });
};

new QRCode(document.getElementById("remoteqr"), {
    text: document.getElementById("remotelink").href,
    width: 128,
    height: 128,
    colorDark: "#0000ff",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.L
});

document.getElementById("remoteqr").title = "";
document.getElementById("remoteqr").onclick = function() {
    document.getElementById("remoteqr").firstChild.toBlob(function(blob) {
        var url = URL.createObjectURL(blob);
        window.open(url);
    });
};


$('#bar').on('keydown keyup keypress', function(e) {
    //console.log(e.keyCode);
    e.preventDefault();
});

/**
 * jQuery Draggable Touch v0.6
 * Jonatan Heyman | http://heyman.info 
 *
 * Make HTML elements draggable by using uses touch events.
 * The plugin also has a fallback that uses mouse events, 
 * in case the device doesn't support touch events.
 * 
 * Licenced under THE BEER-WARE LICENSE (Revision 42):
 * Jonatan Heyman (http://heyman.info) wrote this file. As long as you retain this 
 * notice you can do whatever you want with this stuff. If we meet some day, and 
 * you think this stuff is worth it, you can buy me a beer in return.
 */
;
(function($) {
    $.fn.draggableTouch = function(actionOrSettings) {
        // check if the device has touch support, and if not, fallback to use mouse
        // draggableMouse which uses mouse events
        if (window.ontouchstart === undefined) {
            return this.draggableMouse(actionOrSettings);
        }

        if (typeof(actionOrSettings) == "string") {
            // check if we shall make it not draggable
            if (actionOrSettings == "disable") {
                this.unbind("touchstart.draggableTouch");
                this.unbind("touchmove.draggableTouch");
                this.unbind("touchend.draggableTouch");
                this.unbind("touchcancel.draggableTouch");

                this.trigger("dragdisabled");

                return this;
            }
        } else {
            var useTransform = actionOrSettings && actionOrSettings.useTransform;
        }

        this.each(function() {
            var element = $(this);
            var offset = null;
            var draggingTouchId = null;
            var end = function(e) {
                e.preventDefault();
                var orig = e.originalEvent;
                for (var i = 0; i < orig.changedTouches.length; i++) {
                    var touch = orig.changedTouches[i];
                    // the only touchend/touchcancel event we care about is the touch
                    // that started the dragging
                    if (touch.identifier != draggingTouchId) {
                        continue;
                    }
                    element.trigger("dragend", {
                        top: orig.changedTouches[0].pageY - offset.y,
                        left: orig.changedTouches[0].pageX - offset.x
                    });
                    draggingTouchId = null;
                }
            };

            element.bind("touchstart.draggableTouch", function(e) {
                e.preventDefault();
                var orig = e.originalEvent;
                // if this element is already being dragged, we can early exit, otherwise
                // we need to store which touch started dragging the element
                if (draggingTouchId) {
                    return;
                } else {
                    draggingTouchId = orig.changedTouches[0].identifier;
                }
                var pos = $(this).position();
                offset = {
                    x: orig.changedTouches[0].pageX - pos.left,
                    y: orig.changedTouches[0].pageY - pos.top
                };
                element.trigger("dragstart", pos);
            });
            element.bind("touchmove.draggableTouch", function(e) {
                e.preventDefault();
                var orig = e.originalEvent;

                for (var i = 0; i < orig.changedTouches.length; i++) {
                    var touch = orig.changedTouches[i];
                    // the only touchend/touchcancel event we care about is the touch
                    // that started the dragging
                    if (touch.identifier != draggingTouchId) {
                        continue;
                    }
                    if (useTransform) {
                        $(this).css({
                            "transform": "translate3d(" + (touch.pageX - offset.x) + "px, " + (touch.pageY - offset.y) + "px, 0px)",
                        });
                    } else {
                        $(this).css({
                            top: touch.pageY - offset.y,
                            left: touch.pageX - offset.x
                        });
                    }
                }
            });
            element.bind("touchend.draggableTouch touchcancel.draggableTouch", end);
        });
        return this;
    };

    /**
     * Draggable fallback for when touch is not available
     */
    $.fn.draggableMouse = function(actionOrSettings) {
        if (typeof(actionOrSettings) == "string") {
            // check if we shall make it not draggable
            if (actionOrSettings == "disable") {
                this.unbind("mousedown.draggableTouch");
                this.unbind("mouseup.draggableTouch");
                $(document).unbind("mousemove.draggableTouch");

                this.trigger("dragdisabled");

                return this;
            }
        } else {
            var useTransform = actionOrSettings && actionOrSettings.useTransform;
        }

        this.each(function() {
            var element = $(this);
            var offset = null;

            var move = function(e) {
                if (useTransform) {
                    element.css({
                        "transform": "translate3d(" + (e.pageX - offset.x) + "px, " + (e.pageY - offset.y) + "px, 0px)",
                    });
                } else {
                    element.css({
                        top: e.pageY - offset.y,
                        left: e.pageX - offset.x,
                    });
                }
            };
            var up = function(e) {
                element.unbind("mouseup.draggableTouch", up);
                $(document).unbind("mousemove.draggableTouch", move);
                element.trigger("dragend", {
                    top: e.pageY - offset.y,
                    left: e.pageX - offset.x
                });
            };
            element.bind("mousedown.draggableTouch", function(e) {
                var pos = element.position();
                offset = {
                    x: e.pageX - pos.left,
                    y: e.pageY - pos.top
                };
                $(document).bind("mousemove.draggableTouch", move);
                element.bind("mouseup.draggableTouch", up);
                element.trigger("dragstart", pos);
                e.preventDefault();
            });
        });
        return this;
    };
})(jQuery);


$(function() {
    fixTitle();
    // $("#titre").draggable({
    //     drag: function(event, ui) {
    //         var pos = ui.position;
    //         $("#titreleft").css({
    //             left: pos.left - 80,
    //             top: pos.top
    //         });
    //         $("#titrerightbis").css({
    //             left: pos.left + 1750,
    //             top: pos.top
    //         });
    //     }
    // });
    $("#titre").draggable();
    $("#partialtitre").draggableTouch();
    $("#studentfeedback").draggableTouch();
});

$(function() {
    $("#dialog-invite").dialog({
        autoOpen: false,
        modal: true,
        height: "auto",
        width: "auto",
        buttons: {
            Ok: function() {
                $(this).dialog("close");
            }
        }
    });
});



$("#question").keyup(function(event) {
    if (event.keyCode === 13) {
        sendclick();
    }
});

window.onbeforeunload = function(event) {
    event.returnValue = "Quit";
};

var sizepincodediv = 1;




$(function() {
    var dialog, form;

    function EnvoiMail() {
        var XHR = new XMLHttpRequest();
        var FD = new FormData();
        FD.append('from', document.getElementById('dialog_MailFrom').value);
        FD.append('to', document.getElementById('dialog_MailTo').value.replaceAll(' - ', ','));
        FD.append('subject', document.getElementById('dialog_MailSubject').value);
        //FD.append('body', document.getElementById('dialog_MailBody').innerHTML);
        FD.append('body', document.getElementById('dialog_MailBody').innerHTML);
        //FD.append('body', quillmail.getText());
        XHR.addEventListener('load', function(event) {
            alert('Mail sent.');
        });
        XHR.addEventListener('error', function(event) {
            alert('Error, the mail cannot be sent.');
        });
        XHR.open('POST', 'sendamail.php');
        XHR.send(FD);
        AbortTimeout(XHR,"EnvoiMail");
        dialog.dialog("close");
    }

    dialog = $("#dialog-mail").dialog({
        autoOpen: false,
        height: 500,
        width: 800,
        modal: true,
        buttons: {
            "Send": EnvoiMail,
            Cancel: function() {
                dialog.dialog("close");
            }
        },
        close: function() {}
    });

    form = dialog.find("form").on("submit", function(event) {
        event.preventDefault();
        EnvoiMail();
    });

});

$(function() {
    //$( ".jqueryselect" ).selectmenu();
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
