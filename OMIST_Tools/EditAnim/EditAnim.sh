#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/../config.py

TMPDIR=$SCRIPT_DIR/../tmp

RANDOMPATH=$1
Filename=$2

echo "Converting file " $Filename

export PYTHONIOENCODING=utf8

cd $TMPDIR
cd ..

mkdir -p $TMPDIR/$RANDOMPATH

cp "$Filename" $TMPDIR/$RANDOMPATH/input.html

INPUT=$TMPDIR/$RANDOMPATH/input.html
OUTPUT=$TMPDIR/output_$RANDOMPATH.txt

# $PYTHON $SCRIPT_DIR/EditAnim.py --input=$TMPDIR/$RANDOMPATH/input.html --output=$TMPDIR/output_$RANDOMPATH.txt
/usr/bin/php $SCRIPT_DIR/ExtractSlidesNotesAppHTMLTools.php $OUTPUT $INPUT


#  cd $SCRIPT_DIR/tmp/$RANDOMPATH
#  zip -9 $SCRIPT_DIR/tmp/output_$RANDOMPATH.zip *.html
#  rm -rf $SCRIPT_DIR/tmp/$RANDOMPATH
rm -rf $TMPDIR/$RANDOMPATH
rm  -f $Filename


