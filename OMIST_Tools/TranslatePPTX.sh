#!/bin/bash

source config.py

Filename=$1
From=$2
To=$3
To=`echo "$To"|sed "s/,/ /g"`

echo "Converting file " $Filename

rm -rf tmp/*

cp "$Filename" tmp/original_file.pptx
for lang in $To; do
  echo "Translating in $lang"
  $PYTHON ./TranslatePPTX.py --input=tmp/original_file.pptx --output=tmp/translated_$lang.pptx --from="$From" --to="$lang" $4 2>/dev/null
done
cd tmp
zip -9 output.zip translated*.pptx
