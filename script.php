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
            break;
        case 2:
            if (preg_match("/[a-zA-Z_]/", $znak)){
                $state = 2;
                $argument .= $znak;
            }
            else $repeat = false;
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
        echo "vystupni soubor :\t" . $GLOBALS['fileO'] . "\n";
        echo "vstupni soubor :\t" . $GLOBALS['fileI'] . "\n";
    } else{
        echo "bad";
        return 1;
    }

    $fO = fopen($GLOBALS['fileO'], "w");
    $fI = fopen($GLOBALS['fileI'], "r");

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
                    $state = 0;
                    fseek($fI, -1, SEEK_CUR);
                    if(($makro == "def") || ($makro == "__def__")) {
                        echo $makro;
                    }
                    elseif(($makro == "undef") || ($makro == "__undef__")) {
                        echo $makro;
                    }
                    elseif (($makro == "set") || ($makro == "__set__")) {
                        echo $makro;
                    }
                    else echo $makro;
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
                        fseek($fI, -1, SEEK_CUR);
                        fwrite($fO, "@");
                    }
                }
                elseif(($znak == "\n") || feof($fI)){
                    fprintf(STDERR, "Chyba bloku");
                    return 55;
                }
                
                //echo $counter;
                fwrite($fO, $znak);
                $state = 3;
                break;
        }
    }
    return 0;

}

?>

