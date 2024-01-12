import os
import Subconfig as config
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
speech_config.set_proxy("http://cache.univ-nantes.fr",3128)
speech_config.speech_synthesis_voice_name=outputvoice

speech_synthesizer = speechsdk.SpeechSynthesizer(speech_config=speech_config, audio_config=audio_config)

text = text_to_read

speech_synthesis_result = speech_synthesizer.speak_text_async(text).get()
