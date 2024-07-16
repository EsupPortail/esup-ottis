window.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('load', () => {
      document.cookie = "";
    })
  });
  
  function _(e) {return document.getElementById(e);}
  
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
    var linkteacher = root+'/index.php?'+CreateLink(); 
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
var root = window.location.href.replace("/scenarios.php","");
  
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