#!/bin/bash

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"
SCRIPT_DIR2="$(dirname "$(readlink -f "$SCRIPT_DIR")")"
SCRIPT_DIR3="$(dirname "$(readlink -f "$SCRIPT_DIR2")")"
source $SCRIPT_DIR3/OMIST_Tools/config_sh

export https_proxy='http://cache.univ-nantes.fr:3128'
export http_proxy='http://cache.univ-nantes.fr:3128'


$PYTHON $SCRIPT_DIR/generateStatsGraph.py 
#2>/dev/null
