<?php
/**
 * Created by xnodza00.
 * Projekt do IPP - Makroprocesor
 **/


/** Vrati nazev makra
* @param file pointer
* @return string
*/
function getMakroName($fp){
	$name = "";
	$state = 0;
	$repeat = true;
	while($repeat){
		$znak = fgetc($fp);
		switch ($state) {
			case 0:
				if(preg_match("/[a-zA-Z_]/", $znak)){
					$state = 1;
					$name .= $znak;
				}
				else{
					fprintf(STDERR, "Syntakticka chyba: chybny nazev makra");
            		exit(55);
				}
				break;
			case 1:
				if(preg_match("/[0-9a-zA-Z_]/", $znak)){
					$state = 1;
					$name .= $znak;
				}
				else{
					$repeat = false;
					fseek($fp, -1, SEEK_CUR);
				}
				break;

			default:
				# code...
				break;
		}
	}
	return $name;
}

/** Vrati obsah bloku
* @param file pointer $fp
* @return string $set
*/
function getSetArgument($fp){
	$set = "";
	while(1){
		$znak = fgetc($fp);
		if(feof($fp)){
			fprintf(STDERR, "Syntakticka chyba: neocekavany konec souboru");
            exit(55);
		}
		elseif($znak == "\n"){
            fprintf(STDERR, "Syntakticka chyba: neocekavany konec radku");
            exit(55);
        }
        elseif ($znak == "}") {
        	break;
        }
        else{
        	$set .= $znak;
        }
	}
	return $set;
}

/** Vrati nazev argumentu
* @param file pointer $fp
* @return string $argument
*/
function setArgument($fp, $table){
	$argument = "";
	$repeat = true;
	$state = 0;
	$znak = fgetc($fp);
	if($znak == "{"){
		$argument = getSetArgument($fp);
	}
	elseif(feof($fp)){
		fprintf(STDERR, "Syntakticka chyba: neocekavany konec vstupu pri nacitani argumentu");
        exit(55);
	}
	elseif ($znak == "@") {
		$makro = getMakroName($fp);
		if(isset($table[$makro])){
			$argument = expansion($fp, $makro, $table);
		}
		else{
			fprintf(STDERR, "Semanticka chyba: makro: @".$makro." neni definovane");
        	exit(56);
		}
	}
	else $argument = $znak;
	return $argument;

}

/** Vrati expandovane makro na retezec
* @param file pointer $fp
* @return string $expansion
*/
function expansion($fp, $makroName, $table){
	$counter = $table[$makroName]['counter'];
	$expansion = $table[$makroName]['result'];
	foreach ($table[$makroName] as $argument => $hodnota) {
		if($counter != 0){
			$hodnota = setArgument($fp, $table);
			$counter--;
		}
		else break;
		$pos = strpos($expansion, $argument);
		if($pos !== false){
			$expansion = str_replace($argument, $hodnota, $expansion);
		}
	}
	return $expansion;
}

/** Vrati obsah 3. argumentu makra @def
* @param file pointer $fp
* @return string $result
*/
function getResult($fp){

    $result = "";
    $counter = 0;
    while(1){
    	$znak = fgetc($fp);
    	if(feof($fp)){
    		fprintf(STDERR, "Syntakticka chyba: neocekavany konec souboru");
            exit(55);
    	}
        elseif($znak == "\n"){
            fprintf(STDERR, "Syntakticka chyba: neocekavany konec radku");
            exit(55);
        }
        elseif($znak == "@"){
            $znak = fgetc($fp);
            if($znak == "$"){
                $result .= $znak;
            }
            else{
                fseek($fp, -1, SEEK_CUR);
                $result .= "@";
            }
        }
        elseif ($znak == "}" && $counter == 0) {
        	break;
        }
        elseif ($znak == "{") {
        	$counter++;
        	$result .= $znak;
        }
        elseif ($znak == "}") {
        	$counter--;
        	$result .= $znak;
        }
        else $result .= $znak;
    }
    return $result;
}

/** Vrati argument
* @param file pointer $fp
* @return string $argument
*/
function getArgument($fp){

    $state = 0;
    $repeat = true;
    $argument = "";
    $znak = '';
    while($repeat){
        $znak = fgetc($fp);
        switch($state){
        case 0:
            if($znak == '$'){
                $state = 1;
                $argument .= $znak; 
            }
            break;
        case 1:
            if (preg_match("/[a-zA-Z_]/", $znak)){
                $state = 2;
                $argument .= $znak;
            }
            else{
            	fprintf(STDERR, "Syntakticka chyba: spatne zapsany argument");
            	exit(55);
            }
            break;
        case 2:
            if (preg_match("/[a-zA-Z_]/", $znak)){
                $state = 2;
                $argument .= $znak;
            }
            else {
            	$repeat = false;
            	fseek($fp, -1, SEEK_CUR);
            }
            break;
        default:
            break;
        }
    }
    
    return $argument;
}

