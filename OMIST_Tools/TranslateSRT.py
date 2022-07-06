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
    print("Complete : ./TranslateSRT.py --input test.pptx --output testEN.pptx --from fr --to en")

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hoift:v", ["help", "output=", "input=","from=","to="])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        usage()
        sys.exit(2)
    input=""
    output=""
    lfrom="fr"
    lto="en"
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
        else:
            assert False, "unhandled option"
    if Translate and input != "":
        if output == "":
            output=input.replace(".pptx","_"+lto+".pptx")
        TranslateSRT(input,output,lfrom,lto)
    else:
        usage()




def translate_old(txt,lfrom,lto):
    if txt == "[forward]" or txt == "[backward]" or txt == "[pause]" or txt[0:10] == "[resource ":
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
    if txt == "[forward]" or txt == "[backward]" or txt == "[pause]" or txt[0:10] == "[resource ":
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
    if txt == "[forward]" or txt == "[backward]" or txt == "[pause]" or txt[0:10] == "[resource ":
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

def isphrase(ch):
    cend=ch[len(ch)-1]
    return (cend == "." or cend == "!" or cend == "!")

def formatetime(t):
  res=0
  Tt = t.split(",")
  [h,m,s]=Tt[0].split(":")
  ms = Tt[1];
  return float(ms)+float(s)*1000+float(m)*60*1000+float(h)*3600*1000;


def TranslateSRT(filename,filename_out,in_language,out_language):
    fd=open(filename,"r")
    content = fd.readlines()
    fd.close()
    TSubtitles=("|".join(content).strip()).split("\n|\n|")
    Subtitles=list()
    for s in TSubtitles:
        s=s.replace("|","").strip()
        if not s == "":
            ts = s.split('\n')
            snumber=ts[0]
            stime=ts[1]
            begin = stime.split("-->")[0].strip()
            end = stime.split("-->")[1].strip()
            stxt=" ".join(ts[2:]).replace("  "," ").strip()
            Subtitles.append({"begin":begin, "end":end, "VO":stxt})
    NewSubtitles=list()
    currenttxt=""
    begin=""
    end=""
    for s in Subtitles:
        if begin == "":
            begin=s.get("begin")
        currenttxt+=" "+s.get("VO")
        end=s.get("end")
        if isphrase(currenttxt):
            translatedtext = translate(currenttxt,in_language,out_language)
            NewSubtitles.append({"begin":begin, "end":end, "phrase": translatedtext, "VO":currenttxt})
            currenttxt=""
            begin=""
            end=""
    Subtitles=NewSubtitles
    fd=open(filename_out,"w")
    for i in range(len(Subtitles)):
        if (Subtitles[i].get("phrase")):
            fd.write(str(i+1)+"\n")
            fd.write(Subtitles[i].get("begin")+" --> "+Subtitles[i].get("end")+"\n")
            fd.write(Subtitles[i].get("phrase")+"\n\n")
    fd.close()
