#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/../config_sh
TMPDIR=$SCRIPT_DIR/../tmp

RANDOMPATH=$1
Filename=$2
Speech=$3

export PYTHONIOENCODING=utf8
echo "Converting file " $Filename
cd $TMPDIR
cd ..
mkdir -p $TMPDIR/$RANDOMPATH

cp "$Filename" $TMPDIR/$RANDOMPATH/input.pdf
cp "$Speech" $TMPDIR/$RANDOMPATH/speech.txt
mkdir $TMPDIR/$RANDOMPATH/images
$CAIRO -jpeg $TMPDIR/$RANDOMPATH/input.pdf $TMPDIR/$RANDOMPATH/images/images
$PYTHON $SCRIPT_DIR/CreateMultiLingualAnim.py --input=$TMPDIR/$RANDOMPATH/speech.txt --ImagesDir="$TMPDIR/$RANDOMPATH/images" --output=$TMPDIR/output_$RANDOMPATH.html --from="fr-FR" --to="fr-FR" $4 $5

#cd $TMPDIR/$RANDOMPATH
#zip -9 $TMPDIR/output_$RANDOMPATH.zip *.html
rm -rf $TMPDIR/$RANDOMPATH

rm -f $Filename
if [[ $Speech != *"blank.txt" ]]; then
    rm  -f $Speech
fi

