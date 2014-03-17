<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Iterators
 */



/**
 * Callback iterator filter.
 *
 * @author     David Grudl
 * @package Nette\Iterators
 */
class NNCallbackFilterIterator extends FilterIterator
{
	/** @var callable */
	private $callback;


	public function __construct(Iterator $iterator, $callback)
	{
		parent::__construct($iterator);
		$this->callback = new NCallback($callback);
	}


	public function accept()
	{
		return $this->callback->invoke($this);
	}

}
