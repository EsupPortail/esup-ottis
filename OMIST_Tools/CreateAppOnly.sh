#!/bin/bash

source config.py

Filename=$1

if [ $Filename = "" ]; then
        echo "Filename is missing"
        exit
fi

echo "Converting file " $Filename

rm -rf tmp/*
cp "$Filename" tmp/input.pptx
mkdir tmp/images
export HOME=/tmp && $SOFFICE --headless --convert-to pdf tmp/input.pptx --outdir tmp
pdftocairo -jpeg tmp/input.pdf tmp/images/images
python3 ./CreateMultiLingualAnim.py --input=tmp/input.pptx --ImagesDir="tmp/images" --output=tmp/output.html --from="fr-FR" --to="fr-FR" $2 $3 $4
# ./CreateMultiLingualAnim.py --input tmp/test.pptx --ImagesDir "tmp/test" --output tmp/out.html --to "EN,DE"

