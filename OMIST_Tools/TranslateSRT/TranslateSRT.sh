#!/bin/bash

source ../config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`
Wraplength=$5
NOPUNC=$6


export PYTHONIOENCODING=utf8

mkdir -p tmp/$RANDOMPATH

cp "$Filename" tmp/$RANDOMPATH/original_file.srt
for lang in $To; do
  echo "Translating in $lang"
  $PYTHON ./TranslateSRT.py --input=tmp/$RANDOMPATH/original_file.srt --output=tmp/$RANDOMPATH/translated_$lang.srt --from="$From" --to="$lang" $Wraplength $NOPUNC 2>/dev/null
done

cd tmp/$RANDOMPATH
zip -9 ../output_$RANDOMPATH.zip *.srt *_verbatim.txt
cd ../..
rm -rf tmp/$RANDOMPATH
