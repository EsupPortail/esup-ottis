#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
Speech=$3

echo "Converting file " $Filename

export PYTHONIOENCODING=utf8

mkdir tmp/$RANDOMPATH
#rm -rf tmp/*
cp "$Filename" tmp/$RANDOMPATH/input.pdf
cp "$Speech" tmp/$RANDOMPATH/speech.txt
mkdir tmp/$RANDOMPATH/images
$CAIRO -jpeg tmp/$RANDOMPATH/input.pdf tmp/$RANDOMPATH/images/images
$PYTHON ./CreateMultiLingualAnim.py --input=tmp/$RANDOMPATH/speech.txt --ImagesDir="tmp/$RANDOMPATH/images" --output=tmp/output_$RANDOMPATH.html --from="fr-FR" --to="fr-FR" $4 $5
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"
rm -rf tmp/$RANDOMPATH


