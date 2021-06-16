#!/bin/bash

source config.py

Filename=$1
From=$2
To=$3
Translated=$4

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

if [ $Translated = "" ]; then
        Translated="EN"
fi


echo "Converting file " $Filename
echo "Options From=$From and To=$To with slides translated in Translated=$Translated" $5 $6 $7

rm -rf tmp/*
$FINDER tmp
cp "$Filename" tmp/original_file.pptx
./TranslatePPTX.py --input=tmp/original_file.pptx --output=tmp/test.pptx --from="$From" --to="$Translated"  2>/dev/null
mkdir tmp/images
$SOFFICE --headless --convert-to pdf tmp/test.pptx --outdir tmp  2>/dev/null
pdftocairo -jpeg tmp/test.pdf tmp/images/images  2>/dev/null
./CreateMultiLingualAnim.py --input=tmp/test.pptx --ImagesDir="tmp/images" --output=tmp/out.html --from="$From" --to="$To" $5 $6 $7  2>/dev/null
