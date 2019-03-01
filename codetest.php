<?php
$a = '1';
echo $b = &$a;
echo $c = "2$b";

var_dump(0123 == 123);
var_dump('0123' == 123); 
var_dump('0123' === 123);


$x = true and false;
var_dump($x);

$array = array(
1 => "a",
"1" => "b",
1.5 => "c",
true => "d",
);
print_r($array);
