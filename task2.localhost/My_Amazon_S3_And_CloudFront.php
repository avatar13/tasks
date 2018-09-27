<?php

/**
 * task2.localhost
 * User: Avatar - avatar130891@gmail.com
 * Date: 25.09.2018
 * Time: 19:27
 */
class My_Amazon_S3_And_CloudFront
{
	public function upload_file_to_s3_v2(
		$attachmentId,
		$data = null,
		$file_path = null,
		$force_new_s3_client = false,
		$isRemoveLocalFiles = true)
	{
		$isMetaHaveBeenSupplied = true;

		try {

			// Берем ли данные из БД
			if (is_null($data)) {
				$data = wp_get_attachment_metadata($attachmentId, true);
				$isMetaHaveBeenSupplied = false;
			}
			if (is_wp_error($data) || !$data)
				throw new DataAccessException($data);

			// Allow S3 upload to be hijacked / cancelled for any reason
			$pre = apply_filters( 'as3cf_pre_upload_attachment', false, $attachmentId, $data );
			$this->ensureFilter($pre);

			if (is_null($file_path))
				$file_path = get_attached_file($attachmentId, true);
			
			$mimeType = get_post_mime_type($attachmentId);
			$postFile = new PostFileModel($file_path, $mimeType);

			// note: поменял местами, mime тайп проверить менее нагруженно, чем лезть на диск
			$postFile->checkMimeType($this->get_allowed_mime_types());
			$postFile->checkFileExistsLocally();

			$s3object = $this->configureS3Object($attachmentId, $data, $postFile);

			$s3client = $this->get_s3client($s3object->getRegion(), $force_new_s3_client);
			$client = new AWSClientWrapper($s3client, $s3object);

			// NOTE: загрузка файла между запросами БД=) весело живем.
			// если запросы в транзакции БД, то никакой хайлоад этот код не выдержит, но это вне рамок данной задачи
			$client->uploadFile($postFile, $attachmentId);

			$filePathList = $this->get_attachment_file_paths($attachmentId, true, $data);
			
			// загрузим файлы sizes
			$client->uploadAttachmentSizesObjects($filePathList, $mimeType);

			// note: update=)
			delete_post_meta($attachmentId, 'amazonS3_info');
			add_post_meta($attachmentId, 'amazonS3_info', $s3object->getDBFormat($postFile));

			// Удалим уже загруженные файлы, они уже не нужны
			if ($isRemoveLocalFiles && $this->get_setting('remove-local-file')) {
				$filePathList = apply_filters('as3cf_upload_attachment_local_files_to_remove',
					$filePathList, $attachmentId, $file_path);
				$this->remove_local_files($filePathList);
			}

			if ($client->getTransmittedSize() > 0) {
				update_post_meta($attachmentId, 'wpos3_filesize_total', $client->getTransmittedSize());
			}

			// NOTE: код ниже, чтобы соответствовать вышестоящему инерфейсу
			if (!$isMetaHaveBeenSupplied) {
				// If the attachment metadata is supplied, return it
				return $data;
			}
			
			return $s3object->getDBFormat($postFile);
			
		} catch (DataAccessException $e) {
			
			return $e->getData();
		} catch (ReturnUploadErrorException $e) {
			
			return $this->return_upload_error($e->getMessage(), $isMetaHaveBeenSupplied ? $data : null);
		}
	}


	/**
	 * Кофигурирует s3object.
	 * @param $post_id
	 * @param $data
	 * @return S3ConfigObject
	 */
	private function configureS3Object($post_id, $data)
	{
		// check the attachment already exists in S3, eg. edit or restore image
		if (($old_s3object = $this->get_attachment_s3_info($post_id))) {

			// use existing prefix
			$prefix = dirname( $old_s3object['key'] );
			$prefix = ( '.' === $prefix ) ? '' : $prefix . '/';

			$config = [
				'acl' => isset($old_s3object['acl']) ? $old_s3object['acl'] : S3ConfigObject::DEFAULT_ACL,
				'prefix' => $prefix,
				'bucket' => $old_s3object['bucket'],
				'region' => isset($old_s3object['region']) ? $old_s3object['region'] : S3ConfigObject::DEFAULT_REGION
			];

		} else {
			$config = [
				'acl' => S3ConfigObject::DEFAULT_ACL,
				// не понял какой формат, передал таймстем. либо date('Y/m', time());
				'prefix' => $this->get_file_prefix( time() ),
				'bucket' => $this->get_setting('bucket'),
				'region' => $this->get_setting('region')
			];
		}
		
		$acl = apply_filters('as3cf_upload_acl', $config['acl'], $data, $post_id);

		return new S3ConfigObject($config['bucket'], $config['prefix'], $config['region'], $acl);
	}

	/**
	 * Проверяет прошла ли проверка прав.
	 * @param $pre
	 * @throws ReturnUploadErrorException
	 */
	private function ensureFilter($pre)
	{
		if ( false !== $pre ) {
			$error_msg = is_string( $pre ) ? $pre : __(
				'Upload aborted by filter \'as3cf_pre_upload_attachment\'',
				'amazon-s3-and-cloudfront'
			);

			throw new ReturnUploadErrorException($error_msg);
		}
	}
}