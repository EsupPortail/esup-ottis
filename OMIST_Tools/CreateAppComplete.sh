#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

source $SCRIPT_DIR/config.py


RANDOMPATH=$1
Filename=$2
From=$3
To=$4

echo "Converting file " $Filename
cd $INSTALLDIR
cd ..

mkdir -p $INSTALLDIR/$RANDOMPATH

cp "$Filename" $INSTALLDIR/$RANDOMPATH/input.pptx
mkdir $INSTALLDIR/$RANDOMPATH/images
export HOME=$INSTALLDIR/ && $SOFFICE --headless --convert-to pdf:impress_pdf_Export $INSTALLDIR/$RANDOMPATH/input.pptx --outdir $INSTALLDIR/$RANDOMPATH
$CAIRO -jpeg $INSTALLDIR/$RANDOMPATH/input.pdf $INSTALLDIR/$RANDOMPATH/images/images

export PYTHONIOENCODING=utf8

$PYTHON $SCRIPT_DIR/CreateMultiLingualAnim.py --input=$INSTALLDIR/$RANDOMPATH/input.pptx --ImagesDir="$INSTALLDIR/$RANDOMPATH/images" --output=$INSTALLDIR/output_$RANDOMPATH.html --from="$From" --to="$To" $5 $6 $7

cd $INSTALLDIR/$RANDOMPATH
zip -9 $INSTALLDIR/output_$RANDOMPATH.zip *.html
rm -rf $INSTALLDIR/$RANDOMPATH

rm  -f $Filename
