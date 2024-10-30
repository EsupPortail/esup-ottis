#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/../config_sh

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`
Wraplength=$5
NOPUNC=$6


export PYTHONIOENCODING=utf8

echo "Converting file " $Filename
cd $INSTALLDIR
cd ..

mkdir -p $INSTALLDIR/$RANDOMPATH

cp "$Filename" $INSTALLDIR/$RANDOMPATH/original_file.srt
for lang in $To; do
  echo "Translating in $lang"
  $PYTHON $SCRIPT_DIR/TranslateSRT.py --input=$INSTALLDIR/$RANDOMPATH/original_file.srt --output=$INSTALLDIR/$RANDOMPATH/translated_$lang.srt --from="$From" --to="$lang" --randompath="$RANDOMPATH" $Wraplength $NOPUNC 2>/dev/null
done

cd $INSTALLDIR/$RANDOMPATH
zip -9 $INSTALLDIR/output_$RANDOMPATH.zip *.srt *_verbatim.txt
cd ..
rm -rf $INSTALLDIR/$RANDOMPATH

rm  -f $Filename
