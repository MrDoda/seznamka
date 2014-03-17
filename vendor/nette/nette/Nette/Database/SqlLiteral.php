<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Database
 */



/**
 * SQL literal value.
 *
 * @author     Jakub Vrana
 * @package Nette\Database
 */
class NSqlLiteral extends NObject
{
	/** @var string */
	private $value = '';


	public function __construct($value)
	{
		$this->value = (string) $value;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

}
