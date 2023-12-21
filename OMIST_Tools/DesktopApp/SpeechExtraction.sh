#!/bin/bash

source config.py

Filename=$1

if [ $Filename = "" ]; then
        echo "Filename is missing"
        exit
fi

echo "Converting file " $Filename

rm -rf tmp/*
$FINDER tmp
cp "$Filename" tmp/test.pptx
./SpeechExtractionFromPPTX.py --input=tmp/test.pptx --output=tmp/speech.txt  2>/dev/null
