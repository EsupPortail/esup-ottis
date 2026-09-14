import os
import Subconfig as config
import argparse

parser = argparse.ArgumentParser(add_help=False)

args, remaining = parser.parse_known_args()

parser = argparse.ArgumentParser(
    description=__doc__,
    formatter_class=argparse.RawDescriptionHelpFormatter,
    parents=[parser])

parser.add_argument(
    '-V', '--voice', type=str,
    help='output voice shortname')
parser.add_argument(
    '-o', '--output', type=str,
    help='output filename')
parser.add_argument(
    '-t', '--text', type=str,
    help='text to read')


args = parser.parse_args(remaining)

outputvoice="en-US-JennyNeural"
if args.voice:
    outputvoice=args.voice

filenameout="output.mp3"
if args.output:
    filenameout=args.output

text_to_read="Test"
if args.text:
    text_to_read=args.text

text = text_to_read

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
text=text.replace("\"","'")
ssml=ssml.replace("@@TEXT@@",text)

ssml=ssml.replace("\n","")
ssml=ssml.replace("\"","'")

# os.system("curl --proxy http://cache.univ-nantes.fr:3128 --output test.mp3 --silent --location -d \""+ssml+"\" --header 'Content-Type: application/ssml+xml' --request POST 'https://"+config.MICROSOFT_REGION+".tts.speech.microsoft.com/cognitiveservices/v1' --header 'Ocp-Apim-Subscription-Key: '"+config.MICROSOFT_KEY+" --header 'X-Microsoft-OutputFormat: audio-24khz-48kbitrate-mono-mp3'")

os.system("curl --proxy http://cache.univ-nantes.fr:3128 --output \""+filenameout+"\" --silent --location -d \""+ssml+"\" --header 'Content-Type: application/ssml+xml' --request POST 'https://"+config.MICROSOFT_REGION+".tts.speech.microsoft.com/cognitiveservices/v1' --header 'Ocp-Apim-Subscription-Key: '"+config.MICROSOFT_KEY+" --header 'X-Microsoft-OutputFormat: audio-24khz-48kbitrate-mono-mp3'")
#print("curl --proxy http://cache.univ-nantes.fr:3128 --output \""+filenameout+"\" --silent --location -d \""+ssml+"\" --header 'Content-Type: application/ssml+xml' --request POST 'https://"+config.MICROSOFT_REGION+".tts.speech.microsoft.com/cognitiveservices/v1' --header 'Ocp-Apim-Subscription-Key: '"+config.MICROSOFT_KEY+" --header 'X-Microsoft-OutputFormat: audio-24khz-48kbitrate-mono-mp3'")
