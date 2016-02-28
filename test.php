<?php


$neco1 = "ahoj1";
$neco2 = "ahoj2";
$neco3 = "ahoj3";
$neco4 = "ahoj4";



//*******************
//tabulka maker
$table = array();
$name = 'def';


$makro = array();
$argument = '$a';
$makro[$argument] = "";
$argument = '$b';
$makro[$argument] = "";
$argument = '$c';
$makro[$argument] = "";
$argument = '$d';
$makro[$argument] = "";
$makro['result'] = '<fak>$a</fak>$b $c';
$makro['arguments'] = 4;
$table[$name] = $makro;

$makro = array();
$argument = '$a';
$makro[$argument] = "";
$argument = '$b';
$makro[$argument] = "";
$argument = '$c';
$makro[$argument] = "";
$argument = '$d';
$makro[$argument] = "";
$makro['result'] = '<fak>$a</fak>';
$makro['arguments'] = 4;
$table['def2'] = $makro;
//*******************

//*******************
//plnění tabulky
foreach ($table as $key => $value) {
	if($key == "def2"){
		$counter = 0;
		foreach ($value as $key2 => $value2) {
			if($key2 == "arguments" || $key2 == "result");
			else {
				$value[$key2] = $neco1;
				$counter++;
			}
		}
		if($counter != $value['arguments']){
			echo "spatny pocet argumentu";
			return 55;
		}
		else{
			$table[$key] = $value;
		}
		
	}


}
//*******************



//*******************
//algoritmus expanze makra

foreach ($table as $key2 => $value2) {
	if($key2 == 'def2'){
		$string = $value2['result'];
		foreach ($value2 as $key => $value) {
			if(strpos($string, $key)){
					echo "match         ";
					$string = str_replace($key, $value, $string);
					echo $key . " " .$string . "\n";
			}
		}
	}
	else echo "nope";
}
//*******************




?>