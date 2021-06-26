#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
Speech=$3
From=$4
To=$5

echo "Converting file " $Filename

mkdir tmp/$RANDOMPATH
#rm -rf tmp/*
cp "$Filename" tmp/$RANDOMPATH/input.pdf
cp "$Speech" tmp/$RANDOMPATH/speech.txt
mkdir tmp/$RANDOMPATH/images
$CAIRO -jpeg tmp/$RANDOMPATH/input.pdf tmp/$RANDOMPATH/images/images
$PYTHON ./CreateMultiLingualAnim.py --input=tmp/$RANDOMPATH/speech.txt --ImagesDir="tmp/$RANDOMPATH/images" --output=tmp/output_$RANDOMPATH.html --from="$From" --to="$To" $6 $7 $8
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"
rm -rf tmp/$RANDOMPATH



