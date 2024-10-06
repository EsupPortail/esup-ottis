#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

NBJOURS="+10"
PATHFILES=$SCRIPT_DIR/tmp
REGEXNAME="*.zip"
cd /
echo "Répertoire = " $PATHFILES
#find $PATHFILES -name $REGEXNAME -mtime $NBJOURS -exec rm -rf {} \;