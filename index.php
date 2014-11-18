<?php
require_once 'Pcc/Pcc.php';

$arrayToConvert=[
					["EUR","USa",150.12],
					["EUR","USD",150.12],
					["USD","EUR",150.12],
					["EUR","JPY",150.12]
				];

$Converter = Pcc::convert($arrayToConvert);
 

echo "<pre><h1>Array Input:<br/></h1>";
print_r($arrayToConvert);
print_r("<h1>Array Output:<br/></h1>");
print_r($Converter);
 
