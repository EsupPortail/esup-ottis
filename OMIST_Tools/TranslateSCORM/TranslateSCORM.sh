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

cp "$Filename" $INSTALLDIR/$RANDOMPATH/original_file.zip

for lang in $To; do
  $PYTHON $SCRIPT_DIR/TranslateSCORM.py --input=$INSTALLDIR/$RANDOMPATH/original_file.zip --output=$INSTALLDIR/$RANDOMPATH/scormcontent/index.html --from="$From" --to="$lang" --unzip_path=$INSTALLDIR/$RANDOMPATH/ 2>/dev/null
done

cd $INSTALLDIR/$RANDOMPATH
zip -9 -r $INSTALLDIR/output_$RANDOMPATH.zip *
cd ../..
rm -rf $INSTALLDIR/$RANDOMPATH

rm  -f $Filename
