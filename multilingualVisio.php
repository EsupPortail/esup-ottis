<?php
// Empêche la mise en cache
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');

?>
<?php
$MicrosoftKey='bae361b8f5ef4337bebc416ab9d44cf8';
$MicrosoftRegion='francecentral';
?>
<script>
var language1="fr-FR";
var language2="en-US";

</script>
    <script src="https://aka.ms/csspeech/jsbrowserpackageraw"></script>
    <script>
        var SpeechSDK;
        var recognizerFR;
        var recognizerEN;
        var recognizer;
        var soundContext;


        var AudioContext = window.AudioContext // our preferred impl
                || window.webkitAudioContext       // fallback, mostly when on Safari
                || false;


        soundContext = new AudioContext();
        try {
            AudioContext = window.AudioContext // our preferred impl
                || window.webkitAudioContext       // fallback, mostly when on Safari
                || false;                          // could not find.

            if (AudioContext) {
                soundContext = new AudioContext();
            } else {
                alert("Audio context not supported");
            }
        } catch (e) {
            window.console.log("no sound context found, no audio output. " + e);
        }


        function Initialize(onComplete) {
            if (!!window.SpeechSDK) {
                onComplete(window.SpeechSDK);
            }
        }

        Initialize(async function (speechSdk) {
                SpeechSDK = speechSdk;
            });


        function onRecognizingFR(sender, recognitionEventArgs) {
            var result = recognitionEventArgs.result;
            var text = result.text;
            EcrirePartialEN(text);
        }

        function onRecognizedFR(sender, recognitionEventArgs) {
            var result = recognitionEventArgs.result;
            var text = result.text;
            // var text = RemoveVerbalTics(result.text);
            EcrirePartialFR(text);
            TraductionFR(text);
        }

        function onRecognizingEN(sender, recognitionEventArgs) {
            var result = recognitionEventArgs.result;
            var text = result.text;
            EcrirePartialEN(text);
        }

var flag=10;

        function onRecognizedEN(sender, recognitionEventArgs) {
            var result = recognitionEventArgs.result;
            if (flag>0) {console.log(result); flag--;}
            var text = result.text;
            // var text = RemoveVerbalTics(result.text);
            EcrirePartialEN(text);
            TraductionEN(text);
        }


        function doContinuousRecognition(key,region,language,recognizing,recognized,rec) {
            var audioConfig = SpeechSDK.AudioConfig.fromDefaultMicrophoneInput();
            var speechConfig = SpeechSDK.SpeechConfig.fromSubscription(key, region);
            speechConfig.speechRecognitionLanguage = language;
            speechConfig.setProfanity(SpeechSDK.ProfanityOption.Raw);
            recognizer = new SpeechSDK.SpeechRecognizer(speechConfig, audioConfig);
            //var phrases = document.getElementById('BoostedWords').value.replaceAll("\n",";");
            //if (phrases.length>0) {
            //  var phraseListGrammar = SpeechSDK.PhraseListGrammar.fromRecognizer(recognizer);
            //  phraseListGrammar.addPhrases(phrases.split(";"));
            //}
            recognizer.recognizing = recognizing;
            recognizer.recognized = recognized;
            recognizer.startContinuousRecognitionAsync();
        }

        function stopRecognizer() {
            recognizer.stopContinuousRecognitionAsync(
                            function () {
                                recognizer.close();
                                recognizer = undefined;
                            },
                            function (err) {
                                recognizer.close();
                                recognizer = undefined;
                            }
                        );
        }



        <?php

        echo "var MicrosoftKey='".$MicrosoftKey."';\n";
        echo "var MicrosoftRegion='".$MicrosoftRegion."';\n";
        ?>
    </script>

    <script>

function Init() {

        


    

    doContinuousRecognition(MicrosoftKey,MicrosoftRegion,'fr_FR',onRecognizingFR,onRecognizedFR,recognizer);
    //doContinuousRecognition(MicrosoftKey,MicrosoftRegion,'en-US',onRecognizingEN,onRecognizedEN,recognizerEN);
}

function EcrirePartialFR(ch) {
    document.getElementById('partialfr').innexHTML="<i>ok "+ch+"</i>";
}

function EcrirePartialEN(ch) {
    document.getElementById('partialen').innexHTML="<i>ok "+ch+"</i>";
}

function TraductionFR(ch) {
    document.getElementById('completefr').innexHTML+="<br/>"+ch;

}

function TraductionEN(ch) {
    document.getElementById('completeen').innexHTML+="<br/>"+ch;

}

    </script>
<html>
<head>
<title>Test</title>
</head>
<body>
<table>
<tr>
<th>French</th><th>English</th>
</tr>
<tr>
<td><div id="partialfr"></div></td>
<td><div id="partialen"></div></td>
</tr>
<tr>
<td><div id="completefr"></div></td>
<td><div id="completeen"></div></td>
</tr>
</table>
<button onclick="Init();">Launch</button>
</body>
</html>
