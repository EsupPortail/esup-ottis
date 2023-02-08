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
import requests

def usage():
    print("Usage:")
    print("Minimal : ./TranslatePPTX.py --input test.pptx --to EN")
    print("Complete : ./TranslatePPTX.py --input test.pptx --output testEN.pptx --from FR --to EN --WithNotes")

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hoift:vW", ["help", "output=", "input=","from=","to=","WithNotes"])
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
        elif o in ("-W","--WithNotes"):
            WithNotes = True
        else:
            assert False, "unhandled option"
    if Translate and input != "":
        if output == "":
            output=input.replace(".pptx","_"+lto+".pptx")
        TranslatePPTX(input,output,lfrom,lto,translate_notes=WithNotes)
    else:
        usage()




def translate_old(txt,lfrom,lto):
    if txt == "[forward]" or txt == "[backward]" or txt == "[pause]" or txt[0:10] == "[resource " or txt[0:7] == "[mouse " or txt[0:14] == "[notification " or txt[0:6] == "[wait ":
        return txt
    lfrom = lfrom[0:2]
    lto = lto[0:2]
    if lfrom == lto:
        return txt
    txt=txt.replace("'","’")
    txt=txt.replace('"',"’’")
    """
    txt=txt.replace("'"," ")
    txt=txt.replace('"'," ")
    txt=txt.replace('\xe0'," ")
    txt=txt.replace('\xe9'," ")
    txt=txt.replace('\u2019'," ")
    """
    # AIzaSyDC46tmAnWSDdjAb2SFQFXOMSWSTsW2_eQ
    # batcmd="curl -s -X POST -H \"Content-Type: application/json\" --data \"{ 'q': '"+txt+"',  'source': '"+lfrom+"',  'target': '"+lto+"',  'format': 'text'}\" \"https://translation.googleapis.com/language/translate/v2?key=AIzaSyBPdSCGtTpS1z9Jmo8DwWXdYQfz2Tui8d0\""
    batcmd="curl -s -X POST -H \"Content-Type: application/json\" --data \"{ 'q': '"+txt+"',  'source': '"+lfrom+"',  'target': '"+lto+"',  'format': 'text'}\" \"https://translation.googleapis.com/language/translate/v2?key="+config.GOOGLEAPITOKEN+"\""
    batcmd = batcmd.encode('utf-8','ignore')
    result = subprocess.check_output(batcmd, shell=True, encoding='utf-8')
    result=json.loads(result.decode("utf-8"))
    if (not result.get('data')) or (not result.get('data').get('translations')[0]) or (not result.get('data').get('translations')[0].get('translatedText')):
        return ""
    else:
        return result.get('data').get('translations')[0].get('translatedText')

def translate_deeplpro(txt,lfrom,lto):
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
    if config.WHICHTRANSLATOR == "DEEPLPRO":
        return translate_deeplpro(txt,lfrom,lto)
    else:
        return translate_google(txt,lfrom,lto)

def ExtractNotes(filename):
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

def TranslateNotes(filename,fileout,langsrc="FR",langdst="EN"):
    prs = Presentation(filename)
    for slide in prs.slides:
        for note in slide.notes_slide.notes_text_frame.paragraphs:
            note.text = translate(note.text,langsrc,langdst)
    prs.save(fileout)

def TranslatePPTX(filename,fileout,langsrc="FR",langdst="EN",translate_notes=False):
    translated_characters=0
    prs = Presentation(filename)
    ns=1
    for slide in prs.slides:
        print("Translating slide ",ns)
        ns+=1
        if translate_notes:
            for note in slide.notes_slide.notes_text_frame.paragraphs:
                translated = translate(note.text,langsrc,langdst)
                #if note.text[0]==" ":
                #    translated=" "+translated
                #
                note.text = translated+" "
        for shape in slide.shapes:
            if not shape.has_text_frame:
                continue
            for paragraph in shape.text_frame.paragraphs:
                for run in paragraph.runs:
                    if (translated_characters<config.TRANSLATION_LIMIT):
                        run.text = " "+translate(run.text,langsrc,langdst)+" "
                        translated_characters+=len(run.text)
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
            if entry.name.endswith('jpeg') and entry.is_file():
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




if __name__ == "__main__":
    main()
