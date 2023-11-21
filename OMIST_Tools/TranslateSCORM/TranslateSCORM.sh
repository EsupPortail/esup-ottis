#!/bin/bash

source config.py

RANDOMPATH=$1
Filename=$2
From=$3
To=$4
To=`echo "$To"|sed "s/,/ /g"`


echo "Converting file " $Filename " from " $From " to " $To " in " $RANDOMPATH 

export PYTHONIOENCODING=utf8

mkdir -p tmp/$RANDOMPATH
#rm -rf tmp/*

cp "$Filename" tmp/$RANDOMPATH/original_file.zip
#unzip "tmp/$RANDOMPATH/original_file.zip" -d tmp/$RANDOMPATH/
# cp tmp/$RANDOMPATH/scormcontent/*.html tmp/$RANDOMPATH/original_file.html
for lang in $To; do
  $PYTHON ./TranslateSCORM.py --input=tmp/$RANDOMPATH/original_file.zip --output=tmp/$RANDOMPATH/scormcontent/index.html --from="$From" --to="$lang" --unzip_path=tmp/$RANDOMPATH/ 2>/dev/null
done

cd tmp/$RANDOMPATH
rm original_file.zip
zip -9 -r ../output_$RANDOMPATH.zip *
cd ../..
rm -rf tmp/$RANDOMPATH
