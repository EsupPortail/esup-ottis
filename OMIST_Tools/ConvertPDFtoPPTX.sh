#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
FilenameSpeech=$3

echo "Converting file " $Filename

mkdir tmp/$RANDOMPATH
#rm -rf tmp/*

cp "$Filename" tmp/$RANDOMPATH/input.pdf
cp "$FilenameSpeech" tmp/$RANDOMPATH/speech.txt
mkdir tmp/$RANDOMPATH/images
$CAIRO -jpeg tmp/$RANDOMPATH/input.pdf tmp/$RANDOMPATH/images/images  2>/dev/null
$PYTHON ./PDF_TXT_to_PPTX.py --input=tmp/$RANDOMPATH/speech.txt --ImagesDir=tmp/$RANDOMPATH/images --output=tmp/$RANDOMPATH/converted $4  

cd tmp/$RANDOMPATH
zip -9 ../output_$RANDOMPATH.zip converted.*
