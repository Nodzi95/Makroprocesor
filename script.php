<?php
/**
 * Created by PhpStorm.
 *
 **/
function checkArgument($arg)
{
    $regex = "/^\{\\$[a-zA-Z_][0-9a-zA-Z_]*\}/";
    if (preg_match($regex, $arg)) echo "cool";
    else echo "badly";
    return;
}


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

function getResult($fp){

    $result = "";
    while(($znak = fgetc($fp)) != "}"){
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
        else $result .= $znak;
    }
    return $result;
}

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
    
function handleArguments($argc, $argv)
{
    $input = "/--input=\"*(.*\.txt)\"*/";
    $output = "/--output=\"*(.*\.txt)\"*/";
    $text = "/--cmd=(.*)/";
    $countI = 0;
    $countO = 0;
    for ($i = 1; $i < $argc; $i++) {
        if ((preg_match($input, $argv[$i], $match1)) && ($countI == 0)) {
            $nameI = $match1[1];
            $GLOBALS['fileI'] = $nameI;
            $countI += 1;
        } elseif ((preg_match($output, $argv[$i], $match2)) && ($countO == 0)) {
            $nameO = $match2[1];
            $GLOBALS['fileO'] = $nameO;
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
if (($argv[1] == "--help") && ($argc == 2)) echo "help";
else {
    if (handleArguments($argc, $argv)) {
        //echo "vystupni soubor :\t" . $GLOBALS['fileO'] . "\n";
        //echo "vstupni soubor :\t" . $GLOBALS['fileI'] . "\n";
    } else{
        echo "bad";
        return 1;
    }

    $fO = fopen($GLOBALS['fileO'], "w");
    $fI = fopen($GLOBALS['fileI'], "r");
    $table = array();


    $state = 0;
    $repeat = 1;
    while ($repeat) {
        $znak = fgetc($fI);
        //echo $state;
        switch ($state) {
            case 0:
                if ($znak == '$') {
                    fprintf(STDERR, "Syntakticka chyba\n");
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
                    fprintf(STDERR, "Syntakticka chyba\n");
                    return 55;
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
                    fprintf(STDERR, "Syntakticka chyba");
                    return 55;
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
                    fprintf(STDERR, "Chyba bloku");
                    return 55;
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
		            fprintf(STDERR, "Syntakticka chyba: neocekavany konec radkus");
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
            			fprintf(STDERR, "Syntakticka chyba: nelze zrusit makro: @". $delMakro);
            	    	exit(55);
            		}
            		unset($table[$delMakro]);
            		$state = 0;
            	}
            	else{
            		fprintf(STDERR, "Syntakticka chyba: makro @undef ocekava jako parametr nazev makra");
            		exit(55);
            	}
            	break;
        }
    }
    print_r($table);
    return 0;

}

?>

