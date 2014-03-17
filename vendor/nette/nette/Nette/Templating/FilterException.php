<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Templating
 */



/**
 * The exception occured during template compilation.
 *
 * @author     David Grudl
 * @package Nette\Templating
 */
class NTemplateException extends InvalidStateException
{
	/** @var string */
	public $sourceFile;

	/** @var int */
	public $sourceLine;


	public function __construct($message, $code = 0, $sourceLine = 0)
	{
		$this->sourceLine = (int) $sourceLine;
		parent::__construct($message, $code);
	}


	public function setSourceFile($file)
	{
		$this->sourceFile = (string) $file;
		$this->message = rtrim($this->message, '.') . " in " . str_replace(dirname(dirname($file)), '...', $file)
			. ($this->sourceLine ? ":$this->sourceLine" : '');
	}

}