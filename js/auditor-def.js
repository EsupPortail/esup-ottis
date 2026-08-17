// Global variables for the student app

// Extract variables from window.pageData (injected from PHP)
var supportedlanguages = window.pageData ? window.pageData.supportedlanguages : [];
var classroomid = window.pageData ? window.pageData.classroomid : '';
// Wooclap disabled - tabs commented out
// var wooclapkey = window.pageData ? window.pageData.wooclapkey : '';
// var wooclapaddress = window.pageData ? window.pageData.wooclapaddress : 'https://app.wooclap.com';
var theme = window.pageData ? window.pageData.theme : 'flat';
var randomname = window.pageData ? window.pageData.randomname : '';

var visioactive=false;
var AllOutputVoices=[];
var TLangInput=[];
var builtin_voice;
var builtin_discoursrate=1;
var builtin_language;
var maxvoices=0;

var inputnumber=-1;

var ToTranslate=[];
var ToTranslate2=[];
var completespeechactive=false;

var ToSay=[];
var currentdiscours=-1;
var gotolive=false;

var currentorder_readtext=0;
var maxorder_readtext=-1;
var AllPhrases=[];

// Pour synchroniser le notebook avec notebookVO de conference
var lastNotebookVOContent = '';

var currentrandom = 0;

var firsttime = true;
var firstclick = false;

var ColorsForCR=[];
ColorsForCR["----"]=[[255,0,0],[255,255,255],5];
ColorsForCR["Intervenant"]=[[0,0,255],[255,255,255],0];

var ScreenCR=[];

document.addEventListener('submit', event => {
    event.preventDefault();
    // actual logic, e.g. validate the form
    //alert('Form submission cancelled.');
  });
  
var inputnumberQ=0;


var Tvoices;

var atab = 'tabs-0';


