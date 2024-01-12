import argparse

import Subconfig as config
import os
import json

text="Bonjour le monde"
outputvoice="fr-FR-HenriNeural"

ssml = """
<speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis"
       xmlns:mstts="https://www.w3.org/2001/mstts" xml:lang="@@LANG@@">
    <voice name="@@VOICE@@">
            @@TEXT@@
    </voice>
</speak>
"""

T_outputvoice=outputvoice.split("-")
lang=T_outputvoice[0]+"-"+T_outputvoice[1]

ssml=ssml.replace("@@LANG@@",lang)
ssml=ssml.replace("@@VOICE@@",outputvoice)
ssml=ssml.replace("@@TEXT@@",text)

ssml=ssml.replace("\n","")

# os.system("curl --proxy http://cache.univ-nantes.fr:3128 --output test.mp3 --silent --location -d \""+ssml+"\" --header 'Content-Type: application/ssml+xml' --request POST 'https://"+config.MICROSOFT_REGION+".tts.speech.microsoft.com/cognitiveservices/v1' --header 'Ocp-Apim-Subscription-Key: '"+config.MICROSOFT_KEY+" --header 'X-Microsoft-OutputFormat: audio-24khz-48kbitrate-mono-mp3'")

os.system("curl --proxy http://cache.univ-nantes.fr:3128 --output test.mp3 --silent --location -d '"+ssml+"' --header 'Content-Type: application/ssml+xml' --request POST 'https://"+config.MICROSOFT_REGION+".tts.speech.microsoft.com/cognitiveservices/v1' --header 'Ocp-Apim-Subscription-Key: '"+config.MICROSOFT_KEY+" --header 'X-Microsoft-OutputFormat: audio-24khz-48kbitrate-mono-mp3'")


"""
stream = os.popen("curl --proxy http://cache.univ-nantes.fr:3128 --silent --location --request GET 'https://"+config.MICROSOFT_REGION+".tts.speech.microsoft.com/cognitiveservices/voices/list' --header 'Ocp-Apim-Subscription-Key: '"+config.MICROSOFT_KEY+"")
AllVoices = json.loads(stream.read())

#print(AllVoices)
for v in AllVoices:
    print(v["Locale"]+"|"+v["ShortName"]+"_"+v["Gender"]+"_"+v["LocaleName"]+"|Test")
"""