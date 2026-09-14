import Subconfig as config
import os
import json

stream = os.popen("curl --proxy http://cache.univ-nantes.fr:3128 --silent --location --request GET 'https://"+config.MICROSOFT_REGION+".tts.speech.microsoft.com/cognitiveservices/voices/list' --header 'Ocp-Apim-Subscription-Key: '"+config.MICROSOFT_KEY+"")
AllVoices = json.loads(stream.read())

for v in AllVoices:
    print(v["Locale"]+"|"+v["ShortName"]+"_"+v["Gender"]+"_"+v["LocaleName"]+"|Test")