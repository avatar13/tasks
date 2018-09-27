<?php

/**
 * task2.localhost
 * User: Avatar - avatar130891@gmail.com
 * Date: 25.09.2018
 * Time: 19:32
 */
class S3ConfigObject
{
	const DEFAULT_ACL = 'public-read';
	const DEFAULT_REGION = 'us-west-1';
	
	private $bucket;
	private $acl;
	private $region;
	private $prefix;

	public function __construct($bucket, $prefix, $region, $acl)
	{
		$this->bucket = $bucket;
		$this->prefix = $prefix;
		$this->region = $region;
		$this->acl = $acl;
	}

	public static function createFromConfig($config)
	{
		return new S3ConfigObject(
			$config['bucket'],
			$config['prefix'],
			$config['region'],
			$config['acl']
		);
	}

	public function getAcl()
	{
		return $this->acl;
	}

	public function getAwsFormat(PostFileModel $file)
	{
		return [
			'Bucket'       => $this->bucket,
			'Key'          => $this->prefix . $file->getFileName(),
			'SourceFile'   => $file->getFilePath(),
			'ACL'          => $this->acl,
			'ContentType'  => $file->getType(),
			'CacheControl' => 'max-age=31536000',
			'Expires'      => date( 'D, d M Y H:i:s O', time() + 31536000 ),
		];
	}

	public function getDBFormat(PostFileModel $file)
	{
		$s3object = [
			'bucket' => $this->bucket,
			'key'    => $this->prefix . $file->getFileName(),
			'region' => $this->region,
		];

		// store acl if not default
		if ( $this->acl != self::DEFAULT_ACL )
			$s3object['acl'] = $this->acl;

		return $s3object;
	}


	/**
	 * @return mixed
	 */
	public function getRegion()
	{
		return $this->region;
	}

	public function getPrefix()
	{
		return $this->prefix;
	}


}