#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys
import getopt
import shutil

def main():

    try:
        opts, args = getopt.getopt(sys.argv[1:],"h:i:o",["help","input=","output="])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        sys.exit(2)
    
    input=""
    output=""
    verbose = False

    for opt, arg in opts:
        if opt == "-v":
            verbose = True
            sys.exit()
        elif opt in ("-i", "--input"):
            input = arg
        elif opt in ("-o", "--output"):
            output = arg
        else:
            assert False, "unhandled option"
    
    if input != "":
        GetHTMLFile(input,output)

def GetHTMLFile(input, output):
    shutil.copy2(input, output)

if __name__ == "__main__":
    main()