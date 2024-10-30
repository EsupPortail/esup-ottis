#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
source $SCRIPT_DIR/../config_sh

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`

export PYTHONIOENCODING=utf8

echo "Converting file " $Filename
cd $INSTALLDIR
cd ..

mkdir -p $INSTALLDIR/$RANDOMPATH

cp "$Filename" $INSTALLDIR/$RANDOMPATH/original_file.pptx
for lang in $To; do
  echo "Translating in $lang"
  $PYTHON $SCRIPT_DIR/TranslatePPTX.py --input=$INSTALLDIR/$RANDOMPATH/original_file.pptx --output=$INSTALLDIR/$RANDOMPATH/translated_$lang.pptx --from="$From" --to="$lang" $5 #2>/dev/null
done
export HOME=$INSTALLDIR/ && $SOFFICE --headless --convert-to ppt $INSTALLDIR/$RANDOMPATH/translated_*.pptx --outdir $INSTALLDIR/$RANDOMPATH

cd $INSTALLDIR/$RANDOMPATH
zip -9 $INSTALLDIR/output_$RANDOMPATH.zip translated*.pptx translated*.ppt
cd ..
rm -rf $INSTALLDIR/$RANDOMPATH

rm  -f $Filename
