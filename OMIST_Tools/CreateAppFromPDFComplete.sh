#!/bin/bash

source config.py

Filename=$1
Speech=$2
From=$3
To=$4

echo "Converting file " $Filename

rm -rf tmp/*
cp "$Filename" tmp/input.pdf
cp "$Speech" tmp/speech.txt
mkdir tmp/images
$CAIRO -jpeg tmp/input.pdf tmp/images/images
$PYTHON ./CreateMultiLingualAnim.py --input=tmp/speech.txt --ImagesDir="tmp/images" --output=tmp/output.html --from="$From" --to="$To" $5 $6 $7
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"



