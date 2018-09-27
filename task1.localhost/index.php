<?php
/**
 * Тестируем работу
 * task1.localhost
 * User: Avatar - avatar130891@gmail.com
 * Date: 25.09.2018
 * Time: 16:17
 */

require_once 'BracerChecker.php';

$checker = new BracerChecker();

$statementList = [
	"---(++++)----" => $checker->checkBraces("---(++++)----") == 0,
	"" => $checker->checkBraces("") == 0,
	"before ( middle []) after " => $checker->checkBraces("before ( middle []) after ") == 0,
	") (" => $checker->checkBraces(") (") == 1,
	"} {" => $checker->checkBraces("} {") == 1,
	"<(   >)" => $checker->checkBraces("<(   >)") == 1,
	"(  [  <>  ()  ]  <>  )" => $checker->checkBraces("(  [  <>  ()  ]  <>  )") == 0,
	"   (      [)" => $checker->checkBraces("   (      [)") == 1,
	
];

foreach ($statementList as $statement => $statementValue) {
	if (!$statementValue) {
		echo "Error in $statement<br/>";
	}
}