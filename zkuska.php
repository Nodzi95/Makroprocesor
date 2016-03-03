<?php


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

$fp = fopen("test.txt","r");
echo getArgument($fp);
echo getArgument($fp);
//echo getArgument($fp);


?>