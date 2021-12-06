<?php

include "config.php";

$texte = html_entity_decode(strip_tags($_GET["texte"]));
$lang = $_GET["lang"];
$langinput = $_GET["langinput"];

function removeAccents($str) {
  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
  return str_replace($a, $b, $str);
}

if (isset($_GET["ROOMID"])) {
      $result = "";
      if (file_exists("tmp/Translated_".$_GET["ROOMID"])) {
      $fd=fopen("tmp/Translated_".$_GET["ROOMID"],"r");
      if (isset($_GET["lineid"])) $lineid=$_GET["lineid"]; else $lineid="NONE";
      while($T=fgetcsv($fd,4096,";")) {
        //print_r($T);
        //echo implode(" ",$T)." --- ".$lineid." = ".$T[0]." , ".$lang." = ".$T[1]."\n";
        if (count($T)>2) {
          //$T=explode(";",$L);
          if ($lineid == $T[0] && $lang == $T[1]) {
            $result = "".$T[2];//"{ \"data\": { \"translations\": [ { \"translatedText\": \"".$T[2]."\" } ] } }";
            if ($lang == "en") $result=removeAccents($result);
          }
        }
      }
      fclose($fd);
      }
      if ($result == "") {

        // Launch Google Traduction API
        if ($WhichTranslator == "GOOGLE") {
        $output=array(); $retval=0;
        exec($proxy."curl -s -X POST -H \"Content-Type: application/json\" --data \"{ 'q': '".str_replace("'","\'",$texte."")."',  'source': '".$langinput."',  'target': '".$lang."',  'format': 'text'}\" \"https://translation.googleapis.com/language/translate/v2?key=".$GOOGLEAPITOKEN."\"",$output,$retval);
        $resultjson = (implode(" ",$output));
        $result=$resultjson;
        //$result=$resultjson->data->translations[0]->translatedText;
        }

        // Launch DeepL Translation API
        if ($WhichTranslator == "DEEPL") {
        $output=array(); $retval=0;
        exec($proxy."curl https://api-free.deepl.com/v2/translate -d auth_key=".$DEEPLAPITOKEN." -d \"text=".str_replace('"','\"',$texte."")."\" -d \"source_lang=".strtoupper($langinput)."\" -d \"target_lang=".strtoupper($lang)."\"",$output,$retval);
        $resultjson = (implode(" ",$output));
        $resultsinjson = json_decode($resultjson);
        $resultjson = '{   "data": '.str_replace("\n"," ",str_replace('"text"','"translatedText"',$resultjson)).' }';

        $result=$resultjson;

        }
/*
{   "data": {     "translations": [       {         "translatedText": "For a lattice mode P, all the values h, k, l are possible. Consequently, the first reflection has the index 1 0 0. The series of the ratios of the squared distances is therefore 1, 2, 3, 4, 5, 6, 8, 9"       }     ]   } }
*/
/*
{
	"translations": [{
		"detected_source_language":"EN",
		"text":"Hallo, Welt!"
	}]
}
*/      

        echo $resultjson;
        $fd=fopen("tmp/Translated_".$_GET["ROOMID"],"a");
        fputs($fd,$_GET["lineid"].";".$lang.";".$result."\n");
        fclose($fd);
      } else {
        echo $result;
      }


} else {
  system($proxy."curl -s -X POST -H \"Content-Type: application/json\" --data \"{ 'q': '".str_replace("'","\'",$texte." WRONG.")."',  'source': '".$langinput."',  'target': '".$lang."',  'format': 'text'}\" \"https://translation.googleapis.com/language/translate/v2?key=".$GOOGLEAPITOKEN."\"");
}

?>
