<?php

// Get Tokens from tables/tokens.json 

$content = file_get_contents("tables/tokens.json");
//$AllTokens = (array) json_decode(file_get_contents("tables/tokens.json"));
$AllTokens=json_decode($content,true);

$GOOGLEAPITOKEN="";
if (isset($AllTokens["GOOGLETOKENS"]) && count($AllTokens["GOOGLETOKENS"])>0) $GOOGLEAPITOKEN=$AllTokens["GOOGLETOKENS"][(gmdate("s")*1)%count($AllTokens["GOOGLETOKENS"])];

$DEEPLFREEAPITOKEN = "";
if (isset($AllTokens["DEEPLFREETOKENS"]) && count($AllTokens["DEEPLFREETOKENS"])>0) $DEEPLFREEAPITOKEN=$AllTokens["DEEPLFREETOKENS"][(gmdate("s")*1)%count($AllTokens["DEEPLFREETOKENS"])];

$DEEPLAPITOKEN = "";
if (isset($AllTokens["DEEPLPROTOKENS"]) && count($AllTokens["DEEPLPROTOKENS"])>0) $DEEPLAPITOKEN=$AllTokens["DEEPLPROTOKENS"][(gmdate("s")*1)%count($AllTokens["DEEPLPROTOKENS"])];

$DEEPLKNOWNLANG = ["ar","bg","zh","cs","da","en","fi","fr","de","gr","hu","nl","it","jp","lv","lt","po","pt","ro","ru","sk","sl","es","sv","tr","in","uk"];
$LIBRETRANSLATEKNOWNLANG = ["en","ar","az","zh","cs","nl","eo","fi","fr","de","el","hi","hu","id","ga","it","ja","ko","fa","pl","pt","ru","sk","es","sv","tr","uk","vi"];

//$LIBRETRANSLATESERVER="http://127.0.0.1:5000/translate";
$LIBRETRANSLATESERVER="https://translate.terraprint.co/translate";

//$WhichTranslator="GOOGLE";
//$WhichTranslator="DEEPLPRO";
//$WhichTranslator="DEEPLFREE";
//$WhichTranslator="LIBRETRANSLATE";

$WhichTranslator = "DEEPLFREE";
if (isset($AllTokens["WHICHTRANSLATOR"]) && count($AllTokens["WHICHTRANSLATOR"])>0) $WhichTranslator=$AllTokens["WHICHTRANSLATOR"][(gmdate("s")*1)%count($AllTokens["WHICHTRANSLATOR"])];


//$DEBUG = true;
$DEBUG = false;

$WhichTranslator = "DEEPLPRO";
if (isset($AllTokens["WHICHTRANSLATOR"]) && count($AllTokens["WHICHTRANSLATOR"])>0) $WhichTranslator=$AllTokens["WHICHTRANSLATOR"][(gmdate("s")*1)%count($AllTokens["WHICHTRANSLATOR"])];

//$proxy = "https_proxy='http://cache.univ-nantes.fr:3128' ";

//if ((strpos($_SERVER['SERVER_NAME'],"univ-nantes.fr") === false) || ((strpos($_SERVER['SERVER_NAME'],"172.26.98.138") === false))) $proxy="";

// JB Microsoft account, only for testing purposes !
$MicrosoftKey_default='';
$MicrosoftRegion_default='';

$SAVESTATS=false;
?>
