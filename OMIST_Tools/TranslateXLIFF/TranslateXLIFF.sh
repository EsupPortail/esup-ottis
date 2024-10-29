#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
# Ajout JB (pour être sûr)
SCRIPT_DIR=/var/www/html/AutomaticTranslator/OMIST_Tools
source $SCRIPT_DIR/config_sh

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`

export PYTHONIOENCODING=utf8

mkdir -p $SCRIPT_DIR/tmp/$RANDOMPATH

# Modif JB (j'ai ajouté le répertoire TranslateXLIFF)
cp "$Filename" $SCRIPT_DIR/tmp/$RANDOMPATH/original_file.xlf
for lang in $To; do
  $PYTHON $SCRIPT_DIR/TranslateXLIFF/TranslateXLIFF.py --input=$SCRIPT_DIR/tmp/$RANDOMPATH/original_file.xlf --output=$SCRIPT_DIR/tmp/$RANDOMPATH/translated_$lang.xlf --from="$From" --to="$lang" --randompath="$RANDOMPATH" 2>/dev/null
done

cd $SCRIPT_DIR/tmp/$RANDOMPATH
# Modif JB
zip -9 $SCRIPT_DIR/tmp/output_$RANDOMPATH.zip *.xlf
#zip -9 ../../../tmp/output_$RANDOMPATH.zip *.xlf
cd ../..
rm -rf $SCRIPT_DIR/tmp/$RANDOMPATH
rm  -f $Filename
