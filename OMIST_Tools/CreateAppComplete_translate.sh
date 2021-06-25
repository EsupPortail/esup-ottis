#!/bin/bash

source config.py

Filename=$1
From=$2
To=$3
Translated=$4

echo "Converting file " $Filename

rm -rf tmp/*
cp "$Filename" tmp/original_file.pptx
$PYTHON ./TranslatePPTX.py --input=tmp/original_file.pptx --output=tmp/input.pptx --from="$From" --to="$Translated"  2>/dev/null
mkdir tmp/images
export HOME=/tmp && $SOFFICE --headless --convert-to pdf tmp/input.pptx --outdir tmp
$CAIRO -jpeg tmp/input.pdf tmp/images/images
$PYTHON ./CreateMultiLingualAnim.py --input=tmp/input.pptx --ImagesDir="tmp/images" --output=tmp/output.html --from="$From" --to="$To" $5 $6 $7
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"

