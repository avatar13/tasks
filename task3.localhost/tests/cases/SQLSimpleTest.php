<?php

use PHPUnit\Framework\TestCase;

class SQLSimpleTest extends TestCase
{
	public function testEmailObfuscation()
	{
		$processor = new SqlProcessor([
			'user' => [
				'email' => [
					'type' => 'email',
					'value' => 'quash'
				],
			],
		]);

		$sqlResult = $processor->processSqlStatement("
			INSERT INTO `user` (`email`, `role`) 
			VALUES ('4232456@mail.ru','employee');");

		$this->assertContains('quash', $sqlResult);
	}

	public function testNumberObfuscation()
	{
		$processor = new SqlProcessor([
			'user' => [
				'email' => [
					'type' => 'integer',
					'value' => 1900
				],
			],
		]);

		$sqlResult = $processor->processSqlStatement("
			INSERT INTO `user` (`email`, `role`) 
			VALUES ('4232456@mail.ru','employee');");

		$this->assertContains('1900', $sqlResult);
	}

	public function testCreateWithMultipleInserts()
	{
		$processor = new SqlProcessor([
			'shop_currencies' => [
				'name' => [
					'type' => 'string',
					'value' => 'aszxc'
				],
			],
		]);

		$sql = "CREATE TABLE `shop_currencies` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `code` char(3) NOT NULL DEFAULT '',
			  `exchange_rate` decimal(16,6) NOT NULL DEFAULT '0.000000',
			  `date` date NOT NULL DEFAULT '0000-00-00',
			  `default` tinyint(1) NOT NULL DEFAULT '0',
			  `sorting` smallint(6) NOT NULL DEFAULT '0',
			  `user_id` int(11) NOT NULL DEFAULT '0',
			  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  KEY `deleted` (`deleted`,`code`)
			) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;";

		$sqlSecond = "INSERT INTO `shop_currencies` VALUES ('1', 'руб.', 'RUB', 666, '0000-00-00', '1', '10', '19', '0');";
		$sqlThird = "INSERT INTO `shop_currencies` VALUES ('3', '$', 'USD', '23.639664', '0000-00-00', '0', '30', '19', '0');";

		$resSql = $processor->processSqlStatement($sql);
		$resSqlSecond = $processor->processSqlStatement($sqlSecond);
		$this->assertContains('aszxc', $resSqlSecond, 'second statement not working properly');
		$resSqlThird = $processor->processSqlStatement($sqlThird);
		$this->assertContains('aszxc', $resSqlThird, 'third statement not working properly');
	}

	public function testMultiInsert()
	{
		$processor = new SqlProcessor([
			'user' => [
				'email' => [
					'type' => 'integer',
					'value' => 1900
				],
			],
		]);

		$sqlResult = $processor->processSqlStatement("
			INSERT INTO `user` (`email`, `role`) 
			VALUES ('4232456@mail.ru','employee'), ('4232456@mail.ru','employee');");

		$this->assertNotContains('4232456@mail.ru', $sqlResult);
	}

	public function testTableAssigning()
	{
		$processor = new SqlProcessor([
			'not_user' => [
				'email' => [
					'type' => 'integer',
					'value' => 1900
				],
			],
		]);

		$sqlResult = $processor->processSqlStatement("
			INSERT INTO `user` (`email`, `role`) 
			VALUES ('4232456@mail.ru','employee');");

		$this->assertNotContains('1900', $sqlResult);
	}
}