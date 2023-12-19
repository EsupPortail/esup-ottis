"""
Cette bibliothèque contient les fonctions utiles pour les utilitaires de traduction.
"""

import sys
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
import config
import json
import requests
import time
import collections
import collections.abc
from pptx import Presentation

# Programmes de traductions

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
    url = config.DEEPLURL
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

    if txt.strip() == "" or txt == "[forward]" or txt == "[backward]" or txt == "[pause]" or txt[0:10] == "[resource " or txt[0:7] == "[mouse " or txt[0:14] == "[notification " or txt[0:6] == "[wait ":
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
    if txt.strip() == "":
        return txt
    if config.LOG:
        with open("tmp/XLIFFTranslations.log","a") as fd:
            fd.write(str(len(txt))+";"+lfrom+";"+lto+";"+txt+"\n")
    config.MAX -= len(txt)
    if config.MAX < 0:
        config.WHICHTRANSLATOR = "FAKE"
    if config.WHICHTRANSLATOR == "FAKE":
        return faketranslate(txt,lfrom,lto)
    elif config.WHICHTRANSLATOR == "DEEPLPRO":
        return translate_deeplpro(txt,lfrom,lto)
    else:
        return translate_google(txt,lfrom,lto)

def faketranslate(txt, lfrom, lto):
    """Fausse traduction pour tester le programme.
    
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
    return lfrom +" to " + lto + " : "+ txt + ""


# Extaction des notes d'un fichier
def ExtractNotes(filename):
    """Extraction des notes pour chaque slide.
    
    Parameters
    ----------
    filename : str
        Fichier PPTX ou TXT dont on extrait les notes.

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
