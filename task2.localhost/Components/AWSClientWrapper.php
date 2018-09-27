<?php

/**
 * task2.localhost
 * User: Avatar - avatar130891@gmail.com
 * Date: 25.09.2018
 * Time: 20:01
 */
class AWSClientWrapper
{
	private $s3client;
	private $s3ConfigObject;

	private $uploadedFiles = [];
	private $fileSizeTotal = 0;

	public function __construct($s3client, S3ConfigObject $s3ConfigObject)
	{
		$this->s3client = $s3client;
		$this->s3ConfigObject = $s3ConfigObject;
	}

	/**
	 * Загружает файл на амазон и проверяет права
	 * @param PostFileModel $fileModel
	 * @param $filterParam
	 * @throws ReturnUploadErrorException
	 */
	public function uploadFile(PostFileModel $fileModel, $filterParam)
	{
		$args = apply_filters('as3cf_object_meta', $this->s3ConfigObject->getAwsFormat($fileModel), $filterParam);

		$this->uploadedFiles[] = $fileModel->getFilePath();
		try {
			$this->s3client->putObject($args);
		} catch ( Exception $e ) {

			$error_msg = sprintf( __( 'Error uploading %s to S3: %s', 'amazon-s3-and-cloudfront' ),
				$fileModel->getFilePath(), $e->getMessage()
			);

			throw new ReturnUploadErrorException($error_msg);
		}
	}

	/**
	 * Загружает доп. файлы на амазон
	 * @param $filePathList
	 * @param $mimeType
	 * @throws ReturnUploadErrorException
	 */
	public function uploadAttachmentSizesObjects($filePathList, $mimeType)
	{
		foreach ( $filePathList as $filePath ) {
			if ( !in_array( $filePath, $this->uploadedFiles ) ) {
				$fileModel = new PostFileModel($filePath, $mimeType);
				
				$fileModel->checkFileExistsLocally();
				$this->uploadedFiles[] = $fileModel->getFilePath();
				$this->fileSizeTotal += $fileModel->getFileSize();
				
				try {
					$this->s3client->putObject($this->s3ConfigObject->getAwsFormat($fileModel));
				} catch ( Exception $e ) {

					$error_msg = sprintf( __( 'Error uploading %s to S3: %s', 'amazon-s3-and-cloudfront' ),
						$fileModel->getFilePath(), $e->getMessage()
					);

					throw new ReturnUploadErrorException($error_msg);
				}
			}
		}
	}
	
	public function getUploadedFileList()
	{
		return $this->uploadedFiles;
	}

	public function getTransmittedSize()
	{
		return $this->fileSizeTotal;
	}
}