/** Vrati 1 pokud zpracovani prepinacu problehlo spravne
* @param int $argc
* @param int* $argv
* @return int
*/    
function handleArguments($argc, $argv)
{
    $input = "/--input=\"*(.*)\"*/";
    $output = "/--output=\"*(.*)\"*/";
    $text = "/--cmd=(.*)/";
    $countI = 0;
    $countO = 0;
    for ($i = 1; $i < $argc; $i++) {
        if ((preg_match($input, $argv[$i], $match1)) && ($countI == 0)) {
        	if($match1[1] == ""){
        		$GLOBALS['fileI'] = null;
        	}
        	else{
        		$nameI = $match1[1];
            	$GLOBALS['fileI'] = $nameI;
        	}
            $countI += 1;
        } elseif ((preg_match($output, $argv[$i], $match2)) && ($countO == 0)) {
            if($match2[1] == ""){
        		$GLOBALS['fileO'] = null;
        	}
        	else{
        		$nameO = $match2[1];
            	$GLOBALS['fileO'] = $nameO;
        	}
            $countO += 1;
        } elseif ($argv[$i] == "-r") {
            echo "redefinition \n";
        } elseif (preg_match($text, $argv[$i], $match)) {
            $file = file_get_contents($GLOBALS['fileO']);
            $content = $match[1] . $file;
            file_put_contents($GLOBALS['fileO'], $content);
        } else return 0;
    }
    return 1;
}


