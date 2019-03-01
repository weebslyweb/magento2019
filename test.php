<?php
$a = '10101110';
/*if (preg_match('~^[01]+$~', $a)) {
    echo 'true';
}
else{
	echo 'false';
}*/
$b = binarycheck($a);
function binarycheck($a)
{
	//$array  = array_map('intval', str_split($a));
	$array = str_split($a);
	$check = 0;
	foreach ($array as $key => $value) {
		//return  gettype($value);
		if($value !== '0' && $value !== '1')
		{  
			$check += 1;
		}
		else{
			
			$check += 0;
		}
	}
	if($check >= 1)
	{
		echo 'false';
	}
	else
	{
		echo 'true';
	}
}