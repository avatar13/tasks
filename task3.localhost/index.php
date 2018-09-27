<?php

if ($argc != 4) {
	echo "please select input file \n";
	echo "please select output file \n";
	echo "please select config file \n";
	die;
}

$inputFile = $argv[1];
$outputFile = $argv[2];
$configFile = $argv[3];

if (!file_exists($inputFile))
	die("$inputFile not exists");

if (!file_exists($configFile))
	die("$configFile not exists");

// probalby not very safe
$config = require_once "$configFile";

require_once 'vendor/autoload.php';

require_once 'src/SqlProcessor.php';

// TODO: написать валидацию конфигурации предварительно. В трансформере проверяется конечно
$processor = new SqlProcessor($config);

// читаем построчно, так как дампы БД могут легко превышать 100гб
$inputFileDescriptor = fopen($inputFile, 'r');
if (!$inputFileDescriptor)
	die("Невозможно открыть указанный файл $inputFile");

if (file_exists($outputFile))
	@unlink($outputFile);

$outputFileDescriptor = fopen($outputFile, 'a');

$sqlRawStatement = "";
while (!feof($inputFileDescriptor)) {
	$line = fgets($inputFileDescriptor);
	$sqlRawStatement .= $line;

	// пытаемся найти sql statement
	if (strpos($line, ';') !== FALSE) {

		$resultSql = $processor->processSqlStatement($sqlRawStatement);
		fwrite($outputFileDescriptor, $resultSql);
		$sqlRawStatement = '';
	}

}

fclose($inputFileDescriptor);
fclose($outputFileDescriptor);
