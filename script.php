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

function handleArguments($argc, $argv){
    $input = "/--input=\"*(.*\.txt)\"*/";
    $output = "/--output=\"*(.*\.txt)\"*/";
    for($i = 1; $i < $argc; $i++){
        if(preg_match($input, $argv[$i], $match1)){
            $nameI = $match1[1];
            echo "dostal jsem input : " . $nameI . "\n";
        }
        elseif(preg_match($output, $argv[$i],$match2)){
            $nameO = $match2[1];
            echo "dostal jsem output : " . $nameO . "\n";
        }
        else return 0;
    }
    return 1;
}
function checkInput($io)
{
    //handle file open and check argument
    $input = "/--input=\"*(.*\.txt)\"*/";

    if (preg_match($input, $io, $match)) {
        $io = $match[1];
        if (file_exists($io)) {
            return $io;
        } else echo "File not found.";
    } else echo "koncis";

}

function checkOutput($io)
{
    $output = "/--output=\"*(.*\.txt)\"*/";
    if (preg_match($output, $io, $match)) {
        $io = $match[1];
        if (file_exists($io)) {
            return $io;
        } else echo "File not found.";
    } else echo "koncis";
}

if (($argv[1] == "--help") && ($argc == 2)) echo "help";
elseif ((preg_match("/--cmd=(.*)/", $argv[1], $match)) && ($argc == 2)) {
    $file = file_get_contents("555.txt");
    $content = $match[1] . $file;
    file_put_contents("555.txt", $content);
}
elseif (($argv[1] == "-r") && ($argc == 2)) echo "nope";
else{
    if(handleArguments($argc, $argv)){
        echo "probehlo ok";
    }
    else echo "probehlo spatne";
}
?>

