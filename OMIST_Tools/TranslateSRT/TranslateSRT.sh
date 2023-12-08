#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`
Wraplength=$5
NOPUNC=$6


export PYTHONIOENCODING=utf8

mkdir -p $SCRIPT_DIR/tmp/$RANDOMPATH

cp "$Filename" $SCRIPT_DIR/tmp/$RANDOMPATH/original_file.srt
for lang in $To; do
  echo "Translating in $lang"
  $PYTHON $SCRIPT_DIR/TranslateSRT.py --input=$SCRIPT_DIR/tmp/$RANDOMPATH/original_file.srt --output=$SCRIPT_DIR/tmp/$RANDOMPATH/translated_$lang.srt --from="$From" --to="$lang" $Wraplength $NOPUNC 2>/dev/null
done

cd $SCRIPT_DIR/tmp/$RANDOMPATH
zip -9 ../../../tmp/output_$RANDOMPATH.zip *.srt *_verbatim.txt
cd ../..
rm -rf $SCRIPT_DIR/tmp/$RANDOMPATH

rm  -f $Filename
