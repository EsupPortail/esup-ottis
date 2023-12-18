#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

source ../config.py


RANDOMPATH=$1
Filename=$2

echo "Converting file " $Filename
cd $INSTALLDIR
cd ..

mkdir -p $INSTALLDIR/$RANDOMPATH

cp "$Filename" $INSTALLDIR/$RANDOMPATH/input.pptx
export PYTHONIOENCODING=utf8
$PYTHON $SCRIPT_DIR/SpeechExtractionFromPPTX.py --input=$INSTALLDIR/$RANDOMPATH/input.pptx --output=$INSTALLDIR/$RANDOMPATH/speech.txt

cd $INSTALLDIR/$RANDOMPATH
zip -9 $INSTALLDIR/output_$RANDOMPATH.zip *.pptx *.txt
cd ..
rm -rf $INSTALLDIR/$RANDOMPATH

rm  -f $Filename


