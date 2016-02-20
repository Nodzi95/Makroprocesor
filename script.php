<?php
/**
 * Created by PhpStorm.
 *
 **/
function checkArgument($arg){
    $regex = "/^\{\\$[a-zA-Z_][0-9a-zA-Z_]*\}/";
    if(preg_match($regex, $arg)) echo "cool";
    else echo "badly";
    return;

}
    if (($argv[1] == "--help") && ($argc == 2 ))  echo "help";
    elseif (($argv[1] == "--input=") && ($argc == 3 )){
        checkArgument($argv[2]);
    }
    elseif (($argv[1] == "--output=") && ($argc == 2 )) echo "im writing";
    elseif (($argv[1] == "--cmd=") && ($argc == 2 )) echo "im writing at start";
    elseif (($argv[1] == "-r") && ($argc == 2 )) echo "nope";
    else echo "bad";
?>

