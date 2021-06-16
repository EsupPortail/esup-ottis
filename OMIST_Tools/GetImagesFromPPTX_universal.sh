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
cp "$Filename" tmp/test.pptx
mkdir tmp/images
$SOFFICE --headless --convert-to pdf tmp/test.pptx --outdir tmp  2>/dev/null
pdftocairo -jpeg tmp/test.pdf tmp/images/images  2>/dev/null
./CreateMultiLingualAnim.py --input=tmp/test.pptx --ImagesDir="tmp/images" --output=tmp/out.html --from="$From" --to="$To" $4 $5 $6  2>/dev/null
