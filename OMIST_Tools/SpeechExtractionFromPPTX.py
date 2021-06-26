#!/usr/bin/env python
# -*- coding: utf-8 -*-

import getopt, sys
from pptx import Presentation

import subprocess
import json
import base64
import os
import pipes
import config

def usage():
    print("Usage:")
    print("Minimal : ./SpeechExtraction.py --input test.pptx --output speech.txt")

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




def translate(txt,lfrom,lto):
    if txt == "[forward]" or txt == "[backward]" or txt == "[pause]":
        return txt
    lfrom = lfrom[0:2]
    lto = lto[0:2]
    if lfrom == lto:
        return txt
    txt=txt.replace("'","’")
    txt=txt.replace('"',"’’")
    batcmd="curl -s -X POST -H \"Content-Type: application/json\" --data \"{ 'q': '"+txt+"',  'source': '"+lfrom+"',  'target': '"+lto+"',  'format': 'text'}\" \"https://translation.googleapis.com/language/translate/v2?key="+config.GOOGLEAPITOKEN+"\""
    result = subprocess.check_output(batcmd, shell=True)
    result=json.loads(result)
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
    print("DEBUG Filename=",filename)
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
            print("DEBUG Notes",num," : ",notesslide.join(','))
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

def CreateMultilingualApp(filename,fileout,source="fr-FR",TargetLanguages=['en-US','de-DE','es-ES'],imagesdir='.',ASPECTRATIO = "16/10",KEEPASPECTRATIO = "Y",FILENAME = "Traduction",TITRE = "Traduction automatique",templatefile="AppTemplate.txt"):
    # Gestion des notes
    print("Extracting and translating notes")
    AllNotes=dict()
    Notes = ExtractNotes(filename)
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
    with os.scandir(imagesdir) as it:
        for entry in it:
            if (entry.name.endswith('jpeg') or entry.name.endswith('jpg')) and entry.is_file():
                AllFilenames.append(entry.name)
    AllFilenames.sort()
    AllImages = []
    for f in AllFilenames:
        print("    converting ",f)
        AllImages.append(readbase64(f))
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

def PDFtoPPTX(filename,fileout,imagesdir='.',AspectRatio=16/10):
    # Gestion des notes
    print("Extracting notes")
    Notes = ExtractNotes(filename)
    prs = Presentation()
    prs.slide_width=int(AspectRatio*prs.slide_height)
    blank_slide_layout = prs.slide_layouts[6]

    # Gestion des images
    AllFilenames=[]
    with os.scandir(imagesdir) as it:
        for entry in it:
            if (entry.name.endswith('jpeg') or entry.name.endswith('jpg')) and entry.is_file():
                AllFilenames.append(entry.name)
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
    batcmd="/Applications/LibreOffice.app/Contents/MacOS/soffice --headless --convert-to odp "+fileout+".pptx"+" --outdir tmp"
    result = subprocess.check_output(batcmd, shell=True)
    batcmd="/Applications/LibreOffice.app/Contents/MacOS/soffice --headless --convert-to ppt "+fileout+".pptx"+" --outdir tmp ; rm -f "+fileout+".pptx"
    result = subprocess.check_output(batcmd, shell=True)

def SpeechExtract(filename,fileout):
    # Gestion des notes
    print("Extracting notes")
    Notes = ExtractNotes(filename)
    fd=open(fileout,"w")
    for note in Notes:
        fd.write("------------- Slide "+str(note["Slide"])+" -------------\n")
        fd.write("\n".join(note["Notes"])+"\n")
    fd.close()


if __name__ == "__main__":
    main()
