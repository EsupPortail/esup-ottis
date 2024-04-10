// All global variables definitions

// Student feeback aka Translated subtitle bar
var Studentinputnumber = -1;

var StudentToTranslate = [];
var StudentToTranslate2 = [];
var Studentcompletespeechactive = false;
var StudentToSay = [];
var Studentcurrentdiscours = -1;
var Studentgotolive = false;
var Studentcurrentorder_readtext = 0;
var Studentmaxorder_readtext = -1;
var StudentAllPhrases = [];
var lastSentenceVO = '';
var lastSentenceTranslated = '';
var LiveTranslationFreq = 10;
var LiveTranslationNum = 0;
var totalTranslated = 0;

// Parameters and URL parsing
var base = new URL(window.location.toLocaleString()).searchParams;
var MaxSavedLines = 10;
var LoadPPTX = true;
var zoomid = '';
var uncloudlink = '';
var uncloudhtmllink = '';
var targettab = 0;
var window_location_href = window.location.href;

if (base.has('MaxSavedLines')) MaxSavedLines = parseInt(base.get('MaxSavedLines'), 10);
if (base.has('LoadPPTX')) LoadPPTX = true;
if (base.has('ZOOMID')) zoomid = base.get('ZOOMID');
if (base.has('link')) uncloudlink = base.get('link');
if (base.has('htmllink')) uncloudhtmllink = base.get('htmllink');
if (base.has('tab')) targettab = base.get('tab');
if (base.has('myconfig')) window_location_href = window_location_href.replace('myconfig=' + base.get('myconfig') + '&', '').replace('myconfig=' + base.get('myconfig'), '');

var visioactive = false;
var AllOutputVoices = [];
var TLangInput = [];
var builtin_voice;
var builtin_language;
var subtitle_language;
var speech_language = 'same';

var inputnumber = 0;
var previousimage = -1;

var readytotalk = true;

var currentPresentation = [];
var currentDiscours = -1;
var inSlideTab = false;

function fakeSpeechRecognition() {
    this.lang="";
    this.onresult = function(e) {};
    this.onend=function(e){};
    this.start=function() {console.log('Starting fake SpeechRecognition service')};
    this.stop=function() {console.log('Stoping fake SpeechRecognition service')};
    this.abort=function() {console.log('Aborting fake SpeechRecognition service')};
    this.continuous= true;
    this.interimResults=true;
    this.enableAutomaticPunctuation=true;
    this.maxAlternatives=1
}

var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || fakeSpeechRecognition;
var builtin_recognition = new SpeechRecognition();
builtin_recognition.continuous = false;
builtin_recognition.continuous = true;
builtin_recognition.interimResults = true;
builtin_recognition.enableAutomaticPunctuation = true;
builtin_recognition.maxAlternatives = 1;
var builtin_recognitionon = false;
var builtin_externaltranscritionon = false;
var builtin_microsofttranscritionon = false;

var delayReadtextQ = 100;

var ColorsForCR = [];
ColorsForCR["----"] = [
    [255, 0, 0],
    [255, 255, 255], 5
];
ColorsForCR["Teacher"] = [
    [0, 0, 255],
    [255, 255, 255], 5
];

var ScreenCR = [];

var inputnumberQ = 0;
var NBconnectedusers = 0;
var Tconnectedusers = [];

var timenew_question = 0;


var currentscroll = 0;
var currentindent = 0;
var scrollinterval = 50;

var delayRemote = 200;
var delayStudentReadtext = 500;

var delayFeedback = 50;
var FeedbackQueue = 0;
