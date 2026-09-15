/**
 * Mail functions for auditor
 */

import { _ } from '../dom/selector.js';

/**
 * @summary Generic function for sending an email
 */
function EnvoiMail() {
  // alert("I will send the mail to "+_('dialog_MailTo').value.replaceAll(' - ',','));
  const FD = new FormData();
  FD.append('from', _('dialog_MailTo').value);
  FD.append('to', _('dialog_MailTo').value.replaceAll(' - ', ','));
  FD.append('subject', _('dialog_MailSubject').value);
  // FD.append('body', _('dialog_MailBody').innerHTML);
  FD.append('body', window.DOMPurify.sanitize(_('dialog_MailBody').innerHTML));
  // FD.append('body', quillmail.getText());

  // Ajouter le token CSRF pour la protection
  addCsrfToken(FD);

  const request = createFetchRequest('POST', '/email/send', {
    onSuccess(response) {
      alert('Mail sent.');
    },
    onError(response, context) {
      // Ignorer les timeouts de polling (comportement normal)
      if (response.status !== 0 || response.statusText !== 'Timeout') {
        alert('Error, the mail cannot be sent.');
      }
    },
    data: FD,
    timeout: 10000,
    context: 'EnvoiMail',
  });
  request.send();
  // dialog.dialog( "close" );
}

/**
 * @summary Send the notebook content by mail
 * @param {string} content - Content to send
 */
function sendnotebook(content) {
  _('dialog_MailFrom').value = 'bourdon-j@univ-nantes.fr';
  _('dialog_MailTo').value = 'your.mail@univ-nantes.fr';
  _('dialog_MailSubject').value = `Content of lesson ${_('classroomid').value}`;
  _('dialog_MailBody').innerHTML = window.DOMPurify.sanitize(content);
  // $( "#dialog-mail" ).dialog("open");
  _('dialog_MailTo').value = prompt('Enter your email', 'your.mail@univ-nantes.fr');
  EnvoiMail();
}

export { EnvoiMail, sendnotebook };
