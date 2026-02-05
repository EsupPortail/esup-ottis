#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Création d'une animation web sous forme de diaporama.

On obtient en sortie le fichier html de l'animation.

Usage : 
=======
    py CreateMultiLingualAnim.py -p <parameters.json> -i <input> -o <output> -f <from> -t <to> -d <ImagesDir> -r <randompath> --AspectRatio --KeepAspectRatio --Delay --Title --Name --Template --Licence

    <input> : chemin du fichier contenant la présentation à animer
    <output> : nom du fichier html de l'animation
    <from> : langue d'origine
    <to> : langue de traduction
    <ImagesDir> : chemin du dossier contenant les images extraites de la présentation
    <randompath> : nom aléatoire associé à l'action
    --AspectRatio : ratio de l'animation
    --KeepAspectRatio : le ratio est gardé
    --Delay : ajout d'un délai entre chaque slide
    --Title : titre de la page web de l'animation
    --Name :
    --Template : template de la page web de l'animation
    --Licence : Licence éventuelle (None, CC0, CCBY, CCBYNC, CCBYNCSA, CCBYND,....)
"""

import getopt, sys
import collections
import collections.abc
from pptx import Presentation
import requests
import subprocess
import json
import base64
import os
import pipes
import sys
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
sys.path.append("../")
sys.path.append("./")
import config
from utils import translate
from utils import ExtractNotes
from utils import AspectRatio
from utils import ConnexionDB
import Licences

ABSOLUTE_PATH = os.path.dirname(os.path.abspath(__file__))

def usage():
    """Explication de l'utilisation du programme.
    """

    print("Usage:")
    print("Minimal : ./CreateMultiLingualAnim.py --input test.pptx")
    print("Complete : ./CreateMultiLingualAnim.py --input test.pptx --output out.html --to \"EN,DE,IT,HK\" --randompath \"abc\" --ImagesDir /tmp/images --AspectRatio --KeepAspectRatio --Delay --Title --Name --Template")

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "phoiftdr:v", ["help", "output=", "input=","from=","to=","ImagesDir=","randompath=","AspectRatio=","KeepAspectRatio=","Delay=","Title=","Name=","Template=","Licence="])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        usage()
        sys.exit(2)
    Translate = True
    WithNotes = False
    input=""
    output=""
    lfrom="fr-FR"
    lto="en,de"
    imgdir='.'
    C_ASPECTRATIO = "auto" # "16/9"
    C_KEEPASPECTRATIO = "Y"
    C_FILENAME = "Traduction"
    C_TITRE = "Automatic Translation"
    C_templatefile=ABSOLUTE_PATH + "/Templates/AppTemplate.txt"
    C_DELAY=0
    verbose = False
    C_LICENCE = ""
    C_PARAMETERS = ""
    rdmpath = ""
    for o, a in opts:
        if o == "-v":
            verbose = True
        elif o in ("-h", "--help"):
            usage()
            sys.exit()
        elif o in ("-p"):
            C_PARAMETERS = a
        elif o in ("-i", "--input"):
            input = a
        elif o in ("-o", "--output"):
            output = a
        elif o in ("-f", "--from"):
            lfrom = a
        elif o in ("-t", "--to"):
            lto = a
        elif o in ("-d","--ImagesDir"):
            imgdir = a
        elif o in ("-r", "--randompath"):
            rdmpath = a 
        elif o in ("--AspectRatio"):
            C_ASPECTRATIO = a
        elif o in ("--KeepAspectRatio"):
            C_KEEPASPECTRATIO = a
        elif o in ("--Title"):
            C_TITRE = a
        elif o in ("--Name"):
            C_FILENAME = a
        elif o in ("--Template"):
            C_templatefile = a
        elif o in ("--Delay"):
            C_DELAY = int(a)
        elif o in ("--Licence"):
            C_LICENCE = a
            if "=" in C_LICENCE:
                C_LICENCE=C_LICENCE.split("=").pop()
        else:
            assert False, "unhandled option"
    if C_PARAMETERS != "":
        with open(C_PARAMETERS) as json_data:
            allparams = json.load(json_data)
        
    if Translate and input != "":
        if output == "":
            output="out.html"
        print("Input = ",input)
        print("Output = ",output)
        print("Licence = ",C_LICENCE)
        CreateMultilingualApp(input,output,imagesdir=imgdir,source=lfrom,TargetLanguages=lto.lower().split(","),ASPECTRATIO=C_ASPECTRATIO,KEEPASPECTRATIO=C_KEEPASPECTRATIO,TITRE=C_TITRE,FILENAME=C_FILENAME,templatefile=C_templatefile,delay=C_DELAY,licence=C_LICENCE, rdm_path=rdmpath)
    else:
        usage()

def readbase64(filename):
    """Conversion d'une image en base64.
    
    Parameters
    ----------
    filename : str
        Chemin de l'image.

    Returns
    -------
    str
        L'image convertie.
    """

    with open(filename, "rb") as image_file:
        encoded_string = base64.b64encode(image_file.read())
    return "data:image/jpeg;base64,"+str(encoded_string)[1:].replace("'","")

def CreateMultilingualApp(filename,fileout,source="fr-FR",TargetLanguages=['en-US','de-DE','es-ES'],imagesdir='.',ASPECTRATIO = "16/9",KEEPASPECTRATIO = "Y",FILENAME = "Traduction",TITRE = "Traduction automatique",templatefile="Templates/AppTemplate.txt",delay=0,complementfile="Templates/Complements.txt",licence="", rdm_path=""):
    """Création d'une animation web sous forme de diaporama.
    
    Parameters
    ----------
    filename : str
        Chemin de l'image.
    fileout : str
        Nom du fichier html de l'animation.
    source : str
        Langue d'origine.
    TargetLanguages : str
        Langue de traduction.
    imagesdir : str
        Chemin du dossier contenant les images extraites de la présentation.
    ASPECTRATIO : str
        Ratio de l'animation.
    KEEPASPECTRATIO : str
        Ratio gardé ou non.
    FILENAME : str

    TITRE : str
        Titre de la page web de l'animation.
    templatefile : str
        Template de la page web de l'animation.
    delay : int
        Délai entre chaque slide (en dixième de secondes).
    complementfile : str
        Compléments pour la page web de l'animation.
    licence : str
        Type de licence sur le contenu.
    rdm_path : str
        Nom aléatoire associé à l'action.
    """

    count = 0
    # Gestion des notes
    print("Extracting and translating notes")
    AllNotes=dict()
    Notes = ExtractNotes(filename)
    for note in Notes:
        note["Notes"]=note["Notes"]+([""]*delay)
    AllNotes[source] = Notes
    for l in TargetLanguages:
        translatedNotes=list()
        for s in Notes:
            notes=list()
            for txt in s.get('Notes'):
                translation = txt
                if translation != "":
                    translation =translate(txt,source[0:2].upper(),l[0:2].upper())
                    count =count + len(txt)
                notes.append(translation)
            translatedNotes.append({'Slide': s.get('Slide'), 'Notes': notes})
        AllNotes[l]=translatedNotes
    ConnexionDB(rdm_path, count)
    # Gestion des images
    print("Converting all images in base64")
    AllFilenames=[]
    listfiles = os.listdir(imagesdir)
    for entry in listfiles:
        if (entry.endswith('jpeg') or entry.endswith('jpg')):
            AllFilenames.append(entry)

    AllFilenames.sort()

    # Il ne peut pas y avoir plus de notes que de slides
    lennotes=0
    for l in AllNotes:
        AllNotes[l]=AllNotes[l][0:len(AllFilenames)]
        lennotes=len(AllNotes[l])
    # Il ne peut pas y avoir plus de slides que de notes
    AllFilenames=AllFilenames[0:lennotes]

    AllImages = []
    for f in AllFilenames:
        print("    converting ",f)
        AllImages.append(readbase64(imagesdir+"/"+f))

    if ASPECTRATIO == "auto":
        ASPECTRATIO=AspectRatio(filename)
    LANGUEORIGINE = source
    LANGUETRADUCTION = source
    NBSLIDES = len(AllImages)
    with open(templatefile,"r", encoding='utf8') as f:
        template = "".join(f.readlines())
    template = template.replace("@@LANGUEORIGINE@@",LANGUEORIGINE)
    template = template.replace("@@LANGUETRADUCTION@@",LANGUETRADUCTION)
    template = template.replace("@@ASPECTRATIO@@",ASPECTRATIO)
    template = template.replace("@@NBSLIDES@@",str(NBSLIDES))
    template = template.replace("@@FILENAME@@",FILENAME)
    template = template.replace("@@TITRE@@",TITRE)
    template = template.replace("@@SLIDESNOTES@@",json.dumps(AllNotes))
    with open(complementfile,"r", encoding='utf8') as f:
        complement = "".join(f.readlines())
    if (licence != "" and Licences.LICENCES.get(licence)):
        complement = complement+"\n"+Licences.LICENCES.get(licence)
    template = template.replace("@@COMPLEMENTS@@",complement)
    txtimages = "[];\n";
    for img in AllImages:
        txtimages += "Timages.push('"+img+"');\n";
    template = template.replace("@@IMAGES@@",txtimages)
    f = open(fileout, "w", encoding='utf8')
    f.write(template)
    f.close()

if __name__ == "__main__":
    main()
