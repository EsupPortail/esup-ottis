#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

source $SCRIPT_DIR/../config_sh
TMPDIR=$SCRIPT_DIR/../tmp


RANDOMPATH=$1

PARAMETERFILE=$TMPDIR/$RANDOMPATH/parameters.json

if [ -f "$PARAMETERFILE" ]; then
  Filename=`/usr/bin/php fromJSON.php "$PARAMETERFILE" path_file`
  From=`/usr/bin/php fromJSON.php "$PARAMETERFILE" lang_from`
  To=`/usr/bin/php fromJSON.php "$PARAMETERFILE" lang_to`
  LICENCE=`/usr/bin/php fromJSON.php "$PARAMETERFILE" licence`
else 
  Filename=$2
  From=$3
  To=$4
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
fi
echo "filename= $Filename"
echo "from= $From"
echo "to= $To"
echo "parameters= $PARAMETERFILE"
echo "Licence= $LICENCE"

echo "Converting file " $Filename
cd $TMPDIR
cd ..

mkdir -p $TMPDIR/$RANDOMPATH

cp "$Filename" $TMPDIR/$RANDOMPATH/input.pptx
mkdir -p $TMPDIR/$RANDOMPATH/images
export HOME=$TMPDIR/ && $SOFFICE --headless --convert-to pdf:impress_pdf_Export $TMPDIR/$RANDOMPATH/input.pptx --outdir $TMPDIR/$RANDOMPATH
$CAIRO -jpeg $TMPDIR/$RANDOMPATH/input.pdf $TMPDIR/$RANDOMPATH/images/images

export PYTHONIOENCODING=utf8

$PYTHON $SCRIPT_DIR/CreateMultiLingualAnim.py --input=$TMPDIR/$RANDOMPATH/input.pptx --ImagesDir="$TMPDIR/$RANDOMPATH/images" --output=$TMPDIR/output_$RANDOMPATH.html --from="$From" --to="$To" --randompath="$RANDOMPATH" $5 $6 $7 $8 $9 --Licence="$LICENCE"

#cd $INSTALLDIR/$RANDOMPATH
#zip -9 $INSTALLDIR/output_$RANDOMPATH.zip *.html
rm -rf $INSTALLDIR/$RANDOMPATH
rm  -f $Filename
