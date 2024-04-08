#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Traduction d'un fichier SCORM.

On obtient en sortie les différents éléments du ZIP d'origine. Le fichier HTML traduit se trouve dans le dossier scormcontent.

Usage : 
=======
    py TranslateSCORM.py -i <input_file> -o <output_file> -f <lang_from> -t <lang_to> -u <unzip_path>

    <input_file> : chemin de l'archive ZIP contenant le fichier à traduire
    <output_file> : nom du fichier HTML traduit
    <lang_from> : langue d'origine
    <lang_to> : langue de traduction
    <unzip_path> : chemin où dézipper l'archive
"""

import sys
import os
from bs4 import BeautifulSoup
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
sys.path.append('../')
import config
import json
import requests
import getopt
import shutil
import zipfile
import base64
import logging
import time
from utils import translate

COST_DEEPL = 0.00002
config.PROXYHTTP=""
config.PROXYHTTPS="" 

def usage():
    """Explication de l'utilisation du programme.
    """

    print("Usage:")
    print("Minimal : ./TranslateSCORM.py --input test.zip")
    print("Complete : ./TranslateSCORM.py --input test.zip --output testEN.html --from FR --to EN")

def main():

    try:
        opts, args = getopt.getopt(sys.argv[1:],"h:i:o:f:t:u:v",["help","input=","output=","from=","to=","unzip_path="])
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
        elif opt in ("-u", "--unzip_path"):
            unzip_dest = arg
        else:
            assert False, "unhandled option"
    
    if Translate and input != "":
        if output == "":
            output=input.replace(".zip","_"+lto+".html")
        TranslateSCORM(input,output,lfrom,lto, unzip_dest)
    else:
        usage()

def TranslateHtmlText(content,lfrom="fr",lto="en"):
    """Traduction d'un texte HTML.
    
    Parameters
    ----------
    content : str
        Texte à traduire.
    lfrom : str
        Code du langage initial du texte. Par défaut à "fr".
    lto : str
        Code du langage dans lequel le texte va être traduit. Par défaut à "en".
    
    Returns
    -------
    str
        Le texte traduit.
    int
        Le nombre de caractères traduits.
    """

    soup = BeautifulSoup(content, 'html.parser')
    count_tmp = len(soup.text)
    return translate(soup.text,lfrom,lto), count_tmp

def Recursive(root, dictionary, count=0, count_saved = 0, where="/",lfrom="fr",lto="en"):
    """Fonction récursive pour traduire un texte HTML.
    
    Parameters
    ----------
    root : dict ou list
        Texte à traduire avec les balises.
    dictionary : dict
        Dictionaire qui répertorie les phrases traduites.
    count : int
        Compteur de caractères traduits. Par défaut à 0.
    count_saved : int
        Compteur de caractères réutilisés. Par défaut à 0.
    where : str
        Emplacement dans les balises. Par défaut à "/".
    lfrom : str
        Code du langage initial du texte. Par défaut à "fr".
    lto : str
        Code du langage dans lequel le texte va être traduit. Par défaut à "en".
    
    Returns
    -------
    int
        Le nombre de caractères traduits.
    int
        Le nombre de caractères réutilisés.
    dict
        Dictionaire qui répertorie les phrases traduites.
    """


    if type(root) == dict:
        for term in ["title", "description", "paragraph", "caption", "heading"]:
            if root.get(term):
                text_from = BeautifulSoup(root[term], 'html.parser').text
                if text_from in dictionary:
                    root[term] = dictionary.get(text_from)
                    count_saved = count_saved + len(text_from)
                else:
                    root[term], count_translated =TranslateHtmlText(root[term],lfrom=lfrom,lto=lto)
                    dictionary[text_from] = root[term]
                    count  = count + count_translated

        for k in root:
            if (root.get(k)):
                count, count_saved, dictionary = Recursive(root=root.get(k),dictionary=dictionary, count=count,count_saved =count_saved, where=where+k+'/',lfrom=lfrom,lto=lto)

    else:
        if type(root) == list:
            for k in range(len(root)):
                count, count_saved, dictionary = Recursive(root=root[k],dictionary=dictionary, count=count, count_saved =count_saved, where=where,lfrom=lfrom,lto=lto)

    return count, count_saved, dictionary


def TranslateSCORM(filename,output_filename,lfrom,lto, unzip_dest):
    """Programme pour traduire un fichier SCORM 1.2.
    
    Parameters
    ----------
    filename : str
        Chemin de l'archive ZIP contenant le fichier à traduire.
    output_filename : str
        Nom du fichier HTML traduit.
    lfrom : str
        Langue d'origine.
    lto : str
        Langue de traduction.
    
    """

    dictionary = dict()
    count_label = 0
    count_saved_label = 0

    start_timer= time.time()
    # try: 
    #     os.makedirs('output')
    # except OSError:
    #     if not os.path.isdir('output'):
    #         raise

    # Fichier de log
    # if os.path.isfile('output/log_SCORM12.log'):
    #     os.remove('output/log_SCORM12.log')
    # logging.basicConfig(encoding='utf-8', level=logging.INFO, handlers=[logging.FileHandler("output/log_SCORM12.log"),logging.StreamHandler(sys.stdout)]) #/!\ écrit même si le fichier est ouvert, il faut le réouvrir pour avoir la màj


    # Décompression du zip dans le répertoire tmp
    # if os.path.isdir('tmp_zip'):
    #     shutil.rmtree('tmp_zip')

    # try: 
    #     os.makedirs('tmp_zip')
    # except OSError:
    #     if not os.path.isdir('tmp_zip'):
    #         raise

    with zipfile.ZipFile(filename, 'r') as zip_ref:
        zip_ref.extractall(unzip_dest)


    # Lecture du fichier copié
    src_original = unzip_dest+"/scormcontent/index.html"
    src = unzip_dest+"/scormcontent/original.html"
    os.rename(src_original, src)
    dest = output_filename
    shutil.copy2(src, dest)
    

    with open(dest,"r") as fd:
        contentindex="".join(fd.readlines())

    nonb64content_begin=contentindex[:contentindex.find("window.courseData = \"")+len("window.courseData = \"")]
    b64content=contentindex[contentindex.find("window.courseData = \"")+len("window.courseData = \""):]

    nonb64content_end=b64content[b64content.find("\""):]
    b64content=b64content[:b64content.find("\"")]

    content=json.loads(base64.b64decode(b64content).decode("utf-8"))
    
    count_recursive, count_saved_recursive, dictionary = Recursive(root=content,dictionary=dictionary,count=0, count_saved =0,lfrom=lfrom,lto=lto)

    json_object = json.dumps(content, indent=4)

    b64content=base64.b64encode(json_object.encode('ascii')).decode('ascii')


    # Traductions des termes de navigation
    oldcontentindex=contentindex
    contentindex=nonb64content_begin+b64content+nonb64content_end

    nonb64content_begin_terms=contentindex[:contentindex.find("window.labelSet = ")+len("window.labelSet = ")]
    b64content_terms=contentindex[contentindex.find("window.labelSet = ")+len("window.labelSet = "):]
    nonb64content_end_terms=b64content_terms[b64content_terms.find("window.courseData =")-1:]
    b64content_terms=b64content_terms[:b64content_terms.find("window.courseData =")-1].strip().replace("};","}")
    content_terms=json.loads(b64content_terms)

    for l in content_terms["labels"]:
        ltext_from = content_terms["labels"][l]
        if ltext_from in dictionary:
            content_terms["labels"][l]=dictionary.get(ltext_from)
            count_saved_label = count_saved_label + len(ltext_from)
        else:    
            content_terms["labels"][l]=translate(content_terms["labels"][l],lfrom,lto)
            dictionary[ltext_from] = content_terms["labels"][l]
            count_label = count_label + len(ltext_from) # ici les caractères spéciaux (ex : é) comptent comme 2 caractères

    
    end_timer= time.time()
    timer = round(end_timer - start_timer, 5)
    count_tot = count_label + count_recursive
    count_tot_saved = count_saved_label + count_saved_recursive
    logging.info("Durée de l'exécution du programme : " + str(timer)+" secondes")
    logging.info("Nombre de caractères traduits : " + str(count_tot))
    logging.info("Nombre de caractères économisés : " + str(count_tot_saved))
    logging.info("Coût de traduction Deepl : " + str(count_tot * COST_DEEPL)+" €")


    json_object_terms=json.dumps(content_terms, indent=4)

    translatedcontent=nonb64content_begin_terms+json_object_terms+"; \n"+nonb64content_end_terms

    # Stockage du dictionnaire dans un fichier
    # with open("output/dictionary_SCORM.txt", 'w',encoding="utf-8") as f: 
    #     for key, value in dictionary.items(): 
    #         f.write('%s:%s\n' % (key, value))

    with open(dest,"w") as fd:
        fd.write(translatedcontent)


if __name__ == "__main__":
    main()
