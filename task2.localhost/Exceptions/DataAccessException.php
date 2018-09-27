<?php

/**
 * task2.localhost
 * User: Avatar - avatar130891@gmail.com
 * Date: 25.09.2018
 * Time: 18:29
 */
class DataAccessException extends Exception
{
	private $data;
	
	public function __construct($data, $message = "", $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	public function getData()
	{
		return $this->data;
	}
}