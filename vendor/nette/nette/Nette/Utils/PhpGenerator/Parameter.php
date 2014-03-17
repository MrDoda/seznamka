<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Utils\PhpGenerator
 */



/**
 * Method parameter description.
 *
 * @author     David Grudl
 *
 * @method Parameter setName(string $name)
 * @method Parameter setReference(bool $on)
 * @method Parameter setTypeHint(string $class)
 * @method Parameter setOptional(bool $on)
 * @method Parameter setDefaultValue(mixed $value)
 * @package Nette\Utils\PhpGenerator
 */
class NPhpParameter extends NObject
{
	/** @var string */
	public $name;

	/** @var bool */
	public $reference;

	/** @var string */
	public $typeHint;

	/** @var bool */
	public $optional;

	/** @var mixed */
	public $defaultValue;


	public function __call($name, $args)
	{
		return NObjectMixin::callProperty($this, $name, $args);
	}

}
