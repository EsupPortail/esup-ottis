#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`

echo "Converting file " $Filename

export PYTHONIOENCODING=utf8

mkdir tmp/$RANDOMPATH
#rm -rf tmp/*
cp "$Filename" tmp/$RANDOMPATH/original_file.pptx
for lang in $To; do
  echo "Translating in $lang"
  $PYTHON ./TranslatePPTX.py --input=tmp/$RANDOMPATH/original_file.pptx --output=tmp/$RANDOMPATH/translated_$lang.pptx --from="$From" --to="$lang" $5 2>/dev/null
done
export HOME=/tmp && $SOFFICE --headless --convert-to ppt tmp/$RANDOMPATH/translated_*.pptx --outdir tmp/$RANDOMPATH
cd tmp/$RANDOMPATH
zip -9 ../output_$RANDOMPATH.zip translated*.pptx
cd ../..
rm -rf tmp/$RANDOMPATH
