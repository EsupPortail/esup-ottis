let gt;
function googleTranslateElementInit() {
    document.cookie='';
    gt=new google.translate.TranslateElement({
        pageLanguage: 'auto', layout: google.translate.TranslateElement.InlineLayout
    }, 'google_translate_element');
    setTimeout(Init2,1000);
    document.body.style = "";
}

function Init2() {
    let a = document.querySelector("#google_translate_element select");
    if(a){
        let find=0;
        for (let i=0; i<a.options.length; i++) {
            if (a.options[i].value==document.body.lang) find=i;
        }
        a.selectedIndex=find;
        a.dispatchEvent(new Event('change'));
    }
    a=document.querySelector("#google_translate_element > div");
    a.childNodes[1].deleteData(0,100);
    for (let i=2; i<a.childNodes.length; i++) a.childNodes[i].innerHTML='';
    document.querySelector("#translateButton").style = "background-color : var(--black); border : none;";
}