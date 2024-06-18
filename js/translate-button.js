/**
 * Add an event listener on the translation button (to show the real translation button);
 */

document.addEventListener("DOMContentLoaded", (event) => {
    const elt = document.getElementById("translateButton");
    if (elt) {
        elt.addEventListener("click", (event) => {
            event.target.style.display = "none";
            var glink=document.createElement('script');
            glink.src='https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
            document.body.appendChild(glink);
        }, false);
    }
});