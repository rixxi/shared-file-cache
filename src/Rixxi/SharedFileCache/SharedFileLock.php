<?php

namespace Rixxi\SharedFileCache;


/**
 * Container for keeping lock
 */
class SharedFileLock
{

	/** @var string */
	private $filename;

	/** @var resource */
	private $handle;


	/**
	 * @param string
	 * @param resource
	 */
	public function __construct($filename, $handle)
	{
		$this->filename = $filename;
		$this->handle = $handle;
	}


	public function getFilename()
	{
		return $this->filename;
	}


	public function __toString()
	{
		return $this->filename;
	}

}
