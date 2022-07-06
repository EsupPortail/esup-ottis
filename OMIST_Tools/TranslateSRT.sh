#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`

echo "Converting file " $Filename " from " $From " to " $To 

export PYTHONIOENCODING=utf8

mkdir -f tmp/$RANDOMPATH
#rm -rf tmp/*
cp "$Filename" tmp/$RANDOMPATH/original_file.srt
for lang in $To; do
  echo "Translating in $lang"
  $PYTHON ./TranslateSRT.py --input=tmp/$RANDOMPATH/original_file.srt --output=tmp/$RANDOMPATH/translated_$lang.srt --from="$From" --to="$lang" 2>/dev/null
done
cd tmp/$RANDOMPATH
zip -9 ../output_$RANDOMPATH.zip *.srt
cd ../..
#rm -rf tmp/$RANDOMPATH
