#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Traduction d'un fichier SRTT.

On obtient en sortie le fichier original, le fichier traduit, l'original et la
traduction sans les timecodes, et la traduction avec les sous-titres d'une longueur définie.

Usage : 
=======
    py TranslateSRT.py -i <input_file> -o <output_file> -f <lang_from> -t <lang_to> -w <wraplength> -s <sentencesthreshold> -p <nopunct> -r <randompath>

    <input_file> : chemin du fichier SRT à traduire
    <output_file> : nom fichier SRT traduit
    <from> : langue d'origine
    <to> : langue de traduction
    <wraplength> : longueur maximale des sous-titres voulue (en nombre de caractères)
    <sentencesthreshold> : 
    <nopunct> : True si pas de ponctuation dans les sous-titres
    <randompath> : nom aléatoire associé à l'action
"""

import getopt, sys
import subprocess
import json
import base64
import os
import pipes
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
import config
import requests
import math
from utils import translate
from utils import ConnexionDB

def usage():
    """Explication de l'utilisation du programme.
    """
    
    print("Usage:")
    print("Minimal : ./TranslateSRT.py --input test.srt")
    print("Complete : ./TranslateSRT.py --input test.srt --output testEN.srt --from fr --to en --wraplength 80 --sentencesthreshold --randompath \"abc\" --nopunct")

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hoiftpsr:vw", ["help", "output=", "input=","from=","to=","wraplength=","sentencesthreshold=", "randompath=", "nopunct"])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        usage()
        sys.exit(2)
    inputfile=""
    output=""
    lfrom="fr"
    lto="en"
    wraplength=80
    sentencesThreshold=2000
    verbose = False
    nopun = False
    rdmpath = ""
    for o, a in opts:
        if o == "-v":
            verbose = True
        elif o in ("-h", "--help"):
            usage()
            sys.exit()
        elif o in ("-i", "--input"):
            inputfile = a
        elif o in ("-o", "--output"):
            output = a
        elif o in ("-f", "--from"):
            lfrom = a
        elif o in ("-t", "--to"):
            lto = a
        elif o in ("-w", "--wraplength"):
            wraplength = a
        elif o in ("-p", "--nopunct"):
            nopun = True
        elif o in ("-s", "--sentencesthreshold"):
            sentencesThreshold = int(a)  
        elif o in ("-r", "--randompath"):
            rdmpath = a        
        else:
            assert False, "unhandled option"
    if inputfile != "":
        if output == "":
            output=inputfile.replace(".srt","_"+lto+".srt")

        TranslateSRT(inputfile,output,lfrom[0:2],lto[0:2],rdm_path=rdmpath, nopunct=nopun,sentencesThreshold=sentencesThreshold)
        Verbatim(inputfile)
        Verbatim(output)
        WrapSubtitle(output, wraplength)
    else:
        usage()

def isphrase(ch):
    """Détermine si la chaîne de caractères est une phrase.
    
    Parameters
    ----------
    ch : str
        Chaîne de caractères à tester.
    
    Returns
    -------
    boolean
        Si la chaîne de caractères est une phrase ou non.
    """
    cend=ch[len(ch)-1]
    return (cend == "." or cend == "!" or cend == "?")

def formatetime(t):
    """Conversion du format du chronomètre depuis un fichier SRT.
    
    Parameters
    ----------
    t : str
        Chronomètre dans le fichier SRT.
    
    Returns
    -------
    float
        Chronomètre converti en float.
    """

    res=0
    Tt = t.split(",")
    [h,m,s]=Tt[0].split(":")
    ms = Tt[1];
    return float(ms)+float(s)*1000+float(m)*60*1000+float(h)*3600*1000;

def duration(time1,time2):
    """Calcul de la durée entre deux chronomètres.
    
    Parameters
    ----------
    time1 : str
        Chronomètre dans le fichier SRT.
    
    time2 : str
        Chronomètre dans le fichier SRT.

    Returns
    -------
    float
        Durée entre les deux chronomètres.
    """

    return abs(formatetime(time2)-formatetime(time1))

def prettytime(n):
    """Conversion du format du chronomètre pour un fichier SRT.
    
    Parameters
    ----------
    n : 
    
    Returns
    -------
    str
        Chronomètre pour un fichier SRT.
    """

    timems = math.floor(n)
    ms = timems % 1000
    sec = math.floor(timems/1000)%60
    min = math.floor(timems/1000/60)%60
    h= math.floor(timems/1000/60/60)
    return str(100+h)[1:]+":"+str(100+min)[1:]+":"+str(100+sec)[1:]+","+str(1000+ms)[1:]


def TranslateSRT(filename,filename_out,in_language,out_language,rdm_path, nopunct=False, sentencesThreshold=2000):
    """Traduction d'un fichier SRT.
    
    Parameters
    ----------
    filename : str
        Chemin du fichier SRT à traduire.
    output_filename : str
        Nom du fichier SRT traduit.
    in_language : str
        Langue d'origine.
    out_language : str
        Langue de traduction.
    rdm_path : str
        Nom aléatoire associé à l'action.
    nopunct : boolean
        Pas de ponctuation dans les sous-titres.
    sentencesThreshold : int

    
    """
    count = 0
    fd=open(filename,"r", encoding="utf-8")
    content = fd.readlines()
    fd.close()
    # VTT -> SRT conversion
    if('WEBVTT' in content[0]):
        content=content[2:]
    TSubtitles=("|".join(content).strip()).split("\n|\n|")
    Subtitles=list()
    for s in TSubtitles:
        s=s.replace("|","").strip()
        if not s == "":
            ts = s.split('\n')
            snumber=ts[0]
            stime=ts[1]
            begin = stime.split("-->")[0].strip().replace(".",",")
            end = stime.split("-->")[1].strip().replace(".",",")
            stxt=" ".join(ts[2:]).replace("  "," ").strip()
            Subtitles.append({"begin":begin, "end":end, "VO":stxt})
    NewSubtitles=list()
    currenttxt=""
    begin=""
    end=""
    for i in range(len(Subtitles)):
        s=Subtitles[i]
        if begin == "":
            begin=s.get("begin")
        currenttxt+=" "+s.get("VO")
        end=s.get("end")
        if (filename.endswith('srt')):
            futureend="99:99:99,999"
        else:
            futureend="99:99:99.999"
        if i<len(Subtitles)-1:
            futureend=Subtitles[i+1].get("end")
        if nopunct or isphrase(currenttxt) or ( not(currenttxt == "") and duration(futureend,end)>sentencesThreshold):
            translatedtext = translate(currenttxt,in_language,out_language)
            count =count + len(currenttxt)
            NewSubtitles.append({"begin":begin, "end":end, "phrase": translatedtext, "VO":currenttxt})
            currenttxt=""
            begin=""
            end=""
    Subtitles=NewSubtitles
    ConnexionDB(rdm_path, count)
    #print(Subtitles)
    fd=open(filename_out,"w", encoding="utf-8")
    for i in range(len(Subtitles)):
        if (Subtitles[i].get("phrase")):
            fd.write(str(i+1)+"\n")
            fd.write(Subtitles[i].get("begin")+" --> "+Subtitles[i].get("end")+"\n")
            fd.write(Subtitles[i].get("phrase")+"\n\n")
    fd.close()

    # output_filename_txt = filename_out.replace(".srt", "TEST_verbatim.txt")

    # with open(output_filename_txt,"w", encoding="utf-8") as fd_txt:
    #     fd_txt.write("Number of input characters : "+str(count))
    # fd_txt.close()

def Verbatim(filename):
    """Conversion d'un fichier SRT en fichier TXT sans les timecodes.
    
    Parameters
    ----------
    filename : str
        Chemin du fichier SRT à convertir.    
    """

    fd=open(filename,"r", encoding="utf-8")
    content = fd.readlines()
    fd.close()
    # VTT -> SRT conversion
    if('WEBVTT' in content[0]):
        content=content[2:]
    TSubtitles=("|".join(content).strip()).split("\n|\n|")
    Subtitles=list()
    for s in TSubtitles:
        s=s.replace("|","").strip()
        if not s == "":
            ts = s.split('\n')
            snumber=ts[0]
            stime=ts[1]
            begin = stime.split("-->")[0].strip().replace(".",",")
            end = stime.split("-->")[1].strip().replace(".",",")
            stxt=" ".join(ts[2:]).replace("  "," ").strip()
            Subtitles.append({"begin":begin, "end":end, "VO":stxt})
    fd=open(filename.replace(".srt","_verbatim.txt"),"w", encoding="utf-8")
    for i in range(len(Subtitles)):
        if (Subtitles[i].get("VO")):
            fd.write(Subtitles[i].get("VO")+"\n")
    fd.close()


def WrapSubtitle(filename, wraplength=80):
    """Découpage des sous-titres d'un fichier SRT.
    
    Parameters
    ----------
    filename : str
        Chemin du fichier SRT découper.
    wraplength : int
        Longueur des sous-titres désirée (en nombre de caractères).
    """

    fd=open(filename,"r", encoding="utf-8")
    content = fd.readlines()
    fd.close()
    # VTT -> SRT conversion
    if('WEBVTT' in content[0]):
        content=content[2:]
    i=0
    content.append("\n")
    Subtitles=list()
    Subtitles_cutted=list()
    currenttime=""
    currenttext=""
    for c in content:
        if (i == 1):
            currenttime=c.replace("\n","")#.split(" --> ")[0]
        elif c == "\n":
            if not currenttext == "":
                translatedtext = currenttext
                begin=currenttime.split("-->")[0].strip().replace(".",",")
                end=currenttime.split("-->")[1].strip().replace(".",",")
                begin_ms=formatetime(begin)
                end_ms=formatetime(end)
                Subtitles.append({"begin":currenttime, "phrase":translatedtext, "VO":currenttext})
                nbcar=0
                cumultxt=""
                currentbegin_ms=begin_ms
                for k in range(len(translatedtext)):
                    c=translatedtext[k]
                    cumultxt+=c
                    if (c == " " and len(cumultxt)>=int(wraplength)):
                        currentend_ms=k/len(translatedtext)*(end_ms-begin_ms)+begin_ms
                        Subtitles_cutted.append({"begin":currentbegin_ms, "end":currentend_ms, "phrase":cumultxt})
                        cumultxt = ""
                        currentbegin_ms=currentend_ms+100
                Subtitles_cutted.append({"begin":currentbegin_ms, "end":end_ms, "phrase":cumultxt})

            i=-1
            currenttext=""
        elif not i == 0:
            currenttext+=c.replace("\n"," ")
        i+=1
    sub=Subtitles_cutted
    fd=open(filename.replace(".srt","_wrapped.srt"),"w", encoding="utf-8")
    for i in range(len(sub)):
        if (sub[i].get("phrase")):
            fd.write(str(i+1)+"\n")
            fd.write(prettytime(sub[i].get("begin"))+" --> "+prettytime(sub[i].get("end"))+"\n")
            fd.write(sub[i].get("phrase")+"\n\n")
    fd.close()


if __name__ == "__main__":
    main()
