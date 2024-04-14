#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/../config_sh

RANDOMPATH=$1
Filename=$2
FilenameSpeech=$3

echo "Converting file " $Filename
cd $INSTALLDIR
cd ..

mkdir -p $INSTALLDIR/$RANDOMPATH

cp "$Filename" $INSTALLDIR/$RANDOMPATH/input.pptx
cp "$FilenameSpeech" $INSTALLDIR/$RANDOMPATH/speech.txt

export PYTHONIOENCODING=utf8

$PYTHON $SCRIPT_DIR/PPTX_TXT_to_PPTX.py --input=$INSTALLDIR/$RANDOMPATH/speech.txt --inputpptx=$INSTALLDIR/$RANDOMPATH/input.pptx --output=$INSTALLDIR/$RANDOMPATH/converted

cd $INSTALLDIR/$RANDOMPATH
zip -9 $INSTALLDIR/output_$RANDOMPATH.zip converted.*
cd ..
rm -rf $INSTALLDIR/$RANDOMPATH

rm  -f $Filename
if [[ $FilenameSpeech != *"blank.txt" ]]; then
    rm  -f $FilenameSpeech
fi

