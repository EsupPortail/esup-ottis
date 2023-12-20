#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

source $SCRIPT_DIR/config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
Translated=$5

# Ci-dessous à généraliser
LICENCE="None"

for i in "$@"; do
  case $i in
    -l=*|--Licence=*)
      LICENCE="${i#*=}"
      shift # past argument=value
      ;;
    -*|--*)
      ;;
    *)
      ;;
  esac
done

export PYTHONIOENCODING=utf8

echo "Converting file " $Filename
cd $INSTALLDIR
cd ..

mkdir -p $INSTALLDIR/$RANDOMPATH

cp "$Filename" $INSTALLDIR/$RANDOMPATH/original_file.pptx
$PYTHON $SCRIPT_DIR/TranslatePPTX/TranslatePPTX.py --input=$INSTALLDIR/$RANDOMPATH/original_file.pptx --output=$INSTALLDIR/$RANDOMPATH/input.pptx --from="$From" --to="$Translated"
mkdir $INSTALLDIR/$RANDOMPATH/images
export HOME=$INSTALLDIR/ && $SOFFICE --headless --convert-to pdf:impress_pdf_Export $INSTALLDIR/$RANDOMPATH/input.pptx --outdir $INSTALLDIR/$RANDOMPATH
$CAIRO -jpeg $INSTALLDIR/$RANDOMPATH/input.pdf $INSTALLDIR/$RANDOMPATH/images/images
$PYTHON $SCRIPT_DIR/CreateMultiLingualAnim.py --input=$INSTALLDIR/$RANDOMPATH/input.pptx --ImagesDir="$INSTALLDIR/$RANDOMPATH/images" --output=$INSTALLDIR/output_$RANDOMPATH.html --from="$From" --to="$To" $6 $7 $8 --Licence="$LICENCE"

cd $INSTALLDIR/$RANDOMPATH
zip -9 $INSTALLDIR/output_$RANDOMPATH.zip *.html
rm -rf $RANDOMPATH

rm  -f $Filename
