#!/usr/bin/env python
# -*- coding: utf-8 -*-

import getopt, sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
sys.path.append("../")
sys.path.append("./")
import Licences

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "l:v", ["licence="])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        usage()
        sys.exit(2)
    C_LICENCE = ""
    for o, a in opts:
        if o == "-v":
            verbose = True
        elif o in ("--licence"):
            C_LICENCE = a
            if "=" in C_LICENCE:
                C_LICENCE=C_LICENCE.split("=").pop()
        else:
            assert False, "unhandled option"
        
        # print("Licence = ",C_LICENCE)
        getLicenceComplete(licence=C_LICENCE)

def getLicenceComplete(licence):
    complement = ""
    if (licence != "" and Licences.LICENCES.get(licence)):
        complement = Licences.LICENCES.get(licence)
    print(complement)

if __name__ == "__main__":
    main()