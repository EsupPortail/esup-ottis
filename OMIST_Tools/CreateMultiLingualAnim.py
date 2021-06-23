#!/usr/bin/env python3.5
# -*- coding: utf-8 -*-

import getopt, sys
from pptx import Presentation

import subprocess
import json
import base64
import os
import pipes
import sys
import config

def usage():
    print("Usage:")
    print("Minimal : ./CreateMultiLingualAnim.py --input test.pptx --output out.html")
    print("Complete : ./CreateMultiLingualAnim.py --input test.pptx --output out.html --to \"EN,DE,IT,HK\"")

def main():
    print(sys.getdefaultencoding())
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
    C_templatefile="AppTemplate.txt"
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




def translate(txt,lfrom,lto):
    if txt == "[forward]" or txt == "[backward]" or txt == "[pause]" or txt[0:10] == "[resource ":
        return txt
    lfrom = lfrom[0:2]
    lto = lto[0:2]
    if lfrom == lto:
        return txt
    txt=txt.replace("'"," ")
    txt=txt.replace('"'," ")
    txt=txt.replace('\xe0'," ")

    # AIzaSyDC46tmAnWSDdjAb2SFQFXOMSWSTsW2_eQ
    # batcmd="curl -s -X POST -H \"Content-Type: application/json\" --data \"{ 'q': '"+txt+"',  'source': '"+lfrom+"',  'target': '"+lto+"',  'format': 'text'}\" \"https://translation.googleapis.com/language/translate/v2?key=AIzaSyBPdSCGtTpS1z9Jmo8DwWXdYQfz2Tui8d0\""
    batcmd="curl -s -X POST -H \"Content-Type: application/json\" --data \"{ 'q': '"+txt+"',  'source': '"+lfrom+"',  'target': '"+lto+"',  'format': 'text'}\" \"https://translation.googleapis.com/language/translate/v2?key="+config.GOOGLEAPITOKEN+"\""
    result = subprocess.check_output(batcmd, shell=True)
    result=json.loads(result.decode("utf-8"))
    if (not result.get('data')) or (not result.get('data').get('translations')[0]) or (not result.get('data').get('translations')[0].get('translatedText')):
        return ""
    else:
        return result.get('data').get('translations')[0].get('translatedText')


def ExtractNotes_old(filename):
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
    return notes

def ExtractNotes(filename):
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
        content = open(filename, 'r').read().split("\n")
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



def TranslateNotes(filename,fileout,langsrc="FR",langdst="EN"):
    prs = Presentation(filename)
    for slide in prs.slides:
        for note in slide.notes_slide.notes_text_frame.paragraphs:
            note.text = translate(note.text,langsrc,langdst)
    prs.save(fileout)

def TranslatePPTX(filename,fileout,langsrc="FR",langdst="EN",translate_notes=False):
    prs = Presentation(filename)
    ns=1
    for slide in prs.slides:
        print("Translating slide ",ns)
        ns+=1
        if translate_notes:
            for note in slide.notes_slide.notes_text_frame.paragraphs:
                note.text = translate(note.text,langsrc,langdst)
        for shape in slide.shapes:
            if not shape.has_text_frame:
                continue
            for paragraph in shape.text_frame.paragraphs:
                for run in paragraph.runs:
                    run.text = translate(run.text,langsrc,langdst)
    prs.save(fileout)


def readbase64(filename):
    with open(filename, "rb") as image_file:
        encoded_string = base64.b64encode(image_file.read())
    return "data:image/jpeg;base64,"+str(encoded_string)[1:].replace("'","")

def CreateMultilingualApp(filename,fileout,source="fr-FR",TargetLanguages=['en-US','de-DE','es-ES'],imagesdir='.',ASPECTRATIO = "16/10",KEEPASPECTRATIO = "Y",FILENAME = "Traduction",TITRE = "Traduction automatique",templatefile="AppTemplate.txt",delay=0):
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

    #with os.scandir(imagesdir) as it:
    #    for entry in it:
    #        if (entry.name.endswith('jpeg') or entry.name.endswith('jpg')) and entry.is_file():
    #            AllFilenames.append(entry.name)
    AllFilenames.sort()
    AllImages = []
    for f in AllFilenames:
        print("    converting ",f)
        AllImages.append(readbase64(imagesdir+"/"+f))
    LANGUEORIGINE = source
    LANGUETRADUCTION = source
    NBSLIDES = len(AllImages)
    with open(templatefile) as f:
        template = "".join(f.readlines())
    template = template.replace("@@LANGUEORIGINE@@",LANGUEORIGINE)
    template = template.replace("@@LANGUETRADUCTION@@",LANGUETRADUCTION)
    template = template.replace("@@ASPECTRATIO@@",ASPECTRATIO)
    template = template.replace("@@NBSLIDES@@",str(NBSLIDES))
    template = template.replace("@@FILENAME@@",FILENAME)
    template = template.replace("@@TITRE@@",TITRE)
    template = template.replace("@@SLIDESNOTES@@",json.dumps(AllNotes))
    txtimages = "[];\n";
    for img in AllImages:
        txtimages += "Timages.push('"+img+"');\n";
    template = template.replace("@@IMAGES@@",txtimages)
    f = open(fileout, "w")
    f.write(template)
    f.close()





# In[36]:


# Exemple 1 : Traduction d'une présentation powerpoint de Français à Espagnol,
# en incluant une traduction des notes de l'intervenant

#TranslatePPTX("test.pptx","testES.pptx","FR","ES", translate_notes=True)


# In[38]:


# Exemple 2 : Création d'une application multilingue indépendante (1 unique fichier html complet)
# à partir d'un fichier pptx.
# Les images des slides doivent être générées indépendamment (format et extension .jpeg), elles seront triées par ordre alphabétique
# La liste par défaut de langages cibles peut être modifiée.
#CreateMultilingualApp("test.pptx","out.html",imagesdir='.',TargetLanguages=['en-US','de-DE','es-ES','it-IT','ru-RU','ar-AR','zh-ZH'])


# In[39]:


#TranslatePPTX("/Users/bourdon-j/Downloads/Cours1 L1 MIP 2020-2021(4).pptx","sortie.pptx","FR","AR")

if __name__ == "__main__":
    main()

# In[ ]:





# In[ ]:





# In[ ]:
