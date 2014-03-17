<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Iterators
 */



/**
 * Applies the callback to the elements of the inner iterator.
 *
 * @author     David Grudl
 * @package Nette\Iterators
 */
class NMapIterator extends IteratorIterator
{
	/** @var callable */
	private $callback;


	public function __construct(Traversable $iterator, $callback)
	{
		parent::__construct($iterator);
		$this->callback = new NCallback($callback);
	}


	public function current()
	{
		return $this->callback->invoke(parent::current(), parent::key());
	}

}
