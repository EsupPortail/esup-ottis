#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Création d'une animation web sous forme de diaporama.

On obtient en sortie le fichier html de l'animation.

Usage : 
=======
    py CreateMultiLingualAnim.py -i <input> -o <output> -f <from> -t <to> -d <ImagesDir> --AspectRatio --KeepAspectRatio --Delay --Title --Name --Template

    <input> : chemin du fichier contenant la présentation à animer
    <output> : nom du fichier html de l'animation
    <from> : langue d'origine
    <to> : langue de traduction
    <ImagesDir> : chemin du dossier contenant les images extraites de la présentation
    --AspectRatio : ratio de l'animation
    --KeepAspectRatio : le ratio est gardé
    --Delay : ajout d'un délai entre chaque slide
    --Title : titre de la page web de l'animation
    --Name :
    --Template : template de la page web de l'animation
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
import config

ABSOLUTE_PATH = os.path.dirname(os.path.abspath(__file__))

def usage():
    """Explication de l'utilisation du programme.
    """

    print("Usage:")
    print("Minimal : ./CreateMultiLingualAnim.py --input test.pptx")
    print("Complete : ./CreateMultiLingualAnim.py --input test.pptx --output out.html --to \"EN,DE,IT,HK\" --ImagesDir /tmp/images --AspectRatio --KeepAspectRatio --Delay --Title --Name --Template")

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hoiftd:v", ["help", "output=", "input=","from=","to=","ImagesDir=","AspectRatio=","KeepAspectRatio=","Delay=","Title=","Name=","Template="])
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
    C_ASPECTRATIO = "16/10"
    C_KEEPASPECTRATIO = "Y"
    C_FILENAME = "Traduction"
    C_TITRE = "Automatic Translation"
    C_templatefile=ABSOLUTE_PATH + "/AppTemplate.txt"
    C_DELAY=0
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
        elif o in ("-f", "--from"):
            lfrom = a
        elif o in ("-t", "--to"):
            lto = a
        elif o in ("-d","--ImagesDir"):
            imgdir = a
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
        else:
            assert False, "unhandled option"
    if Translate and input != "":
        if output == "":
            output="out.html"
        CreateMultilingualApp(input,output,imagesdir=imgdir,source=lfrom,TargetLanguages=lto.lower().split(","),ASPECTRATIO=C_ASPECTRATIO,KEEPASPECTRATIO=C_KEEPASPECTRATIO,TITRE=C_TITRE,FILENAME=C_FILENAME,templatefile=C_templatefile,delay=C_DELAY)
    else:
        usage()

def translate_deeplpro(txt,lfrom,lto):
    """Traduction d'un texte par Deepl.
    
    Parameters
    ----------
    txt : str
        Texte à traduire.
    lfrom : str
        Code du langage initial du texte.
    lto : str
        Code du langage dans lequel le texte va être traduit.
    
    Returns
    -------
    str
        Le texte traduit.
    """

    if not lfrom[0:2] in config.DEEPLKNOWNLANG:
        return translate_google(txt,lfrom,lto)
    if not lto[0:2] in config.DEEPLKNOWNLANG:
        return translate_google(txt,lfrom,lto)
    if txt == "[forward]" or txt == "[backward]" or txt == "[pause]" or txt[0:10] == "[resource " or txt[0:7] == "[mouse " or txt[0:14] == "[notification " or txt[0:6] == "[wait ":
        return txt
    lfrom = lfrom[0:2]
    lto = lto[0:2]
    if lfrom == lto:
        return txt
    txt=txt.replace("'","’")
    txt=txt.replace('"',"’’")
    proxyDict = {"http"  : config.PROXYHTTP, "https" : config.PROXYHTTPS}

    url = "https://api.deepl.com/v2/translate"
    datapost={'auth_key':config.DEEPLAPITOKEN, 'text':txt, 'source_lang':lfrom, 'target_lang':lto}
    if config.PROXYHTTP != "":
        result = requests.post(url,data=datapost,proxies=proxyDict)
    else:
        result = requests.post(url,data=datapost)
    result=json.loads(result.text)
    #print(result)
    if (not result.get('translations')):
        return ""
    else:
        return result.get('translations')[0].get('text')

def translate_google(txt,lfrom,lto):
    """Traduction d'un texte par Google.
    
    Parameters
    ----------
    txt : str
        Texte à traduire.
    lfrom : str
        Code du langage initial du texte.
    lto : str
        Code du langage dans lequel le texte va être traduit.
    
    Returns
    -------
    str
        Le texte traduit.
    """

    if txt == "[forward]" or txt == "[backward]" or txt == "[pause]" or txt[0:10] == "[resource " or txt[0:7] == "[mouse " or txt[0:14] == "[notification " or txt[0:6] == "[wait ":
        return txt
    lfrom = lfrom[0:2]
    lto = lto[0:2]
    if lfrom == lto:
        return txt
    txt=txt.replace("'","’")
    txt=txt.replace('"',"’’")
    proxyDict = {"http"  : config.PROXYHTTP, "https" : config.PROXYHTTPS}
    url = "https://translation.googleapis.com/language/translate/v2?key="+config.GOOGLEAPITOKEN
    datapost={'q':txt, 'source':lfrom, 'target':lto, 'format':'text'}
    if config.PROXYHTTP != "":
        result = requests.post(url,data=datapost,proxies=proxyDict)
    else:
        result = requests.post(url,data=datapost)
    result=json.loads(result.text)
    if (not result.get('data')) or (not result.get('data').get('translations')[0]) or (not result.get('data').get('translations')[0].get('translatedText')):
        return ""
    else:
        return result.get('data').get('translations')[0].get('translatedText')

def translate(txt,lfrom,lto):
    """Traduction d'un texte en fonction de la configuration choisie.
    
    Parameters
    ----------
    txt : str
        Texte à traduire.
    lfrom : str
        Code du langage initial du texte.
    lto : str
        Code du langage dans lequel le texte va être traduit.
    
    Returns
    -------
    str
        Le texte traduit.
    """

    if config.WHICHTRANSLATOR == "DEEPLPRO":
        return translate_deeplpro(txt,lfrom,lto)
    else:
        return translate_google(txt,lfrom,lto)

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
        content = open(filename, 'r',encoding='utf-8').read().split("\n")
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

def CreateMultilingualApp(filename,fileout,source="fr-FR",TargetLanguages=['en-US','de-DE','es-ES'],imagesdir='.',ASPECTRATIO = "16/10",KEEPASPECTRATIO = "Y",FILENAME = "Traduction",TITRE = "Traduction automatique",templatefile="AppTemplate.txt",delay=0,complementfile="Complements.txt"):
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
    """

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
                notes.append(translation)
            translatedNotes.append({'Slide': s.get('Slide'), 'Notes': notes})
        AllNotes[l]=translatedNotes
    # Gestion des images
    print("Converting all images in base64")
    AllFilenames=[]
    listfiles = os.listdir(imagesdir)
    for entry in listfiles:
        if (entry.endswith('jpeg') or entry.endswith('jpg')):
            AllFilenames.append(entry)

    AllFilenames.sort()
    AllImages = []
    for f in AllFilenames:
        print("    converting ",f)
        AllImages.append(readbase64(imagesdir+"/"+f))
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
