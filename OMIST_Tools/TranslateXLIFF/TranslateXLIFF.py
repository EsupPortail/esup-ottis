#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Traduction d'un fichier XLIFF.

On obtient en sortie le fichier original et le fichier traduit.

Usage : 
=======
    py TranslateXLIFF.py -i <input_file> -o <output_file> -f <lang_from> -t <lang_to>

    <input_file> : chemin du fichier XLF à traduire
    <output_file> : nom du fichier XLF traduit
    <lang_from> : langue d'origine
    <lang_to> : langue de traduction
"""

import sys
import getopt
from bs4 import BeautifulSoup
# Modif JB (déplacement de import os)
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
import config
import json
import requests
import bs4
import logging
import time

COST_DEEPL = 0.00002 # Cout = 20€ pour 1000000 caractères + forfait de 4.99€
# Modif JB (les deux lignes suivantes ont été commentées, il y a besoin du proxy sur le serveur)
#config.PROXYHTTP = ""
#config.PROXYHTTPS = ""
config.WHICHTRANSLATOR="FAKE" # Uniquement pour les tests
config.DEEPLURL = "https://api.deepl.com/v2/translate"

config.WHICHTRANSLATOR="DEEPLPRO" # Uniquement pour les tests

# pour DEEPLFREE
config.DEEPLAPITOKEN = "***REMOVED***:fx"
config.DEEPLURL = "https://api-free.deepl.com/v2/translate"
config.LOG=True
#config.LOG=False

config.MAX = 1000

def usage():
    """Explication de l'utilisation du programme.
    """

    print("Usage:")
    print("Minimal : ./XLIFF_translator.py --input test.xlf")
    print("Complete : ./XLIFF_translator.py --input test.xlf --output testEN.xlf --from FR --to EN")


# Programme principal XLIFF

def main():
    
    try:
        opts, args = getopt.getopt(sys.argv[1:],"h:i:o:f:t:v",["help","input=","output=","from=","to="])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        usage()
        sys.exit(2)

    Translate = True
    input=""
    output=""
    lfrom="FR"
    lto="EN"
    verbose = False

    for opt, arg in opts:
        if opt == "-v":
            verbose = True
        elif opt in ("-h", "--help"):
            usage()
            sys.exit()
        elif opt in ("-i", "--input"):
            input = arg
        elif opt in ("-o", "--output"):
            output = arg
        elif opt in ("-f", "--from"):
            lfrom = arg
        elif opt in ("-t", "--to"):
            lto = arg
        else:
            assert False, "unhandled option"
    
    if Translate and input != "":
        if output == "":
            output=input.replace(".xlf","_"+lto+".xlf")
        TranslateXLIFF(input,output,lfrom,lto)
    else:
        usage()




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


def TranslateXLIFF(filename,output_filename,lfrom,lto):
    """Traduction d'un fichier XLIFF.
    
    Parameters
    ----------
    filename : str
        Chemin du fichier XLF à traduire.
    output_filename : str
        Nom du fichier XLF traduit.
    lfrom : str
        Langue d'origine.
    lto : str
        Langue de traduction.
    
    """

    ns = bs4.element.NavigableString("")
    count = 0
    dictionary = dict()
    count_saved = 0
    start_timer= time.time()

    # try: 
    #     os.makedirs('output')
    # except OSError:
    #     if not os.path.isdir('output'):
    #         raise

    # Fichier de log
    # if os.path.isfile('log_XLIFF.log'):
    #     os.remove('log_XLIFF.log')
    # logging.basicConfig(encoding='utf-8', level=logging.DEBUG, handlers=[logging.FileHandler("log_XLIFF.log"),logging.StreamHandler(sys.stdout)]) #/!\ écrit même si le fichier est ouvert, il faut le réouvrir pour avoir la màj

    # Traduction
    with open(filename,"r",encoding='utf-8', errors='ignore') as fd:
        content="".join(fd.readlines())
    soup = bs4.BeautifulSoup(content, 'xml')

    # l=soup.find_all("g")
    # for g in l:
    #     gtext_from = g.text

    #     # Vérification de la présence de la traduction dans le dictionnaire
    #     if gtext_from in dictionary:
    #         g.string = dictionary.get(gtext_from)
    #         count_saved = count_saved + len(gtext_from)
    #     else : 
    #         g.string=translate(g.text,lfrom,lto)
    #         dictionary[gtext_from] = g.string
    #         count = count + len(gtext_from)

    l=soup.find_all("trans-unit")
    for g in l:
        for c in g.children:
            # if type(c) == type(ns):
            #     # Vérification de la présence de la traduction dans le dictionnaire en respectant la casse
            #     ctext_from = c.text
            #     if ctext_from in dictionary:
            #         c.replace_with(dictionary.get(ctext_from))
            #         count_saved = count_saved +  len(ctext_from)
            #     else :
            #         c_translated = translate(c.text,lfrom,lto)
            #         c.replace_with(bs4.element.NavigableString(c_translated))
            #         dictionary[ctext_from] = c_translated
            #         count =count + len(ctext_from)
            if str(type(c)) == "<class 'bs4.element.Tag'>" and c.name=="source":
                ctext_from = c.text
                mytag = soup.new_tag("target")
                c_translated = translate(c.text,lfrom,lto)
                mytag.string = c_translated  # translate(c.text,lfrom,lto) (il y avait doublon ici)
                mytag.attrs["xml:lang"]=lto
                g.append(mytag)
                count =count + len(ctext_from)

    # Stockage du dictionnaire dans un fichier
    # with open("dictionary_XLIFF.txt", 'w',encoding="utf-8") as f: 
    #     for key, value in dictionary.items(): 
    #         f.write('%s:%s\n' % (key, value))

    with open(output_filename,"w", encoding="utf-8") as fd:
        fd.write(str(soup.find_all("xliff")[0]))

    end_timer= time.time()
    timer = round(end_timer - start_timer, 5)
    # logging.info("Durée de l'exécution du programme : " + str(timer)+" secondes")
    # logging.info("Nombre de caractères traduits : " + str(count))
    # logging.info("Nombre de caractères économisés : " + str(count_saved))
    # logging.info("Coût de traduction Deepl : " + str(count * COST_DEEPL)+" €")

if __name__ == "__main__":
    main()
