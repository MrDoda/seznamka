<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Caching\Storages
 */



/**
 * PHP files cache storage.
 *
 * @author     David Grudl
 * @package Nette\Caching\Storages
 */
class NPhpFileStorage extends NFileStorage
{
	/** @var string */
	public $hint;


	/**
	 * Reads cache data from disk.
	 * @param  array
	 * @return mixed
	 */
	protected function readData($meta)
	{
		return array(
			'file' => $meta[self::FILE],
			'handle' => $meta[self::HANDLE],
		);
	}


	/**
	 * Returns file name.
	 * @param  string
	 * @return string
	 */
	protected function getCacheFile($key)
	{
		return parent::getCacheFile(substr_replace(
			$key,
			trim(strtr($this->hint, '\\/@', '.._'), '.') . '-',
			strpos($key, NCache::NAMESPACE_SEPARATOR) + 1,
			0
		)) . '.php';
	}

}
