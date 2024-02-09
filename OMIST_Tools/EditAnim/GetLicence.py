#!/usr/bin/env python
# -*- coding: utf-8 -*-

import getopt, sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
sys.path.append("../")
sys.path.append("./")
import Licences

# string = ''
# ABSOLUTE_PATH = os.path.dirname(os.path.abspath(__file__))
# complementfile = ABSOLUTE_PATH + "/ComplementsEditAnim.txt"

# for word in sys.argv[1:]:
#     string += word + ' '
# print(string)
# print(complementfile)
# print(os.path.exists(complementfile))
# licence = sys.argv[1]
# print(licence)

# with open(complementfile,"r", encoding='utf8') as f:
#     complement = "".join(f.readlines())
# if (licence != "" and Licences.LICENCES.get(licence)):
#     complement = complement+"\n"+Licences.LICENCES.get(licence)

# print(complement)

# import Licences

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
        
        print("Licence = ",C_LICENCE)
        getLicenceComplete(licence=C_LICENCE)

def getLicenceComplete(licence):
    ABSOLUTE_PATH = os.path.dirname(os.path.abspath(__file__))
    complementfile = ABSOLUTE_PATH + "/ComplementsEditAnim.txt"
    with open(complementfile,"r", encoding='utf8') as f:
            complement = "".join(f.readlines())
    if (licence != "" and Licences.LICENCES.get(licence)):
        complement = complement+"\n"+Licences.LICENCES.get(licence)
    print("Complement: ", complement)


# def helloworld(){
#     return "Hello World"
# }

# if __name__ == "__main__":
#     main()