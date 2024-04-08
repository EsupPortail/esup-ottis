#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys
import getopt
import shutil
import json
import hashlib
from urllib.request import urlopen
import urllib
from bs4 import BeautifulSoup
import os
from pptx import Presentation

def main():

    try:
        opts, args = getopt.getopt(sys.argv[1:],"h:i:o",["help","input=","output="])
    except getopt.GetoptError as err:
        # print help information and exit:
        print(err)  # will print something like "option -a not recognized"
        sys.exit(2)
    
    input=""
    output=""
    verbose = False

    for opt, arg in opts:
        if opt == "-v":
            verbose = True
            sys.exit()
        elif opt in ("-i", "--input"):
            input = arg
        elif opt in ("-o", "--output"):
            output = arg
        else:
            assert False, "unhandled option"
    
    if input != "":
        CreatePPTX(input,output)

def CreatePPTX():
    #Create blank PowerPoint file
    prs = Presentation()

    #set slide template labels. This just makes is easier to read the code. The layout index can also be used.                   
    SLD_LAYOUT_TITLE = 0
    SLD_LAYOUT_TITLE_AND_CONTENT = 1
    SLD_LAYOUT_TITLE_ONLY = 5

    for image_slide in image_tot :
        #create a dictionary of file names and the location they will be placed on slide
        shape_location = {image_slide: {"top": Inches(1.92), "left": Inches(1.88)}}

        #Add a slide
        slide_layout = prs.slide_layouts[SLD_LAYOUT_TITLE_ONLY]
        slide = prs.slides.add_slide(slide_layout)

        #Add slide images
        for image, location in shape_location.items():
            #insert images into slide at the specified location. This will place a single image in the center of the slide.
            slide.shapes.add_picture(image, location['left'], location['top'])


def GetHTMLFile(input, output):
    shutil.copy2(input, output)

def CopyHTMLFile(input, output):

    HTMLFile = open(input, "r") 
    index = HTMLFile.read() 
    
    soup = BeautifulSoup(index, 'html.parser')
    print(type(soup))
    html = soup.decode("utf-8")
    print(type(html))
    with open(output, "w") as fd:
        fd.write(html)


#Traduction du fichier PHP
def ExtractSlidesNotesHTML(input):

    os.environ["upload_max_filesize"] = "50M"
    os.environ["post_max_size"] = "50M"


    tmp_file = input
    # tmp_file = request.FILES['ppt_presentation']['tmp_name']
    flag_justif = False
    filename = "tmpfiles/test.html"

    # contenttxt= urllib.request.urlopen(tmp_file)
    with open(tmp_file, "r") as file:
        contenttxt = file.read()
    content = contenttxt.split("\n")
    txtslidenotes=""
    T_images = []

    for i in range(len(content)):
        txt = content[i]
        if ("_"+txt.find("var slidesnotes = ")>0):
            txtslidenotes = txt.replace("var slidesnotes = ","")
            txtslidenotes = txtslidenotes[:len(txtslidenotes)-1]
        if ("_"+txt.find("Timages.push(")>0):
            txtimage = txt.replace("Timages.push(","")
            txtimage = txtimage.replace("');","")
            T_images.append(txtimage)
    
    Tnotes = list(json.loads(txtslidenotes))
    notes = []

    for lang, noteslang in Tnotes.items():
        notes = noteslang
        break

    n = len(notes)
    T = []
    T["images"] = T_images
    T["Slide"] = 0
    T["Notes"] = []
    notes.append(T)

    json_object = json.dumps(notes, indent=4)

if __name__ == "__main__":
    main()