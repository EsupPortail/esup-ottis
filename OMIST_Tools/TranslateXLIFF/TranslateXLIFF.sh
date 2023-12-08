#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`

export PYTHONIOENCODING=utf8

mkdir -p $SCRIPT_DIR/tmp/$RANDOMPATH

cp "$Filename" $SCRIPT_DIR/tmp/$RANDOMPATH/original_file.xlf
for lang in $To; do
  $PYTHON $SCRIPT_DIR/TranslateXLIFF.py --input=$SCRIPT_DIR/tmp/$RANDOMPATH/original_file.xlf --output=$SCRIPT_DIR/tmp/$RANDOMPATH/translated_$lang.xlf --from="$From" --to="$lang" 2>/dev/null
done

cd $SCRIPT_DIR/tmp/$RANDOMPATH
zip -9 ../../../tmp/output_$RANDOMPATH.zip *.xlf
cd ../..
rm -rf $SCRIPT_DIR/tmp/$RANDOMPATH
rm  -f $Filename
