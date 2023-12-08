#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/config.py

RANDOMPATH=$1
Filename=$2
Speech=$3
From=$4
To=$5

echo "Converting file " $Filename

export PYTHONIOENCODING=utf8

mkdir -p $SCRIPT_DIR/tmp/$RANDOMPATH

cp "$Filename" $SCRIPT_DIR/tmp/$RANDOMPATH/input.pdf
cp "$Speech" $SCRIPT_DIR/tmp/$RANDOMPATH/speech.txt
mkdir $SCRIPT_DIR/tmp/$RANDOMPATH/images
$CAIRO -jpeg $SCRIPT_DIR/tmp/$RANDOMPATH/input.pdf $SCRIPT_DIR/tmp/$RANDOMPATH/images/images
$PYTHON $SCRIPT_DIR/CreateMultiLingualAnim.py --input=$SCRIPT_DIR/tmp/$RANDOMPATH/speech.txt --ImagesDir="$SCRIPT_DIR/tmp/$RANDOMPATH/images" --output=$SCRIPT_DIR/tmp/output_$RANDOMPATH.html --from="$From" --to="$To" $6 $7 $8

cd $SCRIPT_DIR/tmp/$RANDOMPATH
zip -9 $SCRIPT_DIR/tmp/output_$RANDOMPATH.zip *.html
rm -rf $SCRIPT_DIR/tmp/$RANDOMPATH

rm  -f $Filename
if [[ $Speech != *"blank.txt" ]]; then
    rm  -f $Speech
fi

