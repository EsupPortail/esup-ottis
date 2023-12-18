#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Traduction d'un fichier PPTX.

On obtient en sortie le fichier traduit au format PPT et PPTX.

Usage : 
=======
    py TranslatePPTX.py -i <input> -o <output> -f <from> -t <to> --WithNotes

    <input> : chemin du fichier PPTX à traduire
    <output> : nom du fichier PowerPoint traduit
    <from> : langue d'origine
    <to> : langue de traduction
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
import config
import requests

def usage():
    """Explication de l'utilisation du programme.
    """

    print("Usage:")
    print("Minimal : ./TranslatePPTX.py --input test.pptx")
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

def TranslatePPTX(filename,fileout,langsrc="FR",langdst="EN",translate_notes=False):
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
                        run.text = " "+translate(run.text,langsrc,langdst)+" "
                        translated_characters+=len(run.text)
    prs.save(fileout)

if __name__ == "__main__":
    main()
