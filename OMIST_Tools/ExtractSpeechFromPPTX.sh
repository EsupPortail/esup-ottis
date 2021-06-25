#!/bin/bash

source config.py

Filename=$1

echo "Converting file " $Filename

rm -rf tmp/*
cp "$Filename" tmp/input.pptx
$PYTHON ./SpeechExtractionFromPPTX.py --input=tmp/input.pptx --output=tmp/speech.txt  2>/dev/null

