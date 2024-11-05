#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Traduction d'un fichier PPTX.

On obtient en sortie le fichier traduit au format PPT et PPTX.

Usage : 
=======
    py TranslatePPTX.py -i <input> -o <output> -f <from> -t <to> -r <randompath> --WithNotes

    <input> : chemin du fichier PPTX à traduire
    <output> : nom du fichier PowerPoint traduit
    <from> : langue d'origine
    <to> : langue de traduction
    <randompath> : nom aléatoire associé à l'action
    WithNotes : Traduction des notes des slides
"""

import getopt, sys
from pptx import Presentation

import subprocess
import json
import base64
import os
import pipes
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
sys.path.append("../")
import config
import requests
from utils import translate
from utils import ConnexionDB

def usage():
    """Explication de l'utilisation du programme.
    """

    print("Usage:")
    print("Minimal : ./TranslatePPTX.py --input test.pptx")
    print("Complete : ./TranslatePPTX.py --input test.pptx --output testEN.pptx --from FR --to EN --randompath \"abc\" --WithNotes")

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hoiftr:vW", ["help", "output=", "input=","from=","to=","randompath=","WithNotes"])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        usage()
        sys.exit(2)
    Translate = True
    WithNotes = False
    input=""
    output=""
    lfrom="FR"
    lto="EN"
    verbose = False
    rdmpath = ""
    for o, a in opts:
        if o == "-v":
            verbose = True
        elif o in ("-h", "--help"):
            usage()
            sys.exit()
        elif o in ("-i", "--input"):
            input = a
        elif o in ("-o", "--output"):
            output = a
        elif o in ("-f", "--from"):
            lfrom = a
        elif o in ("-t", "--to"):
            lto = a
        elif o in ("-r", "--randompath"):
            rdmpath = a 
        elif o in ("-W","--WithNotes"):
            WithNotes = True
        else:
            assert False, "unhandled option"
    if Translate and input != "":
        if output == "":
            output=input.replace(".pptx","_"+lto+".pptx")
        TranslatePPTX(input,output,lfrom,lto,rdm_path=rdmpath,translate_notes=WithNotes)
    else:
        usage()

def TranslatePPTX(filename,fileout,langsrc="FR",langdst="EN",rdm_path="",translate_notes=False):
    """Traduction d'un fichier PPTX.
    
    Parameters
    ----------
    filename : str
        Chemin du fichier PPTX à traduire.
    output_filename : str
        Nom du fichier PowerPoint traduit.
    langsrc : str
        Langue d'origine.
    langdst : str
        Langue de traduction.
    rdm_path : str
        Nom aléatoire associé à l'action.
    translate_notes : boolean
        True si traduction des notes des slides.
    """

    translated_characters=0
    prs = Presentation(filename)
    ns=1
    for slide in prs.slides:
        print("Translating slide ",ns)
        ns+=1
        if translate_notes:
            for note in slide.notes_slide.notes_text_frame.paragraphs:
                translated = translate(note.text,langsrc,langdst)
                note.text = translated+" "
        for shape in slide.shapes:
            if not shape.has_text_frame:
                continue
            for paragraph in shape.text_frame.paragraphs:
                for run in paragraph.runs:
                    if (translated_characters<config.TRANSLATION_LIMIT):
                        translated_characters+=len(run.text)
                        run.text = " "+translate(run.text,langsrc,langdst)+" "
    ConnexionDB(rdm_path, translated_characters)
    prs.save(fileout)

if __name__ == "__main__":
    main()