$GLOBALS['fileI'] = "";
$GLOBALS['fileO'] = "";
$fileO = "";
if (($argv[1] == "--help") && ($argc == 2)){ 
	echo "Pro zadani vstupniho souboru je prepinac --intput=vas_vstupni_soubor.\nPro zadani vystupniho souboru je prepinac --output=vas_vystupni_soubor.\nPro vlozeni textu na zacatek vystupniho souboru je prepinac --cmd=vas_text.\nPro redefinici makra je prepinac -r\n";
}
else {
    if (handleArguments($argc, $argv)) {
        //echo "vystupni soubor :\t" . $GLOBALS['fileO'] . "\n";
        //echo "vstupni soubor :\t" . $GLOBALS['fileI'] . "\n";
    } else{
        echo "bad";
        exit(1);
    }

    if (!$GLOBALS['fileO']) {
    	$fO = STDOUT;
    }
    elseif($fO = fopen($GLOBALS['fileO'], "w"));
    else{
    	fprintf(STDERR, "Chyba souboru: chyba pri pokusu o otevreni vystupniho souboru");
        exit(3);
    }

    if(!$GLOBALS['fileI']) {
    	$fI = STDIN;
    }
    elseif(file_exists($GLOBALS['fileI'])){
    	$fI = fopen($GLOBALS['fileI'], "r");
    }
    else{
    	fprintf(STDERR, "Chyba souboru: chyba pri pokusu o otevreni vstupniho souboru");
        exit(3);
    }
    $table = array();


    $state = 0;
    $repeat = 1;
    while ($repeat) {
        $znak = fgetc($fI);
        //echo $state;
        switch ($state) {
            case 0:
                if ($znak == '$') {
                    fprintf(STDERR, "Syntakticka chyba 1\n");
                    return 55;
                } elseif ($znak == '@') {
                    $znak = fgetc($fI);
                    if ($znak == '@') fwrite($fO, "@");
                    elseif ($znak == '{') fwrite($fO, "{");
                    elseif ($znak == '}') fwrite($fO, "}");
                    elseif ($znak == '$') fwrite($fO, "$");
                    else {
                        fseek($fI, -1, SEEK_CUR);
                        $state = 1;
                    }
                    break;
                } elseif ($znak == '{') {
                    $counter = 0;
                    $state = 3;
                    break;
                } elseif ($znak == '}') {
                    fprintf(STDERR, "Syntakticka chyba: chybi znak '{' ?\n");
                    exit(55);
                }
                elseif(feof($fI)) $repeat = 0;
                else {
                    fwrite($fO, $znak);
                    break;
                }
                break;
            case 1:
                if (preg_match("/[a-zA-Z_]/", $znak)) {
                    $state = 2;
                    $makro = "";
                    $makro .= $znak;
                }
                else {
                    fprintf(STDERR, "Syntakticka chyba: nepovoleny nazev makra");
                    exit(55);
                }
                break;
            case 2:
                if (preg_match("/[0-9a-zA-Z_]/", $znak)) { 
                    $state = 2;
                    $makro .= $znak;
                }
                else {
                    fseek($fI, -1, SEEK_CUR);
                    if(($makro == "def") || ($makro == "__def__")) {
                        $state = 4;
                    }
                    elseif(($makro == "undef") || ($makro == "__undef__")) {
                        $state = 5;
                    }
                    elseif (($makro == "set") || ($makro == "__set__")) {
                        $state = 6;
                    }
                    else $state = 7;
                }
                break;
            case 3:
                if($znak == '}' && $counter == 0){
                    $state = 0;
                    break;
                }
                elseif($znak == '}' && $counter != 0){
                    $counter--;
                }
                elseif($znak == '{'){
                    $counter++;
                }
                elseif($znak == '@'){
                    $znak = fgetc($fI);
                    if ($znak == '@');
                    elseif ($znak == '{');
                    elseif ($znak == '}');
                    elseif ($znak == '$');
                    else{
                        fwrite($fO, "@");
                    }
                }
                elseif(($znak == "\n") || feof($fI)){
                    fprintf(STDERR, "Syntakticka chyba: neocekavany konec bloku");
                    exit(55);
                }
                fwrite($fO, $znak);
                $state = 3;
                break;
            case 4:
            	if($znak == "@"){
            		$name = getMakroName($fI);
            		$state = 40;
            		$nameMakro = array();
            		//echo "nazev makra:" . $name . "\n";
            	}
            	else{
            		fprintf(STDERR, "Syntakticka chyba: makro @def ocekava jako 1. parametr nazev makra");
            		exit(55);
            	}
            	break;
            case 40:
            	if($znak == "{"){
            		$state = 41;
            		$counterA = 0;
            	}
            	else {
            		fprintf(STDERR, "Syntakticka chyba: makro @def ocekava jako 2. parametr blok");
            	    exit(55);
            	}
            	break;
            case 41:
            	if($znak == "$"){
            		fseek($fI, -1, SEEK_CUR);
            		$argument = getArgument($fI);
            		$counterA++;
            		$nameMakro[$argument] = null;
            		//echo "argument: " . $argument . "|\n";
            		$state = 41;
            	}
            	elseif($znak == " " || $znak == "\t"){
            		$state = 41;
            	}
            	elseif($znak == "}"){
            		$state = 42;
            		$nameMakro['counter'] = $counterA;
            		//echo "pocet argumentu makra: " . $counterA . "\n";
            	}
            	elseif(feof($fI)){
		    		fprintf(STDERR, "Syntakticka chyba: neocekavany konec souboru");
		            exit(55);
		    	}
		        elseif($znak == "\n"){
		            fprintf(STDERR, "Syntakticka chyba: neocekavany konec radku");
		            exit(55);
		        }
		        else {
            		fprintf(STDERR, "Syntakticka chyba: neocekavany znak: " . $znak . " pri nacitani argumentu\n");
                    exit(55);
            	}
            	break;
            case 42:
            	if($znak == "{"){
            		$result = getResult($fI);
            		//print_r($nameMakro);
            		if(preg_match_all('/([$][a-zA-Z_][0-9a-zA-Z_]*)/', $result, $m)){
						foreach ($m[0] as $key => $value) {
							$test = false;
							foreach ($nameMakro as $key2 => $value2) {
								if($value == $key2) $test = true;
							}
						}
						if(!$test){
							fprintf(STDERR, "Syntakticka chyba: nedefinovany argument v makru: @".$name);
		            		exit(55);
						}
					}

            		$nameMakro['result'] = $result;
            		$table[$name] = $nameMakro;
            		//echo "vysledek makra: " . $result . "\n";

            		$state = 0;
            	}
            	else{
            		fprintf(STDERR, "Syntakticka chyba: makro @def ocekava jako 2. parametr blok");
            	    exit(55);
            	}
            	break;
            case 5:
            	if($znak == "@"){
            		$delMakro = getMakroName($fI);
            		if($delMakro == "def" || $delMakro == "__def__" || $delMakro == "undef" || $delMakro == "__undef__" || $delMakro == "set" || $delMakro == "__set__"){
            			fprintf(STDERR, "Chyba: nelze zrusit makro: @". $delMakro);
            	    	exit(57);
            		}
            		unset($table[$delMakro]);
            		$state = 0;
            	}
            	else{
            		fprintf(STDERR, "Semanticka chyba: makro @undef ocekava jako parametr nazev makra");
            		exit(56);
            	}
            	break;
            case 6:
            	if($znak == "{"){
            		$set = getSetArgument($fI);
            		if($set == "-INPUT_SPACES"){
            			$white = true;
            		}
            		elseif ($set == "+INPUT_SPACES") {
            			$white = false;
            		}
            		else{
            			fprintf(STDERR, "Semanticka chyba: makro @set ocekava jako parametr +INPUT_SPACES nebo -INPUT_SPACES");
            			exit(56);
            		}
            	}
            	else{
            		fprintf(STDERR, "Semanticka chyba: makro @set ocekava jako parametr blok");
            		exit(56);
            	}
            	$state = 0;
            	break;
            case 7:
            	if(isset($table[$makro])){
            			fseek($fI, -1, SEEK_CUR);
            		$final = expansion($fI, $makro, $table);
            		fwrite($fO, $final);
            	}
            	else{
            		fprintf(STDERR, "Semanticka chyba: makro @". $makro ." neexistuje");
            		exit(56);
            	}
            	$state = 0;
            	break;
        }
    }
    //print_r($table);
    return 0;

}

?>

