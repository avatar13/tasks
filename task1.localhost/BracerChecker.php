<?php

/**
 * task1.localhost
 * User: Avatar - avatar130891@gmail.com
 * Date: 25.09.2018
 * Time: 16:19
 */
class BracerChecker
{
	/**
	 * Реализовать функцию checkBraces($str), проверяющую на синтаксическую верность последовательность скобок
	 * @param $str
	 * @return int
	 */
	public function checkBraces($str)
	{
		$pairs = [
			'(' => ')',
			'[' => ']',
			'{' => '}',
			'<' => '>',
		];
		$braceStack = [];

		$len = strlen($str);
		for ($i = 0; $i < $len; $i++) {
			$char = $str[$i];

			// note: isset faster than array_key_exists
			if (isset($pairs[$char])) {
				$braceStack[] = $char;
			}

			$key = array_search($char, $pairs);
			if ($key !== false) {
				if (end($braceStack) != $key) {
					return 1;
				} else {
					array_pop($braceStack);
				}
			}
		}

		return !empty($braceStack) ? 1 : 0;
	}
}