# Read API Tokens
import json
import random

with open("../tables/tokens.json","r") as fd:
    content=fd.read()
AllTokens=json.loads(content)

GOOGLEAPITOKEN=""
if AllTokens.get("GOOGLETOKENS") and len(AllTokens["GOOGLETOKENS"])>0:
    GOOGLEAPITOKEN=AllTokens["GOOGLETOKENS"][random.randint(0,len(AllTokens["GOOGLETOKENS"])-1)]

DEEPLFREEAPITOKEN = ""
if AllTokens.get("DEEPLFREETOKENS") and len(AllTokens["DEEPLFREETOKENS"])>0:
    DEEPLFREEAPITOKEN=AllTokens["DEEPLFREETOKENS"][random.randint(0,len(AllTokens["DEEPLFREETOKENS"])-1)]

DEEPLAPITOKEN = ""
if AllTokens.get("DEEPLPROTOKENS") and len(AllTokens["DEEPLPROTOKENS"])>0:
    DEEPLAPITOKEN=AllTokens["DEEPLPROTOKENS"][random.randint(0,len(AllTokens["DEEPLPROTOKENS"])-1)]

DEEPLKNOWNLANG=["ar","bg","zh","cs","da","en","fi","fr","de","gr","hu","nl","it","jp","lv","lt","po","pt","ro","ru","sk","sl","es","sv","tr","in"]

LIBRETRANSLATEKNOWNLANG=["en","ar","az","zh","cs","nl","eo","fi","fr","de","el","hi","hu","id","ga","it","ja","ko","fa","pl","pt","ru","sk","es","sv","tr","uk","vi"]


WHICHTRANSLATOR="DEEPLPRO"
#WHICHTRANSLATOR="DEEPLFREE"
#WHICHTRANSLATOR="GOOGLE"
#WHICHTRANSLATOR="LIBRETRANSLATE"
#WHICHTRANSLATOR="FAKE" # Uniquement pour les tests

if AllTokens.get("WHICHTRANSLATOR") and len(AllTokens["WHICHTRANSLATOR"])>0:
    WHICHTRANSLATOR=AllTokens["WHICHTRANSLATOR"][random.randint(0,len(AllTokens["WHICHTRANSLATOR"])-1)]




### Mac OS configuration
#SOFFICE="/Applications/LibreOffice.app/Contents/MacOS/soffice"
#FINDER="open"
#INSTALLDIR="/var/www/html/AutomaticTranslator/OMIST_Tools/tmp"
#CAIRO="/usr/local/bin/pdftocairo"
#PYTHON="/usr/bin/python3"
#PROXYHTTP=""
#PROXYHTTPS=""

### linux misc serveur configuration
SOFFICE="/usr/bin/soffice"
FINDER="ls"
INSTALLDIR="/var/www/html/AutomaticTranslator/OMIST_Tools/tmp"
CAIRO="/usr/bin/pdftocairo"
PYTHON="/usr/bin/python3.9"
PROXYHTTP="http://cache.univ-nantes.fr:3128"
PROXYHTTPS="http://cache.univ-nantes.fr:3128"
#PROXYHTTP=""
#PROXYHTTPS=""

#TRANSLATION_LIMIT=5000
TRANSLATION_LIMIT=500000

if WHICHTRANSLATOR == "DEEPLFREE":
    DEEPLURL="https://api-free.deepl.com/v2/translate"
    DEEPLAPITOKEN=DEEPLFREEAPITOKEN
    WHICHTRANSLATOR = "DEEPLPRO" # pas top...
else:
    DEEPLURL="https://api.deepl.com/v2/translate" # pour avoir le choix entre DeepLPro (notre abonnement) et DeepLFree (limite de 500000 caractères/mois)

# Pour LIBRETRANSLATE
LIBRETRANSLATEURL="https://translate.terraprint.co/translate"

#LOG=True     # Pour les tests, enregistrement de toutes les traductions dans tmp/XLIFFTranductions.log
LOG=False
MAX=500000 # Eventuellement pour limiter l'usage par fichier (bascule vers FAKE au dela du MAX)

#print(WHICHTRANSLATOR,DEEPLURL,DEEPLAPITOKEN)
#print(GOOGLEAPITOKEN)