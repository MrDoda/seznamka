<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Database
 */



/**
 * Represents a single table row.
 *
 * @author     David Grudl
 * @package Nette\Database
 */
class NRow extends NArrayHash
{

	public function __construct(NStatement $statement)
	{
		$data = array();
		foreach ((array) $this as $key => $value) {
			$data[$key] = $value;
			unset($this->$key);
		}
		foreach ($statement->normalizeRow($data) as $key => $value) {
			$this->$key = $value;
		}
	}


	/**
	 * Returns a item.
	 * @param  mixed  key or index
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		if (is_int($key)) {
			$arr = array_slice((array) $this, $key, 1);
			if (!$arr) {
				trigger_error('Undefined offset: ' . __CLASS__ . "[$key]", E_USER_NOTICE);
			}
			return current($arr);
		}
		return $this->$key;
	}


	public function offsetExists($key)
	{
		if (is_int($key)) {
			return (bool) array_slice((array) $this, $key, 1);
		}
		return parent::offsetExists($key);
	}

}
