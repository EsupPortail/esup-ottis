#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
FilenameSpeech=$3

echo "Converting file " $Filename

mkdir tmp/$RANDOMPATH
#rm -rf tmp/*

cp "$Filename" tmp/$RANDOMPATH/input.pptx
cp "$FilenameSpeech" tmp/$RANDOMPATH/speech.txt

export PYTHONIOENCODING=utf8

$PYTHON ./PPTX_TXT_to_PPTX.py --input=tmp/$RANDOMPATH/speech.txt --inputpptx=tmp/$RANDOMPATH/input.pptx --output=tmp/$RANDOMPATH/converted

cd tmp/$RANDOMPATH
zip -9 ../output_$RANDOMPATH.zip *.ppt*
cd ../..
rm -rf tmp/$RANDOMPATH

