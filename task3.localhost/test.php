<?php

$cmds = [
	'php index.php testSamples/example.dump.sql testSamples/example.dump.out.sql config/config.php',
	'php index.php testSamples/sakila-mv-data.sql testSamples/sakila-mv-data.out.sql config/saikaConfig.php'
];

$outs = [
	'testSamples/example.dump.out.sql',
	'testSamples/sakila-mv-data.out.sql'
];

$assert = [
	['asd@test.com', '+79053334455'],
	['666']
];

for ($i = 0; $i < count($cmds); $i++) {
	shell_exec($cmds[$i]);
	$contents = file_get_contents($outs[$i]);
	foreach ($assert[$i] as $assertItem) {
		if (strpos($contents, $assertItem) === FALSE)
			echo "$assertItem not found in {$outs[$i]}\n";
	}
}
