#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

source $SCRIPT_DIR/config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
Translated=$5

echo "Converting file " $Filename

export PYTHONIOENCODING=utf8

mkdir -p $SCRIPT_DIR/tmp/$RANDOMPATH

cp "$Filename" $SCRIPT_DIR/tmp/$RANDOMPATH/original_file.pptx
$PYTHON $SCRIPT_DIR/TranslatePPTX/TranslatePPTX.py --input=$SCRIPT_DIR/tmp/$RANDOMPATH/original_file.pptx --output=$SCRIPT_DIR/tmp/$RANDOMPATH/input.pptx --from="$From" --to="$Translated"
mkdir $SCRIPT_DIR/tmp/$RANDOMPATH/images
export HOME=$SCRIPT_DIR/tmp && $SOFFICE --headless --convert-to pdf:impress_pdf_Export $SCRIPT_DIR/tmp/$RANDOMPATH/input.pptx --outdir $SCRIPT_DIR/tmp/$RANDOMPATH
$CAIRO -jpeg $SCRIPT_DIR/tmp/$RANDOMPATH/input.pdf $SCRIPT_DIR/tmp/$RANDOMPATH/images/images
$PYTHON $SCRIPT_DIR/CreateMultiLingualAnim.py --input=$SCRIPT_DIR/tmp/$RANDOMPATH/input.pptx --ImagesDir="$SCRIPT_DIR/tmp/$RANDOMPATH/images" --output=$SCRIPT_DIR/tmp/output_$RANDOMPATH.html --from="$From" --to="$To" $6 $7 $8

cd $SCRIPT_DIR/tmp/$RANDOMPATH
zip -9 $SCRIPT_DIR/tmp/output_$RANDOMPATH.zip *.html
rm -rf $SCRIPT_DIR/tmp/$RANDOMPATH

rm  -f $Filename
