//$( "#tabs" ).tabs();
//$( "#enregistrer" ).button();
/*
  $( function() {
    $( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
  } );
*/

window.onresize=resizeSlides;


function retourOK(e) {
    var res = '';
    for (var p in e) res += p + '=' + e[p] + '\n';
    console.log(res);
}

function retourErreur() {}

window.onbeforeunload = function(event) {
    event.returnValue = "Quit";
};

window.addEventListener('unload', function(event) {
    saveTextQ("[newstudentlived]");
});

function fixTitle() {
    var e = document.getElementById("titre");
    e.style.top = (window.innerHeight - e.style.height - 100 - 10) + 'px';

    e = document.getElementById("VOBanner");
    e.style.top = (window.innerHeight - e.style.height - 100 - 10) + 'px';
}

function MinTitlePos() {
    var e = document.getElementById("titre");
    var postop = 1 * (e.style.top.replace('px', ''));
    if (postop > window.innerHeight - 50) {
        e.style.top = (window.innerHeight - 50) + 'px';
    }
    var e = document.getElementById("VOBanner");
    var postop = 1 * (e.style.top.replace('px', ''));
    if (postop > window.innerHeight - 50) {
        e.style.top = (window.innerHeight - 50) + 'px';
    }

}

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
    //$( "#titre" ).draggable();
    $("#titre").draggableTouch();
    $("#VOBanner").draggableTouch();

});

function sendclick() {
    var tosend = document.getElementById('question').value.replaceAll('\n', '').trim();
    //alert("Will send : ("+tosend+")");
    if (tosend != "") saveTextQ(tosend);
    document.getElementById('question').value = '';
}

$("#question").keyup(function(event) {
    if (event.keyCode === 13) {
        sendclick();
    }
});

$(function() {
    $("#showmenu").tooltip();
    $(".tooltip").tooltip();

});


// Word Cloud



var bufferWC = [];
var sizebufferWC = 15;
var placebufferWC = 0;
var buffer_has_changed = true;

for (var i = 0; i < sizebufferWC; i++) bufferWC[i] = "";

function removeTags(str) {
    if ((str === null) || (str === ''))
        return false;
    else
        str = str.toString();
    return str.replace(/(<([^>]+)>)/ig, ' ');
}

function CreateString() {
    var previous = (placebufferWC - 1 + sizebufferWC) % sizebufferWC;
    var pprevious = (placebufferWC - 2 + sizebufferWC) % sizebufferWC;
    var ch = bufferWC.join(' ') + ' ' + bufferWC[previous] + ' ' + bufferWC[previous] + ' ' + bufferWC[pprevious];
    return ch;
}

function updateWC() {
    //if (buffer_has_changed) parseText(removeTags(bufferWC.join(' ')));
    if (buffer_has_changed) {
        parseText(CreateString());
        console.log("WC with : " + CreateString());
    }
    buffer_has_changed = false;
}

function LaunchWC() {
    //setInterval(updateWC, 1000);
}
//setTimeout(LaunchWC, 3000);




$(function() {
    var dialog, form, dialogchoose;



    dialog = $("#dialog-mail").dialog({
        autoOpen: false,
        height: 500,
        width: 800,
        modal: false,
        buttons: {
            "Send": EnvoiMail,
            Cancel: function() {
                dialog.dialog("close");
            }
        },
        close: function() {}
    });
    dialogchoose = $("#dialog-choose").dialog({
        closeText: "hide",
        closeOnEscape: false,
        hide: { effect: "puff", duration: 1000 },
        autoOpen: true,
        //height: 800,
        //width: 600,
        modal: true,
        buttons: {
            "Go": function() {
                $("#dialog-choose").dialog("close");
            }
        },
        close: function() {
            firsttime=false;
            firstclick=false;
            resizeSlides();
            _('VoiceOutput').value=_('VoiceOutputchoose').value;
            setLanguageOutput(_('VoiceOutput').value);
            _('synthesison').checked=true;
            _('arrowlanguage').style.visibility='hidden';
            _('arrowlanguage').style.display='none';
            _('arrowsynthesis').style.visibility='visible';
            _('arrowsynthesis').style.display='none';
            _('lilanguage').style.animation='none';
            _('lisynthese').style.animation='none'; 
            firstclick=true; 
            showMessageMenu();       
        }
    });
    //    form = dialog.find( "form" ).on( "submit", function( event ) {
    //      event.preventDefault();
    //      EnvoiMail();
    //    });
    function refreshSpeed() {
        builtin_discoursrate = $("#slider_speed").slider("value") / 100;
    }
    $("#slider_speed").slider({
        orientation: "horizontal",
        range: "min",
        max: 200,
        value: 100,
        slide: refreshSpeed,
        change: refreshSpeed
    });
    $("#slider_speed").slider("value", 100);

    var handle = $( "#custom-handle" );
    $( "#slider_speed" ).slider({
        create: function() {
          handle.text( $( this ).slider( "value" ) );
        },
        slide: function( event, ui ) {
          handle.text( ui.value );
        }
      });
});