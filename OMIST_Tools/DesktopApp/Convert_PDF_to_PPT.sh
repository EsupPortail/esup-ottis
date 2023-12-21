#!/bin/bash

Filename=$1
FilenameSpeech=$2

if [ $Filename = "" ]; then
        echo "Filename is missing"
        exit
fi

echo "Converting file " $Filename

rm -rf tmp/*
cp "$Filename" tmp/input.pdf
cp "$FilenameSpeech" tmp/speech.txt
mkdir tmp/images
pdftocairo -jpeg tmp/input.pdf tmp/images/images  2>/dev/null
./PDF_TXT_to_PPTX.py --input=tmp/speech.txt --ImagesDir="tmp/images" --output=tmp/converted $3  2>/dev/null
