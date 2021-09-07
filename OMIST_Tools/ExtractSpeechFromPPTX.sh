#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2

echo "Converting file " $Filename
cd $INSTALLDIR
cd ..

mkdir tmp/$RANDOMPATH
#rm -rf tmp/*
cp "$Filename" tmp/$RANDOMPATH/input.pptx
export PYTHONIOENCODING=utf8
$PYTHON ./SpeechExtractionFromPPTX.py --input=tmp/$RANDOMPATH/input.pptx --output=tmp/speech_$RANDOMPATH.txt
#rm -rf tmp/$RANDOMPATH


