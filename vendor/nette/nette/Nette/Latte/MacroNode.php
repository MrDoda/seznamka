<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Latte
 */



/**
 * Macro element node.
 *
 * @author     David Grudl
 * @package Nette\Latte
 */
class NMacroNode extends NObject
{
	const PREFIX_INNER = 'inner',
		PREFIX_TAG = 'tag';

	/** @var IMacro */
	public $macro;

	/** @var string */
	public $name;

	/** @var bool */
	public $isEmpty = FALSE;

	/** @var string  raw arguments */
	public $args;

	/** @var string  raw modifier */
	public $modifiers;

	/** @var bool */
	public $closing = FALSE;

	/** @var NMacroTokenizer */
	public $tokenizer;

	/** @var NMacroNode */
	public $parentNode;

	/** @var string */
	public $openingCode;

	/** @var string */
	public $closingCode;

	/** @var string */
	public $attrCode;

	/** @var string */
	public $content;

	/** @var \stdClass  user data */
	public $data;

	/** @var NHtmlNode  for n:attr macros */
	public $htmlNode;

	/** @var string  for n:attr macros (NULL, PREFIX_INNER, PREFIX_TAG) */
	public $prefix;

	public $saved;


	public function __construct(IMacro $macro, $name, $args = NULL, $modifiers = NULL, NMacroNode $parentNode = NULL, NHtmlNode $htmlNode = NULL, $prefix = NULL)
	{
		$this->macro = $macro;
		$this->name = (string) $name;
		$this->modifiers = (string) $modifiers;
		$this->parentNode = $parentNode;
		$this->htmlNode = $htmlNode;
		$this->prefix = $prefix;
		$this->tokenizer = new NMacroTokenizer($this->args);
		$this->data = new stdClass;
		$this->setArgs($args);
	}


	public function setArgs($args)
	{
		$this->args = (string) $args;
		$this->tokenizer->tokenize($this->args);
	}

}
