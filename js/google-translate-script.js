var gt;
function googleTranslateElementInit() {
    document.cookie='';
    gt=new google.translate.TranslateElement({
        pageLanguage: 'auto', layout: google.translate.TranslateElement.InlineLayout
    }, 'google_translate_element');
    setTimeout(Init2,1000);
    document.body.style = "";
    console.log(document.getElementById('google_translate_element').style);
}

function Init2() {
    var a = document.querySelector("#google_translate_element select");
    if(a){
        var find=0;
        for(var i=0; i<a.options.length; i++) {
            if (a.options[i].value==document.body.lang) find=i;
        }
        a.selectedIndex=find;
        a.dispatchEvent(new Event('change'));
    }
    a=document.querySelector("#google_translate_element > div");
    a.childNodes[1].deleteData(0,100);
    for(var i=2; i<a.childNodes.length; i++) a.childNodes[i].innerHTML='';
    document.querySelector("#translateButton").style = "background-color : var(--black); border : none;";
}