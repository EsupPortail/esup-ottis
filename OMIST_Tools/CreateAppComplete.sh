#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4

echo "Converting file " $Filename

mkdir tmp/$RANDOMPATH
#rm -rf tmp/*
cp "$Filename" tmp/$RANDOMPATH/input.pptx
mkdir tmp/$RANDOMPATH/images
export HOME=/tmp && $SOFFICE --headless --convert-to pdf tmp/$RANDOMPATH/input.pptx --outdir tmp/$RANDOMPATH
$CAIRO -jpeg tmp/$RANDOMPATH/input.pdf tmp/$RANDOMPATH/images/images

export PYTHONIOENCODING=utf8

$PYTHON ./CreateMultiLingualAnim.py --input=tmp/$RANDOMPATH/input.pptx --ImagesDir="tmp/$RANDOMPATH/images" --output=tmp/output_$RANDOMPATH.html --from="$From" --to="$To" $5 $6 $7
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"
rm -rf tmp/$RANDOMPATH


