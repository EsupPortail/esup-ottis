#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`

export PYTHONIOENCODING=utf8

mkdir -p tmp/$RANDOMPATH

cp "$Filename" tmp/$RANDOMPATH/original_file.xlf
for lang in $To; do
  $PYTHON ./TranslateXLIFF.py --input=tmp/$RANDOMPATH/original_file.xlf --output=tmp/$RANDOMPATH/translated_$lang.xlf --from="$From" --to="$lang" 2>/dev/null
done

cd tmp/$RANDOMPATH
zip -9 ../output_$RANDOMPATH.zip *.xlf *.txt
cd ../..
rm -rf tmp/$RANDOMPATH

