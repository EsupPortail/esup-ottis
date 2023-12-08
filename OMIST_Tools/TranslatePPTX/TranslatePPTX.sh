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

cp "$Filename" $SCRIPT_DIR/tmp/$RANDOMPATH/original_file.pptx
for lang in $To; do
  echo "Translating in $lang"
  $PYTHON $SCRIPT_DIR/TranslatePPTX.py --input=$SCRIPT_DIR/tmp/$RANDOMPATH/original_file.pptx --output=$SCRIPT_DIR/tmp/$RANDOMPATH/translated_$lang.pptx --from="$From" --to="$lang" $5 #2>/dev/null
done
export HOME=$SCRIPT_DIR/tmp && $SOFFICE --headless --convert-to ppt $SCRIPT_DIR/tmp/$RANDOMPATH/translated_*.pptx -outdir $SCRIPT_DIR/tmp/$RANDOMPATH

cd $SCRIPT_DIR/tmp/$RANDOMPATH
zip -9 ../../../tmp/output_$RANDOMPATH.zip translated*.pptx translated*.ppt
cd ../..
rm -rf $SCRIPT_DIR/tmp/$RANDOMPATH

rm  -f $Filename
