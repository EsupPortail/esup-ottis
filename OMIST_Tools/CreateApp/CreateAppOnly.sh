#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

source $SCRIPT_DIR/../config_sh

TMPDIR=$SCRIPT_DIR/../tmp

Filename=$1
RANDOMPATH=$2

export PYTHONIOENCODING=utf8

echo "Converting file " $Filename
cd $TMPDIR
cd ..

mkdir -p $TMPDIR/$RANDOMPATH

cp "$Filename" $TMPDIR/$RANDOMPATH/input.pptx
mkdir $TMPDIR/$RANDOMPATH/images
export HOME=$TMPDIR/ && $SOFFICE --headless --convert-to pdf:impress_pdf_Export $TMPDIR/$RANDOMPATH/input.pptx --outdir $TMPDIR/$RANDOMPATH
$CAIRO -jpeg $TMPDIR/$RANDOMPATH/input.pdf $TMPDIR/$RANDOMPATH/images/images
$PYTHON $SCRIPT_DIR/CreateMultiLingualAnim.py --input=$TMPDIR/$RANDOMPATH/input.pptx --ImagesDir="$TMPDIR/$RANDOMPATH/images" --output=$TMPDIR/output_$RANDOMPATH.html --from="fr-FR" --to="fr-FR" $3 $4 $5


#cd $INSTALLDIR/$RANDOMPATH
#zip -9 $INSTALLDIR/output_$RANDOMPATH.zip *.html
rm -rf $TMPDIR/$RANDOMPATH
rm  -f $Filename
