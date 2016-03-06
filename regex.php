<?php 

$string = '<b>aba</b>nenepotom';
if(preg_match_all('/([$][a-zA-Z_][0-9a-zA-Z_]*)/', $string, $m)){
	foreach ($m[0] as $key => $value) {
		echo $value;
	}
}


?>