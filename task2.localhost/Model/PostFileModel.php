<?php

/**
 * task2.localhost
 * User: Avatar - avatar130891@gmail.com
 * Date: 25.09.2018
 * Time: 18:56
 */
class PostFileModel
{
	private $filePath;
	private $type;

	public function __construct($filePath, $type)
	{
		$this->filePath = $filePath;
		$this->type = $type;
	}

	public function checkMimeType($allowedTypes)
	{
		// check mime type of file is in allowed S3 mime types
		if (!in_array($this->type, $allowedTypes)) {
			$error_msg = sprintf(__('Mime type %s is not allowed', 'amazon-s3-and-cloudfront'), $this->type);

			throw new ReturnUploadErrorException($error_msg);
		}
	}

	public function checkFileExistsLocally()
	{
		// Check file exists locally before attempting upload
		if (!file_exists($this->filePath)) {
			$error_msg = sprintf(__('File %s does not exist', 'amazon-s3-and-cloudfront'), $this->filePath);

			throw new ReturnUploadErrorException($error_msg);
		}
	}

	public function getFileName()
	{
		return basename($this->filePath);
	}

	public function getFileSize()
	{
		$size = filesize($this->filePath);
		if ($size !== false)
			return $size;
		
		return 0;
	}

	/**
	 * @return mixed
	 */
	public function getFilePath()
	{
		return $this->filePath;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}
}