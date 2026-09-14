var subscriptionKey, serviceRegion;
var SpeechSDK;
var recognizer;
var stopRecognizer, onRecognizing, onRecognized, doContinuousRecognition;
var firttimemicrosoft = true;

function InitializeMicrosoft() {
    if (firttimemicrosoft) {
        firttimemicrosoft = false;

        if (!!window.SpeechSDK) {
            SpeechSDK = window.SpeechSDK;
        }



        onRecognizing = function onRecognizing(sender, recognitionEventArgs) {
            var result = recognitionEventArgs.result;
            if (document.getElementById("directMicrosoftTranslation").checked && builtin_language != subtitle_language) {
                var text = recognitionEventArgs.privResult.privTranslations.privMap.privValues[0];
            } else {
                var text = RemoveVerbalTics(result.text);
            }
            EcrirePartial(text);
        }

        onRecognized = function onRecognized(sender, recognitionEventArgs) {
            var result = recognitionEventArgs.result;
            if (document.getElementById("directMicrosoftTranslation").checked && builtin_language != subtitle_language) {
                var text = recognitionEventArgs.privResult.privTranslations.privMap.privValues[0];
                var textVO = RemoveVerbalTics(result.text);
            } else {
                var text = RemoveVerbalTics(result.text);
                var textVO = text;
            }
            EcrirePartial(text);
            Traduction(textVO);
        }


        doContinuousRecognition = function doContinuousRecognition(key, region, language) {
            var audioConfig = SpeechSDK.AudioConfig.fromDefaultMicrophoneInput();
            if (document.getElementById("directMicrosoftTranslation").checked && builtin_language != subtitle_language) {
                var speechConfig = SpeechSDK.SpeechTranslationConfig.fromSubscription(key, region);
                speechConfig.addTargetLanguage(subtitle_language);
                speechConfig.speechRecognitionLanguage = language;
                speechConfig.setProfanity(SpeechSDK.ProfanityOption.Raw);
                recognizer = new SpeechSDK.TranslationRecognizer(speechConfig, audioConfig);
            } else {
                var speechConfig = SpeechSDK.SpeechConfig.fromSubscription(key, region);
                speechConfig.speechRecognitionLanguage = language;
                speechConfig.setProfanity(SpeechSDK.ProfanityOption.Raw);
                recognizer = new SpeechSDK.SpeechRecognizer(speechConfig, audioConfig);
            }

            //          var autoDetectSourceLanguageConfig = SpeechSDK.AutoDetectSourceLanguageConfig.fromLanguages(["fr-FR","en-US", "de-DE"]);
            //          recognizer = new SpeechSDK.SpeechRecognizer(speechConfig, autoDetectSourceLanguageConfig, audioConfig);
            var phrases = document.getElementById('BoostedWords').value.replaceAll("\n", ";");
            if (phrases.length > 0) {
                var phraseListGrammar = SpeechSDK.PhraseListGrammar.fromRecognizer(recognizer);
                phraseListGrammar.addPhrases(phrases.split(";"));
            }
            recognizer.recognizing = onRecognizing;
            recognizer.recognized = onRecognized;
            recognizer.startContinuousRecognitionAsync();
        }

        stopRecognizer = function stopRecognizer() {
            recognizer.stopContinuousRecognitionAsync(
                function() {
                    recognizer.close();
                    recognizer = undefined;
                },
                function(err) {
                    recognizer.close();
                    recognizer = undefined;
                }
            );
        }
    }
}