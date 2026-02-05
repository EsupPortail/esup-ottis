import os
import Subconfig as config
import argparse
import srt
from pydub import AudioSegment
from pydub.effects import speedup

parser = argparse.ArgumentParser(add_help=False)

args, remaining = parser.parse_known_args()

parser = argparse.ArgumentParser(
    description=__doc__,
    formatter_class=argparse.RawDescriptionHelpFormatter,
    parents=[parser])

parser.add_argument(
    '-V', '--voice', type=str,
    help='output voice name (microsoft voice name)')
parser.add_argument(
    '-o', '--output', type=str,
    help='output directory')
parser.add_argument(
    '-f', '--outputfile', type=str,
    help='output filename')
parser.add_argument(
    '-i', '--input', type=str,
    help='input SRT filename')
parser.add_argument(
    '-s', '--speed', type=float,
    help='speed (default to 1, -1 means adapted speed)')

args = parser.parse_args(remaining)

outputvoice="en-US-JennyNeural"
if args.voice:
    outputvoice=args.voice

diroutput="../tmp/sertomp3/"
if args.output:
    diroutput=args.output

filenamein="test.srt"
if args.input:
    filenamein=args.input

filenamefinal=diroutput+"myoutput.mp3"
if args.outputfile:
    filenamefinal=args.outputfile

speed=1
if args.speed:
    speed=args.speed

def ReadText(text="Test",outputvoice="fr-FR-HenriNeural",filenameout="output.mp3"):
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

def adjustTempo(filename, speed=1, duration=0, minspeed=0.7, maxspeed=1.5):
    audio=AudioSegment.from_mp3(filename)
    if speed == -1:
        speed=len(audio)/float(duration)
    if speed<minspeed:
        speed=minspeed
    if speed>maxspeed:
        speed=maxspeed
    os.system("sox "+filename+" "+filename.replace('.mp3','_adjusted.mp3')+" tempo "+str(speed))
    #return audio.speedup(speed,150)
    return AudioSegment.from_mp3(filename.replace('.mp3','_adjusted.mp3'))



with open(filenamein,"r",encoding="utf8") as fd:
    contentSRT="".join(fd.readlines())

srtfile=list(srt.parse(contentSRT))

total_duration=0

os.makedirs(diroutput, exist_ok=True)

i=0
AllFiles=dict()
for sub in srtfile:
    text=sub.content.strip()
    start=int(sub.start.total_seconds()*1000)
    duration=int((sub.end.total_seconds()-sub.start.total_seconds())*1000)
    
    ReadText(text,filenameout=diroutput+"output_"+str(i)+".mp3",outputvoice=outputvoice)
    

    AllFiles[i]=dict()
    AllFiles[i]['start']=start
    #AllFiles[i]['song']=AudioSegment.from_mp3(diroutput+"output_"+str(i)+".mp3")
    AllFiles[i]['song']=adjustTempo(diroutput+"output_"+str(i)+".mp3",speed=speed,duration=duration)
    #AllFiles[i]['song']=AudioSegment.from_mp3(diroutput+"output_"+str(i)+"_adjusted.mp3")
    AllFiles[i]['duration']=len(AllFiles[i]['song'])
    AllFiles[i]['end']=AllFiles[i]['start']+AllFiles[i]['duration']
    print(i,text,duration,AllFiles[i]['duration'])
    i+=1

# Recalage
for i in range(len(AllFiles)-1):
    current=AllFiles.get(i)
    suivant=AllFiles.get(i+1)
    if (current['end']>=suivant['start']):
        suivant['start']=current['end']+1
        suivant['end']=suivant['start']+suivant['duration']

# création du son
res=AudioSegment.empty()
current=0
for i in range(len(AllFiles)):
    res+=AudioSegment.silent(duration=AllFiles.get(i)['start']-current-1)
    res+=AllFiles.get(i)["song"]
    current=AllFiles.get(i)['end']
#res+=AudioSegment.silent(duration=len(original)-len(res))

# export
res=AudioSegment.from_mono_audiosegments(res,res)
res.export(filenamefinal, format="mp3")

