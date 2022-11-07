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
import math

def usage():
    print("Usage:")
    print("Complete : ./TranslateSRT.py --input test.pptx --output testEN.pptx --from fr --to en")

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hoift:vw", ["help", "output=", "input=","from=","to=","wraplength="])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        usage()
        sys.exit(2)
    input=""
    output=""
    lfrom="fr"
    lto="en"
    wraplength=50
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
        elif o in ("-w", "--wraplength"):
            wraplength = a
        else:
            assert False, "unhandled option"
    if input != "":
        if output == "":
            output=input.replace(".srt","_"+lto+".srt")
        #print("TranslateSRT ",input,output,lfrom,lto)
        TranslateSRT(input,output,lfrom[0:2],lto[0:2])
        Verbatim(output)
        WrapSubtitle(output, wraplength)
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
        return "TRANSLATION PROBLEM"
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

def prettytime(n):
    timems = math.floor(n)
    ms = timems % 1000
    sec = math.floor(timems/1000)%60
    min = math.floor(timems/1000/60)%60
    h= math.floor(timems/1000/60/60)
    return str(100+h)[1:]+":"+str(100+min)[1:]+":"+str(100+sec)[1:]+","+str(1000+ms)[1:]


def TranslateSRT(filename,filename_out,in_language,out_language):
    fd=open(filename,"r", encoding="utf-8")
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
    fd=open(filename_out,"w", encoding="utf-8")
    for i in range(len(Subtitles)):
        if (Subtitles[i].get("phrase")):
            fd.write(str(i+1)+"\n")
            fd.write(Subtitles[i].get("begin")+" --> "+Subtitles[i].get("end")+"\n")
            fd.write(Subtitles[i].get("phrase")+"\n\n")
    fd.close()

def Verbatim(filename):
    fd=open(filename,"r", encoding="utf-8")
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
    fd=open(filename.replace(".srt","_verbatim.txt"),"w", encoding="utf-8")
    for i in range(len(Subtitles)):
        if (Subtitles[i].get("VO")):
            fd.write(Subtitles[i].get("VO")+"\n")
    fd.close()


def WrapSubtitle(filename, wraplength=50):
    fd=open(filename,"r", encoding="utf-8")
    content = fd.readlines()
    fd.close()
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
                begin=currenttime.split("-->")[0].strip()
                end=currenttime.split("-->")[1].strip()
                begin_ms=formatetime(begin)
                end_ms=formatetime(end)
                Subtitles.append({"begin":currenttime, "phrase":translatedtext, "VO":currenttext})
                nbcar=0
                cumultxt=""
                currentbegin_ms=begin_ms
                for k in range(len(translatedtext)):
                    c=translatedtext[k]
                    cumultxt+=c
                    if (c == " " and len(cumultxt)>=wraplength):
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
