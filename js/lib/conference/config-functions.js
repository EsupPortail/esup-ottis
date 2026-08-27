import { NBconnectedusers, Tconnectedusers } from './state.js';

import { _ } from './dom-helpers.js';
import { addCsrfToken, createXhrRequest } from './network.js';

import { ROOM_TOKEN } from './compat.js';
import { zoomid, uncloudlink, uncloudhtmllink, targettab, inSlideTab, setLanguageInput } from './config.js';
import { setLanguageSubtitle, PopulateSubtitlesLang, PopulateSpeechLang } from './voices.js';
import { LaunchRemote } from './remote.js';
import { startChatPolling, saveTextQ } from '../chat/main.js';
import { Forward, Backward, SlideForward, SlideBackward, SkipForward, SkipBackward } from './navigation.js';
import { Micro, showET, toggleTextBandDisplay } from './ui.js';
import { SlideNotesUncloud, SlideNotesUncloudHtml } from './pdf-viewer.js';



/** @module conference/config */



export function Lancer() {

    LaunchRemote();
    startChatPolling();
    //LaunchStudentReadText();
    //LaunchFeedbackReady();
    //applyConfig();
}

export function Init() {
    PopulateSubtitlesLang();
    PopulateSpeechLang();

    /*  _('question').addEventListener('keyup', function (event) {
        if (event.code === 'Enter') {
            saveTextQ(_('question').value);
          event.preventDefault();
          event.stopPropagation();
        }
      },false);
    */
    //resizeSlides();

    document.onkeyup = function(event) {
        // Gestion des flèches pour la présentation (toujours active)
        if (event.code === 'ArrowDown') {
            Forward();
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        if (event.code === 'ArrowUp') {
            Backward();
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        if (event.code === 'ArrowRight') {
            SlideForward();
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        if (event.code === 'ArrowLeft') {
            SlideBackward();
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        if (event.code === 'PageDown') {
            SlideForward();
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        if (event.code === 'PageUp') {
            SlideBackward();
            event.preventDefault();
            event.stopPropagation();
            return;
        }
    };

    //setTimeout(Micro,1000);
    //setTimeout(Micro,1500);

    /*if (_('embedwooclapon').checked) {
        _('spanembedwooclapon').innerHTML = 'Yes !';
        show('titlewooclap');
    } else {
        _('spanembedwooclapon').innerHTML = 'No.';
        hide('titlewooclap');
    }*/
    if (zoomid != '') startZoom(zoomid);
    if (uncloudlink != '') SlideNotesUncloud(uncloudlink);
    if (uncloudhtmllink != '') SlideNotesUncloudHtml(uncloudhtmllink);
    if (targettab > 0) _('ttab-' + targettab).click();

    // Adjust a few parameters according to the url arguments
    let base = new URL(window.location.toLocaleString()).searchParams;
    if (base.has('WhichTranslator')) _('whichtranslator').value = base.get('WhichTranslator');


}

export function opentab_url(url) {
    let tabnumber = 1;
    while (tabnumber < 4 && _('extraurl' + tabnumber).value != '') tabnumber++;
    _('extraurl' + tabnumber).value = url;
    _('embedET' + tabnumber).checked = true;
    showET(_('embedET' + tabnumber), tabnumber);
}

export function startZoom(zoomid) {
    let lnk = 'https://univ-nantes-fr.zoom.us/wc/join/' + zoomid;
    opentab_url(lnk);
    if (window.confirm('Are you the host for this zoom session ?')) {
        window.open('https://univ-nantes-fr.zoom.us/wc/' + zoomid + '/start');
    }
}

export function setLanguageStr(l) {

    // Try to find the language in the language list
    let s = _('classroomlanguage');
    let finded = -1;
    for (let i = 0; i < s.options.length && finded == -1; i++) {
        if (l.substr(0, 2).toLowerCase() == s.options[i].innerHTML.substr(0, 2).toLowerCase()) finded = i;
    }
    if (finded != -1) {
        s.selectedIndex = finded;
        setLanguageInput(_('classroomlanguage').value);
        Micro();
    }
}

export function remote() {
    if (_('classroomid') && _('classroomid').value != '') {
        // Check if ROOM_TOKEN exists and is not empty
        if (typeof ROOM_TOKEN === 'undefined' || ROOM_TOKEN === '') {

            return;
        }

        let f = new FormData();
        f.append('ROOMID', _('classroomid').value);
        f.append('APPID', (_('appid') ? _('appid').value : ''));
        f.append('room_token', ROOM_TOKEN);
        f.append('ORDER', '');
        addCsrfToken(f);

        let request = createXhrRequest('POST', 'saveremoteorder.php', {
            onSuccess: function(xhr) {
                let order = xhr.responseText;
                if (order != '') {
                    if (order == 'forward') Forward();
                    if (order == 'backward') Backward();
                    if (order == 'skipforward') SkipForward();
                    if (order == 'skipbackward') SkipBackward();
                    if (order == 'playcurrent') {
                        SkipBackward();
                        Forward();
                    }
                    if (order.substr(0, 'micro'.length) == 'micro') {
                        let fromremote = order.substr('micro'.length).trim();
                        if (fromremote == (_('appid') ? _('appid').value : '') || fromremote == '') Micro();
                    }
                    if (order.substr(0, 'language'.length) == 'language') {
                        let options = order.substr('language'.length).trim().split('|');
                        let to_lang = options[0].trim();
                        let fromremote = options[1].trim();

                        if (fromremote == (_('appid') ? _('appid').value : '') || fromremote == '') setLanguageStr(to_lang);
                    }
                }
            },
            data: f,
            timeout: 10000,
            context: 'remote'
        });
        request.onerror = function(event) {

        };
        request.send(f);
    }
}

export function updateUsers() {
    let connectedEl = _('connectedusers');
    if (connectedEl) {
        connectedEl.textContent = (NBconnectedusers) + ' connected users';
        if (Tconnectedusers && Array.isArray(Tconnectedusers)) {
            connectedEl.title = Tconnectedusers.join(', ');
        } else {
            connectedEl.title = '';
        }
    }
}

export function updateConfig() {
    let newConfig = [];
    newConfig[0] = String.fromCharCode(65 + _('classroomlanguage').selectedIndex);
    newConfig[1] = String.fromCharCode(65 + _('subtitlelanguage').selectedIndex);
    currentconfig = newConfig.join('');
    // Configuration simplifiée : seuls classroomlanguage et subtitlelanguage sont conservés
}

export function applyConfig() {

    let config = window.pageData?.currentconfig;
    if (!config || config.length < 2) return; // Seuls les 2 premiers caractères (langues) sont utilisés
    
    // classroomlanguage
    let classroomLangEl = _('classroomlanguage');
    if (classroomLangEl) {
        classroomLangEl.selectedIndex = 1 * (config.charCodeAt(0) - 65);
    }
    //setLanguageInput(_('classroomlanguage').value);
    
    // subtitlelanguage
    let subtitleLangEl = _('subtitlelanguage');
    if (subtitleLangEl) {
        subtitleLangEl.selectedIndex = 1 * (config.charCodeAt(1) - 65);
        setLanguageSubtitle(subtitleLangEl.value);
    }
    // Les autres paramètres de config ne sont plus utilisés (éléments supprimés)
    
    // Initialiser l'affichage de textBand
    if (_('usetextband')) {
        toggleTextBandDisplay();
    }
}

export function sendclick() {
    let tosend = get('question').value.replaceAll('\n', '').trim();
    //alert("Will send : ("+tosend+")");
    if (tosend != '') saveTextQ(tosend);
    get('question').value = '';
}