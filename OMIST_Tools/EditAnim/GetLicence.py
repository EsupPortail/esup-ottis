#!/usr/bin/env python
# -*- coding: utf-8 -*-

import getopt, sys
import Licences

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], ":v", ["Licence="])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        usage()
        sys.exit(2)
    C_LICENCE = ""
    for o, a in opts:
        if o == "-v":
            verbose = True
        elif o in ("--Licence"):
            C_LICENCE = a
            if "=" in C_LICENCE:
                C_LICENCE=C_LICENCE.split("=").pop()
        else:
            assert False, "unhandled option"
        
        print("Licence = ",C_LICENCE)
        getLicenceComplete(licence=C_LICENCE)

def getLicenceComplete(licence, complementfile="../CreateApp/Templates/Complements.txt"):
    try:
        with open(complementfile,"r", encoding='utf8') as f:
                complement = "".join(f.readlines())
        if (licence != "" and Licences.LICENCES.get(licence)):
            complement = complement+"\n"+Licences.LICENCES.get(licence)
        return complement
    except Exception as error:
        print("An exception occurred:", error)

if __name__ == "__main__":
    main()