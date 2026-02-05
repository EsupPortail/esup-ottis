#!/usr/bin/env python
# -*- coding: utf-8 -*-

import getopt, sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
sys.path.append("../")
sys.path.append("./")
from utils import translate
import numpy as np

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "mft", ["message=","from=","to=",])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        usage()
        sys.exit(2)
    lfrom="FR"
    lto="EN"
    verbose = False
    for o, a in opts:
        if o == "-v":
            verbose = True
        elif o in ("-m", "--message"):
            message = a
        elif o in ("-f", "--from"):
            lfrom = a
        elif o in ("-t", "--to"):
            lto = a
        else:
            assert False, "unhandled option"

    TranslateMessage(message, lfrom, lto)


def TranslateMessage(message, lfrom, lto):
    tab_translated = []
    tab_lang = lto.split(",")
    i = 0
    for lang in tab_lang:
        translated = translate(message, lfrom, lang[:2])
        translated = translated.replace(",", " ")
        tab_translated.append(translated)
    print(tab_translated)

if __name__ == "__main__":
    main()
