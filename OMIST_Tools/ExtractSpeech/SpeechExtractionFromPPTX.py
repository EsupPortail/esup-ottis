#!/usr/bin/env python3.5
# -*- coding: utf-8 -*-

"""Extraction du discours d'un fichier PPTX.

On obtient en sortie le discours au format TXT.

Usage : 
=======
    py SpeechExtraction.py -i <input_file> -o <output_file>

    <input_file> : chemin du fichier PPTX
    <output_file> : nom du fichier TXT avec le discours
"""

import getopt, sys
import collections
import collections.abc
from pptx import Presentation

import subprocess
import json
import base64
import os
import pipes
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
# Ajout JB (ne trouve pas config.py)
sys.path.append('../')
# Fin
import config

def usage():
    """Explication de l'utilisation du programme.
    """

    print("Usage:")
    print("Minimal : ./SpeechExtraction.py --input test.pptx")
    print("Complete : ./SpeechExtraction.py --input test.pptx --output test_speech.txt")

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hoi:v", ["help", "output=", "input="])
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
    C_ASPECTRATIO = "16/10"
    verbose = False
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
        else:
            assert False, "unhandled option"
    if Translate and input != "":
        if output == "":
            output="tmp/out.txt"
        SpeechExtract(input,output)
    else:
        usage()

def ExtractNotes(filename):
    """Extraction des notes pour chaque slide.
    
    Parameters
    ----------
    filename : str
        Fichier PPTX dont on extrait les notes.

    Returns
    -------
    array
        Les notes et les slides associées.
    """

    if filename.split(".")[-1] == "pptx":
        prs = Presentation(filename)
        notes=[]
        num=0
        for slide in prs.slides:
            num+=1
            notesslide=[]
            for note in slide.notes_slide.notes_text_frame.paragraphs:
                notesslide.append(str(note.text))
            slidenotes=dict()
            slidenotes["Slide"]=num
            slidenotes["Notes"]=notesslide
            notes.append(slidenotes)
    else:
        content = open(filename, 'r',encoding='utf-8').read().split("\n")
        notes=[]
        num=0
        notesslide=[]
        for txt in content:
            if txt.find("----------")>=0 or txt.find("[forward]")>=0:
                if (num>0):
                    slidenotes=dict()
                    slidenotes["Slide"]=num
                    slidenotes["Notes"]=notesslide
                    notes.append(slidenotes)
                    notesslide=[]
                num+=1
            else:
                notesslide.append(txt)
    return notes

def SpeechExtract(filename,fileout):
    """Création d'un fichier TXT avec les notes du PPTX pour chaque slide.
    
    Parameters
    ----------
    filename : str
        Nom du fichier PPTX dont on extrait les notes.
    fileout : str
        Nom du fichier TXT où il y aura les notes.
    """

    # Gestion des notes
    print("Extracting notes")
    Notes = ExtractNotes(filename)
    fd=open(fileout,"w",encoding='utf-8')
    for note in Notes:
        fd.write("------------- Slide "+str(note["Slide"])+" -------------\n")
        fd.write("\n".join(note["Notes"])+"\n")
    fd.close()


if __name__ == "__main__":
    main()
