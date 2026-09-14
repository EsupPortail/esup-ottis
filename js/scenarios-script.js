window.addEventListener('DOMContentLoaded', () => {
  document.body.addEventListener('load', () => {
    document.cookie = "";
  })
});

function _(e) {return document.getElementById(e);}

/**
* @summary Apply a configuration (at startup)
* 
*/

function applyConfig() {
  var config = isExternal;
  if (config==='yes'){
      _('toggle-external').checked = true;
      // urlIndex = "/indexNOCAS.php";
  }
}

/**
* @summary Updates the URL if the user is external or not
* 
*/
function updateExternal(){
  if (document.getElementById('toggle-external').checked) {
      urlIndex = "/indexNOCAS.php";
      }else{
      urlIndex = "/index.php";
  }
  return urlIndex;
}

/**
* ---EN---
* @summary Set up all redirections (per scenario).
* ---FR---
* @summary Configuration des redirections (par scénario).
*/
function setupRedirections() {
document.querySelector('[id="scenario-1-button"]').addEventListener('click', () => {
  window.open(root + updateExternal() + "?myconfig=AANAYKCYNCNNNYNYNN");
});

document.querySelector('[id="scenario-2-button"]').addEventListener('click', () => {
    window.open(root + updateExternal() + "?myconfig=ABNAYKCNYCNNNYNYYN");
});

document.querySelector('[id="scenario-3-button"]').addEventListener('click', () => {
    let inputValue = document.querySelector('[id="scenario-3-input"]').value;

    window.open(root + updateExternal() + "?myconfig=ABNAYKCNYCNNNYYYYN&tab=4&link=" + (inputValue));
});

document.querySelector('[id="scenario-4-button"]').addEventListener('click', () => {
    let inputValue = document.querySelector('[id="scenario-4-input"]').value;

    window.open(root + updateExternal() + "?myconfig=ABNAYKCNYCNNNYYYYN&tab=4&htmllink=" + (inputValue));
});

document.querySelector('[id="scenario-5-button"]').addEventListener('click', () => {
    // window.open(root + "/index.php?" + CreateLink());
    GenereLinkAlert();
});

document.querySelector('[id="scenario-6-button"]').addEventListener('click', () => {
    window.open(root + updateExternal() + "?myconfig=ABYANKCNYCNNNYNYYN");
});

document.querySelector('[id="direct-access-teacher"]').addEventListener('click', () => {
    window.open(root + updateExternal());
});

document.querySelector('[id="direct-access-tools"]').addEventListener('click', () => {
    window.open(root + "/OMIST_Tools" + updateExternal());
});

document.querySelector('[id="direct-access-conv"]').addEventListener('click', () => {
    window.open(root + "/Translator2.html");
});
}

/**
* @summary Create the link for the teacher application, customized according to parameters.
* 
*/
function CreateLink() {
  res="";
  if (_('persroomid').value != "") res+="&ROOMID="+_('persroomid').value;
  if (_('persmaxlines').value != "") res+="&MaxSavedLines="+_('persmaxlines').value;
  if (_('persolink').value != "") res+="&link="+_('persolink').value;
  if (_('persohtmllink').value != "") res+="&htmllink="+_('persohtmllink').value;
  if (_('persoconfig').value != "") res+="&myconfig="+_('persoconfig').value;
  if (_('persotab').value != "" && _('persotab').value != "0") res+="&tab="+_('persotab').value;
  if (_('persomicrosoftkey').value != "") res+="&MicrosoftKey="+_('persomicrosoftkey').value;
  if (_('persomicrosoftregion').value != "") res+="&MicrosoftRegion="+_('persomicrosoftregion').value;
  if (_('persotheme').value != "" && _('persotheme').value != "0") res+="&theme="+_('persotheme').value;
  return res;
}

/**
* @summary Create the link for the student application, linked to a pre-configured class.
* 
*/
function CreateLinkStudent() {
  res="";
  if (_('persroomid').value != "") res+="&ROOMID="+_('persroomid').value;
  return res;
}

/**
* @summary Generates text with custom links.
* 
*/
function GenereLinkAlert() {
  var linkteacher = root + updateExternal() + "?" + CreateLink(); 
  linkteacher="<a href=\""+linkteacher+"\" target='_blank'>"+linkteacher+"</a>"; 
  var linkstudent=CreateLinkStudent(); 
  if (linkstudent != '') {
      linkstudent=root+'/Student.php?'+linkstudent; 
      linkstudent="<a href=\""+linkstudent+"\" target='_blank'>"+linkstudent+"</a>"; 
      linkstudent='Et voici le lien étudiant : \n '+linkstudent;
  }
  openModal('generate_link', linkteacher, linkstudent); 
//   myalert('Voici le lien de connexion, à utiliser n\'importe quand\n'+linkteacher+linkstudent,'Lien de connexion');
}
var root = window.location.href.replace("/scenarios.php","").replace("?external", "");

/**
* @summary A prettier alert function
* 
* @param {string} ch The text shown in the alert.
* @param {string} title The title of the alert box (default to "Message").
*/

function myalert(ch,title="Message") {
  ch = ch.replaceAll("\n", "<br>");
  $("#dialog-invite").dialog( "option", "title", title );
  _('dialog-invite-p').innerHTML = ch;
  // $("#dialog-invite").dialog("option", "title", title).dialog("open");
  $("#dialog-invite").dialog("open");
}