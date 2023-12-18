#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/../config.py

RANDOMPATH=$1
Filename=$2
FilenameSpeech=$3

mkdir -p $SCRIPT_DIR/../tmp/$RANDOMPATH

cp "$Filename" $SCRIPT_DIR/../tmp/$RANDOMPATH/input.pdf
cp "$FilenameSpeech" $SCRIPT_DIR/../tmp/$RANDOMPATH/speech.txt
mkdir $SCRIPT_DIR/../tmp/$RANDOMPATH/images
$CAIRO -jpeg $SCRIPT_DIR/../tmp/$RANDOMPATH/input.pdf $SCRIPT_DIR/../tmp/$RANDOMPATH/images/images  2>/dev/null
export PYTHONIOENCODING=utf8

$PYTHON $SCRIPT_DIR/PDF_TXT_to_PPTX.py --input=$SCRIPT_DIR/../tmp/$RANDOMPATH/speech.txt --ImagesDir=$SCRIPT_DIR/../tmp/$RANDOMPATH/images --output=$SCRIPT_DIR/../tmp/$RANDOMPATH/converted $4  

cd $SCRIPT_DIR/../tmp/$RANDOMPATH
zip -9 $SCRIPT_DIR/../tmp/output_$RANDOMPATH.zip converted.*
cd ../..
rm -rf $SCRIPT_DIR/../tmp/$RANDOMPATH

rm  -f $Filename
if [[ $FilenameSpeech != *"blank.txt" ]]; then
    rm  -f $FilenameSpeech
fi
