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
import mysql.connector


def Add_to_log(txt,translator):
    with open("/AutomaticTranslator/tmp/translations.log", "a") as myfile:
    myfile.write(translator+" : "+txt)

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
    Add_to_log(txt,"Deeplpro"):
    if (not result.get('translations')):
        return ""
    else:
        return result.get('translations')[0].get('text')


def translate_libretranslate(txt,lfrom,lto):
    """Traduction d'un texte par LibreTranslate.
    
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

    if not lfrom[0:2] in config.LIBRETRANSLATEKNOWNLANG:
        return translate_google(txt,lfrom,lto)
    if not lto[0:2] in config.LIBRETRANSLATEKNOWNLANG:
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
    url = config.LIBRETRANSLATEURL

    datapost={'q':txt, 'source':lfrom, 'target':lto, 'format':'text'}
    if config.PROXYHTTP != "":
        #result = os.popen("HTTPS_PROXY='"+config.HTTPS_PROXY+"' curl "+config.LIBRETRANSLATEURL+" -d \"q='"+txt+"'\" -d \"source="+lfrom+"\" -d \"target="+lto+"\" -d \"format=text\""
        result = requests.post(url,data=datapost,proxies=proxyDict)
    else:
        #result = os.popen("curl "+config.LIBRETRANSLATEURL+" -d \"q='"+txt+"'\" -d \"source="+lfrom+"\" -d \"target="+lto+"\" -d \"format=text\""
        result = requests.post(url,data=datapost)
    result=json.loads(result.text)
    #result=json.loads(result.read())

    #print(result)
    Add_to_log(txt,"LibreTranslate"):
    if (not result.get('translatedText')):
        return ""
    else:
        return result.get('translatedText')


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
    Add_to_log(txt,"Google"):

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
    if lfrom[0:2].lower() == lto[0:2].lower():
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
        deepl_usable = open("../locks/DeeplPro_quota_exceeded.txt", "r")
        deepl_bool = deepl_usable.read()
        deepl_usable.close()
        if deepl_bool == "false":
            return translate_libretranslate(txt,lfrom,lto)
        else:
            return translate_deeplpro(txt,lfrom,lto)
    elif config.WHICHTRANSLATOR == "LIBRETRANSLATE":
        return translate_libretranslate(txt,lfrom,lto)
    elif config.WHICHTRANSLATOR == "GOOGLE":
        #return "GOOGLE TRANSLATOR ("+config.WHICHTRANSLATOR+","+lfrom+","+lto+") : "+txt
        return translate_google(txt,lfrom,lto)
    else:
        return "ERROR TRANSLATOR ("+config.WHICHTRANSLATOR+","+lfrom+","+lto+") : "+txt

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


# Extraction des notes d'un fichier
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

# Extaction des notes d'un fichier
def AspectRatio(filename):
    """Extraction du ratio des slides
    
    Parameters
    ----------
    filename : str
        Fichier PPTX.

    Returns
    -------
    str
        le ratio largeur/hauteur.
    """
    if filename.split(".")[-1] == "pptx":
        prs = Presentation(filename)
        return str(prs.slide_width)+"/"+str(prs.slide_height)
    else:
        return "16/9"

def ConnexionDB(randompath, char_translated):
    """Ajout du nombre de caracrtères traduits dans la base de données de statistiques
    
    Parameters
    ----------
    randompath : str
        Nom aléatoire associé à l'action.
    char_translated : int
        Nombre de caractères traduits.
    """
    return None
    if randompath !="" :
        mydb = mysql.connector.connect(
        user = "root",
        password = "root",
        host = "localhost",
        database = "db_stats")

        mycursor = mydb.cursor()
        mycursor.execute("UPDATE statslog SET char_translated=char_translated + %s WHERE room_id=%s;", (char_translated, randompath, ))
        mydb.commit()