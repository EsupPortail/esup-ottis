#!/bin/bash

source config.py
Filename=$1
From=$2
To=$3

if [ $Filename = "" ]; then
        echo "Filename is missing"
        exit
fi

if [ $From = "" ]; then
        From="fr-FR"
fi

if [ $To = "" ]; then
        To="EN,DE"
fi
To=`echo "$To"|sed "s/,/ /g"`

echo "Converting file " $Filename
echo "Options From=$From and To=$To"

#exit -1
rm -rf tmp/*
$FINDER tmp
cp "$Filename" tmp/original_file.pptx
for lang in $To; do
  echo "Translating in $lang"
  ./TranslatePPTX.py --input=tmp/original_file.pptx --output=tmp/translated_$lang.pptx --from="$From" --to="$lang" $4 2>/dev/null
done
