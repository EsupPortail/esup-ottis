import os
import config
import azure.cognitiveservices.speech as speechsdk
import argparse

parser = argparse.ArgumentParser(add_help=False)
parser.add_argument(
    '-S', '--speaker', action='store_true',
    help='Output to the default speaker')
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


speakeroutput=True
if args.speaker:
    speakeroutput=True

args = parser.parse_args(remaining)

outputvoice="en-US-JennyNeural"
if args.voice:
    outputvoice=args.voice

filenameout="output.mp3"
if args.output:
    speakeroutput=False
    filenameout=args.output

text_to_read="Test"
if args.text:
    text_to_read=args.text



speech_config = speechsdk.SpeechConfig(subscription=config.MICROSOFT_KEY, region=config.MICROSOFT_REGION)

if speakeroutput:
    audio_config = speechsdk.audio.AudioOutputConfig(use_default_speaker=True)
else:
    speech_config.set_speech_synthesis_output_format(speechsdk.SpeechSynthesisOutputFormat.Audio16Khz32KBitRateMonoMp3)
    audio_config=speechsdk.audio.AudioOutputConfig(filename=filenameout)

#speech_config.speech_synthesis_voice_name='sq-AL-AnilaNeural'
speech_config.speech_synthesis_voice_name=outputvoice

speech_synthesizer = speechsdk.SpeechSynthesizer(speech_config=speech_config, audio_config=audio_config)

text = text_to_read

speech_synthesis_result = speech_synthesizer.speak_text_async(text).get()


style = "angry"
role = "Boy"

"""

style="advertisement_upbeat" 	Expresses an excited and high-energy tone for promoting a product or service.
style="affectionate" 	Expresses a warm and affectionate tone, with higher pitch and vocal energy. The speaker is in a state of attracting the attention of the listener. The personality of the speaker is often endearing in nature.
style="angry" 	Expresses an angry and annoyed tone.
style="assistant" 	Expresses a warm and relaxed tone for digital assistants.
style="calm" 	Expresses a cool, collected, and composed attitude when speaking. Tone, pitch, and prosody are more uniform compared to other types of speech.
style="chat" 	Expresses a casual and relaxed tone.
style="cheerful" 	Expresses a positive and happy tone.
style="customerservice" 	Expresses a friendly and helpful tone for customer support.
style="depressed" 	Expresses a melancholic and despondent tone with lower pitch and energy.
style="disgruntled" 	Expresses a disdainful and complaining tone. Speech of this emotion displays displeasure and contempt.
style="documentary-narration" 	Narrates documentaries in a relaxed, interested, and informative style suitable for dubbing documentaries, expert commentary, and similar content.
style="embarrassed" 	Expresses an uncertain and hesitant tone when the speaker is feeling uncomfortable.
style="empathetic" 	Expresses a sense of caring and understanding.
style="envious" 	Expresses a tone of admiration when you desire something that someone else has.
style="excited" 	Expresses an upbeat and hopeful tone. It sounds like something great is happening and the speaker is really happy about that.
style="fearful" 	Expresses a scared and nervous tone, with higher pitch, higher vocal energy, and faster rate. The speaker is in a state of tension and unease.
style="friendly" 	Expresses a pleasant, inviting, and warm tone. It sounds sincere and caring.
style="gentle" 	Expresses a mild, polite, and pleasant tone, with lower pitch and vocal energy.
style="hopeful" 	Expresses a warm and yearning tone. It sounds like something good will happen to the speaker.
style="lyrical" 	Expresses emotions in a melodic and sentimental way.
style="narration-professional" 	Expresses a professional, objective tone for content reading.
style="narration-relaxed" 	Express a soothing and melodious tone for content reading.
style="newscast" 	Expresses a formal and professional tone for narrating news.
style="newscast-casual" 	Expresses a versatile and casual tone for general news delivery.
style="newscast-formal" 	Expresses a formal, confident, and authoritative tone for news delivery.
style="poetry-reading" 	Expresses an emotional and rhythmic tone while reading a poem.
style="sad" 	Expresses a sorrowful tone.
style="serious" 	Expresses a strict and commanding tone. Speaker often sounds stiffer and much less relaxed with firm cadence.
style="shouting" 	Speaks like from a far distant or outside and to make self be clearly heard
style="sports_commentary" 	Expresses a relaxed and interesting tone for broadcasting a sports event.
style="sports_commentary_excited" 	Expresses an intensive and energetic tone for broadcasting exciting moments in a sports event.
style="whispering" 	Speaks very softly and make a quiet and gentle sound
style="terrified" 	Expresses a very scared tone, with faster pace and a shakier voice. It sounds like the speaker is in an unsteady and frantic status.
style="unfriendly" 	Expresses a cold and indifferent tone.


role="Girl" 	The voice imitates a girl.
role="Boy" 	The voice imitates a boy.
role="YoungAdultFemale" 	The voice imitates a young adult female.
role="YoungAdultMale" 	The voice imitates a young adult male.
role="OlderAdultFemale" 	The voice imitates an older adult female.
role="OlderAdultMale" 	The voice imitates an older adult male.
role="SeniorFemale" 	The voice imitates a senior female.
role="SeniorMale" 	The voice imitates a senior male.
"""

ssml = """
<speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis"
       xmlns:mstts="https://www.w3.org/2001/mstts" xml:lang="@@LANG@@">
    <voice name="@@VOICE@@">
        <mstts:express-as role="@@ROLE@@" style="@@STYLE@@">
            @@TEXT@@
        </mstts:express-as>
    </voice>
</speak>
"""

T_outputvoice=outputvoice.split("-")
lang=T_outputvoice[0]+"-"+T_outputvoice[1]

ssml=ssml.replace("@@LANG@@",lang)
ssml=ssml.replace("@@VOICE@@",outputvoice)
ssml=ssml.replace("@@TEXT@@",text)
ssml=ssml.replace("@@ROLE@@",role)
ssml=ssml.replace("@@STYLE@@",style)

#speech_synthesis_result = speech_synthesizer.speak_ssml_async(ssml).get()
