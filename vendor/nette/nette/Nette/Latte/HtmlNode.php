<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Latte
 */



/**
 * HTML element node.
 *
 * @author     David Grudl
 * @package Nette\Latte
 */
class NHtmlNode extends NObject
{
	/** @var string */
	public $name;

	/** @var bool */
	public $isEmpty = FALSE;

	/** @var array */
	public $attrs = array();

	/** @var array */
	public $macroAttrs = array();

	/** @var bool */
	public $closing = FALSE;

	/** @var string */
	public $attrCode;

	/** @var int */
	public $offset;


	public function __construct($name)
	{
		$this->name = $name;
	}

}
