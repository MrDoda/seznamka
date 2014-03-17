<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Utils\PhpGenerator
 */



/**
 * PHP literal value.
 *
 * @author     David Grudl
 * @package Nette\Utils\PhpGenerator
 */
class NPhpLiteral
{
	/** @var string */
	public $value = '';


	public function __construct($value)
	{
		$this->value = (string) $value;
	}

}
