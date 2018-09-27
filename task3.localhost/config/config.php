<?php

// имя таблицы => список полей, данные которых мы очищаем
return [
	'user' => [
		'email' => [
			'type' => 'email',
			'value' => 'asd@test.com'
		],
		'phone' => [
			'type' => 'phone'
		],
		'name' => [
			'type' => 'string'
		]
	],
];