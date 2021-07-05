#!/bin/bash

source config.py

Filename=$1
RANDOMPATH=$2

echo "Converting file " $Filename

export PYTHONIOENCODING=utf8

mkdir tmp/$RANDOMPATH
#rm -rf tmp/*
cp "$Filename" tmp/$RANDOMPATH/input.pptx
mkdir tmp/$RANDOMPATH/images
export HOME=/tmp && $SOFFICE --headless --convert-to pdf:impress_pdf_Export tmp/$RANDOMPATH/input.pptx --outdir tmp/$RANDOMPATH
$CAIRO -jpeg tmp/$RANDOMPATH/input.pdf tmp/$RANDOMPATH/images/images
$PYTHON ./CreateMultiLingualAnim.py --input=tmp/$RANDOMPATH/input.pptx --ImagesDir="tmp/$RANDOMPATH/images" --output=tmp/output_$RANDOMPATH.html --from="fr-FR" --to="fr-FR" $3 $4 $5
rm -rf tmp/$RANDOMPATH
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"

