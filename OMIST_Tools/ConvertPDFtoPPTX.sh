#!/bin/bash

source config.py

Filename=$1
FilenameSpeech=$2

echo "Converting file " $Filename

rm -rf tmp/*
cp "$Filename" tmp/input.pdf
cp "$FilenameSpeech" tmp/speech.txt
mkdir tmp/images
$CAIRO -jpeg tmp/input.pdf tmp/images/images  2>/dev/null
$PYTHON ./PDF_TXT_to_PPTX.py --input=tmp/speech.txt --ImagesDir="tmp/images" --output=tmp/converted $3  2>/dev/null

cd tmp
zip -9 output.zip converted.*
