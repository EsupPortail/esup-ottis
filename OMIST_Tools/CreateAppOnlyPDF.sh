#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/config.py

RANDOMPATH=$1
Filename=$2
Speech=$3

export PYTHONIOENCODING=utf8
echo "Converting file " $Filename
cd $INSTALLDIR
cd ..
mkdir -p $INSTALLDIR/$RANDOMPATH

cp "$Filename" $INSTALLDIR/$RANDOMPATH/input.pdf
cp "$Speech" $INSTALLDIR/$RANDOMPATH/speech.txt
mkdir $INSTALLDIR/$RANDOMPATH/images
$CAIRO -jpeg $INSTALLDIR/$RANDOMPATH/input.pdf $INSTALLDIR/$RANDOMPATH/images/images
$PYTHON $SCRIPT_DIR/CreateMultiLingualAnim.py --input=$INSTALLDIR/$RANDOMPATH/speech.txt --ImagesDir="$INSTALLDIR/$RANDOMPATH/images" --output=$INSTALLDIR/output_$RANDOMPATH.html --from="fr-FR" --to="fr-FR" $4 $5

cd $INSTALLDIR/$RANDOMPATH
zip -9 $INSTALLDIR/output_$RANDOMPATH.zip *.html
rm -rf $INSTALLDIR/$RANDOMPATH

rm -f $Filename
if [[ $Speech != *"blank.txt" ]]; then
    rm  -f $Speech
fi

