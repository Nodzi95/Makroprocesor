<?php

function getResult($fp){

    $result = "";
    $znak = '';
    while(($znak = fgetc($fp)) != "}"){
        if(feof($fp)){
            fprintf(STDERR, "Syntakticka chyba: konec souboru");
            exit(55);
        }
        elseif($znak == "\n"){
            fprintf(STDERR, "Syntakticka chyba: konec radku");
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

$fp = fopen("in03.txt", "r");
$result = getResult($fp);
echo $result;

?>