#!/bin/bash

source config.py
Filename=$1
From=$2
To=$3

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
echo "Options From=$From and To=$To" $4 $6 $7

rm -rf tmp/*
$FINDER tmp
cp "$Filename" tmp/input.pptx
mkdir tmp/images
$SOFFICE --headless --convert-to pdf tmp/input.pptx --outdir tmp  2>/dev/null
pdftocairo -jpeg tmp/input.pdf tmp/images/images  2>/dev/null
./CreateMultiLingualAnim.py --input=tmp/input.pptx --ImagesDir="tmp/images" --output=tmp/output.html --from="$From" --to="$To" $4 $5 $6  2>/dev/null
