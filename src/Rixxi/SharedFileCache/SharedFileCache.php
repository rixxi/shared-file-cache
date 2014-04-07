<?php

namespace Rixxi\SharedFileCache;


/**
 * Based on caching code of Nette\Latte\Engine and Nette\DI\ContainerFactory
 */
class SharedFileCache
{

	/** @var string */
	private $tempDirectory;

	/** @var bool */
	private $autoRefresh = FALSE;

	/** @var callback */
	private $contentGenerator;

	/** @var callback */
	private $filenameGenerator;

	/** @var callback */
	private $expirator;


	/**
	 * Sets path to temporary directory.
	 * @param string
	 * @return self
	 */
	public function setContentGenerator($callback)
	{
		if (!is_callable($callback)) {
			throw new InvalidArgumentException("Content generator must be a valid callback.");
		}
		$this->contentGenerator = $callback;
		return $this;
	}


	/**
	 * Sets path to temporary directory.
	 * @param callback
	 * @return self
	 */
	public function setFilenameGenerator($callback)
	{
		if (!is_callable($callback)) {
			throw new InvalidArgumentException("Filename generator must be a valid callback.");
		}
		$this->filenameGenerator = $callback;
		return $this;
	}


	/**
	 * Sets path to temporary directory.
	 * @param callback
	 * @return self
	 */
	public function setExpirator($callback)
	{
		if (!is_callable($callback)) {
			throw new InvalidArgumentException("Expirator generator must be a valid callback.");
		}
		$this->expirator = $callback;
		return $this;
	}


	/**
	 * Sets path to temporary directory.
	 * @param string
	 * @return self
	 */
	public function setTempDirectory($path)
	{
		$this->tempDirectory = $path;
		return $this;
	}


	/**
	 * Sets auto-refresh mode.
	 * @param bool
	 * @return self
	 */
	public function setAutoRefresh($on = TRUE)
	{
		$this->autoRefresh = (bool) $on;
		return $this;
	}


	/**
	 * Returns filename (without path) without generating the file.
	 *
	 * @param mixed
	 * @return string
	 */
	public function getFilename($value)
	{
		if (!$this->tempDirectory) {
			throw new InvalidStateException("Set path to temporary directory using setTempDirectory().");

		} elseif (!is_dir($this->tempDirectory)) {
			mkdir($this->tempDirectory);
		}

		if (!$this->filenameGenerator) {
			throw new InvalidStateException("Set filename generator using setFilenameGenerator().");
		}

		return $this->tempDirectory . '/' . call_user_func($this->filenameGenerator, $value) . '.php';
	}


	/**
	 * Generates content if necessary and returns its lock
	 *
	 * @param mixed
	 * @return \Rixxi\SharedFileCache\SharedFileLock
	 */
	public function getGeneratedFilename($value)
	{
		if ($this->contentGenerator === NULL) {
			throw new InvalidStateException("Set content generator using setContentGenerator().");

		} elseif ($this->autoRefresh && $this->expirator === NULL) {
			throw new InvalidStateException("Set expirator using setExpirator().");
		}

		$file = $this->getFilename($value);
		$handle = fopen($file, 'c+');
		if (!$handle) {
			throw new IOException("Unable to open or create file '$file'.");
		}
		flock($handle, LOCK_SH);
		$stat = fstat($handle);
		if (!$stat['size'] || ($this->autoRefresh && call_user_func($this->expirator, $value, $stat))) {
			ftruncate($handle, 0);
			flock($handle, LOCK_EX);
			$stat = fstat($handle);
			if (!$stat['size']) {
				$code = call_user_func($this->contentGenerator, $value);
				if (fwrite($handle, $code, strlen($code)) !== strlen($code)) {
					ftruncate($handle, 0);
					throw new IOException("Unable to write file '$file'.");
				}
			}
			flock($handle, LOCK_SH);
		}
		return new SharedFileLock($file, $handle);
	}

}
