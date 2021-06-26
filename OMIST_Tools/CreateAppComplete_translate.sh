#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
Translated=$5

echo "Converting file " $Filename

mkdir tmp/$RANDOMPATH
#rm -rf tmp/*
cp "$Filename" tmp/$RANDOMPATH/original_file.pptx
$PYTHON ./TranslatePPTX.py --input=tmp/$RANDOMPATH/original_file.pptx --output=tmp/$RANDOMPATH/input.pptx --from="$From" --to="$Translated"
mkdir tmp/$RANDOMPATH/images
export HOME=/tmp && $SOFFICE --headless --convert-to pdf tmp/$RANDOMPATH/input.pptx --outdir tmp/$RANDOMPATH
$CAIRO -jpeg tmp/$RANDOMPATH/input.pdf tmp/$RANDOMPATH/images/images
$PYTHON ./CreateMultiLingualAnim.py --input=tmp/$RANDOMPATH/input.pptx --ImagesDir="tmp/$RANDOMPATH/images" --output=tmp/output_$RANDOMPATH.html --from="$From" --to="$To" $6 $7 $8
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"
rm -rf tmp/$RANDOMPATH

