#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/config.py

RANDOMPATH=$1
Filename=$2
FilenameSpeech=$3

mkdir -p $SCRIPT_DIR/tmp/$RANDOMPATH

cp "$Filename" $SCRIPT_DIR/tmp/$RANDOMPATH/input.pptx
cp "$FilenameSpeech" $SCRIPT_DIR/tmp/$RANDOMPATH/speech.txt

export PYTHONIOENCODING=utf8

$PYTHON $SCRIPT_DIR/PPTX_TXT_to_PPTX.py --input=$SCRIPT_DIR/tmp/$RANDOMPATH/speech.txt --inputpptx=$SCRIPT_DIR/tmp/$RANDOMPATH/input.pptx --output=$SCRIPT_DIR/tmp/$RANDOMPATH/converted

cd $SCRIPT_DIR/tmp/$RANDOMPATH
zip -9 ../../../tmp/output_$RANDOMPATH.zip converted.*
cd ../..
rm -rf $SCRIPT_DIR/tmp/$RANDOMPATH

rm  -f $Filename
if [[ $FilenameSpeech != *"blank.txt" ]]; then
    rm  -f $FilenameSpeech
fi

