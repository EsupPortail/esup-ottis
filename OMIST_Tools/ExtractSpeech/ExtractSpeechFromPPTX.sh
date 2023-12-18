#!/bin/sh

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

echo "SCRIPT DIR =" $SCRIPT_DIR
ls ../config.py

#source $SCRIPT_DIR/../config.py
source ../config.py


RANDOMPATH=$1
Filename=$2

echo "Converting file " $Filename
cd $INSTALLDIR
cd ..

mkdir -p $SCRIPT_DIR/tmp/$RANDOMPATH

cp "$Filename" $SCRIPT_DIR/tmp/$RANDOMPATH/input.pptx
export PYTHONIOENCODING=utf8
$PYTHON $SCRIPT_DIR/SpeechExtractionFromPPTX.py --input=$SCRIPT_DIR/tmp/$RANDOMPATH/input.pptx --output=$SCRIPT_DIR/tmp/speech_$RANDOMPATH.txt

cd $SCRIPT_DIR/tmp/$RANDOMPATH
zip -9 ../../../tmp/output_$RANDOMPATH.zip *.pptx *.txt
cd ../..
rm -rf $SCRIPT_DIR/tmp/$RANDOMPATH

rm  -f $Filename


