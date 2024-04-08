#!/bin/bash

Filename=$1
FilenameSpeech=$2
From=$3
To=$4

if [ $Filename = "" ]; then
        echo "Filename is missing"
        exit
fi

if [ $From = "" ]; then
        From="fr-FR"
fi

if [ $To = "" ]; then
        To="EN,DE"
fi

echo "Converting file " $Filename
echo "Options From=$From and To=$To" $4 $5 $6

rm -rf tmp/*
cp "$Filename" tmp/input.pdf
cp "$FilenameSpeech" tmp/speech.txt
mkdir tmp/images
pdftocairo -jpeg tmp/input.pdf tmp/images/images
./CreateMultiLingualAnim.py --input=tmp/speech.txt --ImagesDir="tmp/images" --output=tmp/output.html --from="$From" --to="$To" $5 $6 $7
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"
