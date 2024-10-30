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
  Translated=`/usr/bin/php fromJSON.php "$PARAMETERFILE" translate_lang`
else 
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
fi
echo "filename= $Filename"
echo "from= $From"
echo "to= $To"
echo "parameters= $PARAMETERFILE"
echo "Licence= $LICENCE"



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
cd $TMPDIR
cd ..

mkdir -p $TMPDIR/$RANDOMPATH

cp "$Filename" $TMPDIR/$RANDOMPATH/original_file.pptx
$PYTHON $SCRIPT_DIR/../TranslatePPTX/TranslatePPTX.py --input=$TMPDIR/$RANDOMPATH/original_file.pptx --output=$TMPDIR/$RANDOMPATH/input.pptx --from="$From" --to="$Translated" --randompath="$RANDOMPATH"
mkdir $TMPDIR/$RANDOMPATH/images
export HOME=$TMPDIR/ && $SOFFICE --headless --convert-to pdf:impress_pdf_Export $TMPDIR/$RANDOMPATH/input.pptx --outdir $TMPDIR/$RANDOMPATH
$CAIRO -jpeg $TMPDIR/$RANDOMPATH/input.pdf $TMPDIR/$RANDOMPATH/images/images
$PYTHON $SCRIPT_DIR/CreateMultiLingualAnim.py --input=$TMPDIR/$RANDOMPATH/input.pptx --ImagesDir="$TMPDIR/$RANDOMPATH/images" --output=$TMPDIR/output_$RANDOMPATH.html --from="$From" --to="$To" --randompath="$RANDOMPATH" $6 $7 $8 --Licence="$LICENCE"

#cd $INSTALLDIR/$RANDOMPATH
#zip -9 $INSTALLDIR/output_$RANDOMPATH.zip *.html
rm -rf $RANDOMPATH

rm  -f $Filename
