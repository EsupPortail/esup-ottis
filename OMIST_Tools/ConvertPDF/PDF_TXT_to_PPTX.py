#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Convertisseur d'un fichier PDF et d'un fichier TXT en PowerPoint avec des notes.

On obtient en sortie le fichier avec les notes sous 3 formats différents : ODP, PPT et PPTX.

Usage : 
=======
    py PDF_TXT_to_PPTX.py --i <input> -d <ImagesDir> -o <output> --AspectRatio

    <input> : chemin du fichier TXT avec les notes
    <output> : nom du PowerPoint en sortie
    <ImagesDir> : chemin des images extraites du PDF
    --AspectRatio : 
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
sys.path.append("../")
import config

def usage():
    """Explication de l'utilisation du programme.
    """

    print("Usage:")
    print("Minimal : ./PDF_TXT_to_PPTX.py --input test.txt --ImagesDir tmp/images")
    print("Complete : ./TranslatePPTX.py --input test.pptx --ImagesDir tmp/images --output test.pptx --AspectRatio")

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hoid:v", ["help", "output=", "input=","AspectRatio=","ImagesDir="])
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
        elif o in ("-d","--ImagesDir"):
            imgdir = a
        elif o in ("--AspectRatio"):
            C_ASPECTRATIO = a
        else:
            assert False, "unhandled option"
    if Translate and input != "":
        if output == "":
            output="tmp/out.pptx"
        PDFtoPPTX(input,output,imgdir,AspectRatio=eval(C_ASPECTRATIO))
    else:
        usage()

def ExtractNotes(filename):
    """Extraction des notes pour chaque slide.
    
    Parameters
    ----------
    filename : str
        Fichier TXT dont on extrait les notes.

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
                notesslide.append(note.text)
            slidenotes=dict()
            slidenotes["Slide"]=num
            slidenotes["Notes"]=notesslide
            notes.append(slidenotes)
    else:
        content = open(filename, 'r', encoding="utf-8").read().split("\n")
        content.append('-----------')
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

def PDFtoPPTX(filename,fileout,imagesdir='.',AspectRatio=16/10):
    """Ajout des notes pour chaque slide.
    
    Parameters
    ----------
    filename : str
        Fichier TXT avec les notes.
    imagesdir : str
        Chemin du dossier où se situent les images extraites du PDF.
    fileout : str
        Nom du PowerPoint en sortie
    AspectRatio : str
        Ratio de l'image.
    """

    # Gestion des notes
    print("Extracting notes")
    Notes = ExtractNotes(filename)
    prs = Presentation()
    prs.slide_width=int(AspectRatio*prs.slide_height)
    blank_slide_layout = prs.slide_layouts[6]

    # Gestion des images
    AllFilenames=[]
    listfiles = os.listdir(imagesdir)
    for entry in listfiles:
        if (entry.endswith('jpeg') or entry.endswith('jpg')):
            AllFilenames.append(entry)
    AllFilenames.sort()
    AllImages = []
    nslide=0
    for f in AllFilenames:
        print("    adding ",f)
        slide = prs.slides.add_slide(blank_slide_layout)
        pic = slide.shapes.add_picture(imagesdir+"/"+f, 0,0,width=prs.slide_width,height=prs.slide_height)
        notes_slide = slide.notes_slide
        text_frame = notes_slide.notes_text_frame
        Notes.append({"Notes":[""]})
        text_frame.add_paragraph().text = "\n".join(Notes[nslide]["Notes"])
        nslide+=1

    prs.save(fileout+".pptx")
    outputdir=os.path.dirname(fileout)
    batcmd="HOME=/tmp "+config.SOFFICE+" --headless --convert-to odp "+fileout+".pptx"+" --outdir "+outputdir
    result = subprocess.check_output(batcmd, shell=True)
    batcmd="HOME=/tmp "+config.SOFFICE+" --headless --convert-to ppt "+fileout+".pptx"+" --outdir "+outputdir
    result = subprocess.check_output(batcmd, shell=True)

if __name__ == "__main__":
    main()
