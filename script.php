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
elseif ((preg_match("/--input=\"*(.*\.txt)\"*/", $argv[1])) && ($argc == 2)) $fIn = checkInput($argv[1]);
elseif ((preg_match("/--output=\"*(.*\.txt)\"*/", $argv[1])) && ($argc == 2)) $fOut = checkOutput($argv[1]);
else echo "chyba";
?>

