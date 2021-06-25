#!/bin/bash

source config.py

Filename=$1
From=$2
To=$3

echo "Converting file " $Filename

rm -rf tmp/*
cp "$Filename" tmp/input.pptx
mkdir tmp/images
export HOME=/tmp && $SOFFICE --headless --convert-to pdf tmp/input.pptx --outdir tmp
$CAIRO -jpeg tmp/input.pdf tmp/images/images
$PYTHON ./CreateMultiLingualAnim.py --input=tmp/input.pptx --ImagesDir="tmp/images" --output=tmp/output.html --from="$From" --to="$To" $4 $5 $6
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"


