<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application\UI
 */



/**
 * Signal exception.
 *
 * @author     David Grudl
 * @package Nette\Application\UI
 */
class NBadSignalException extends NBadRequestException
{
	/** @var int */
	protected $defaultCode = 403;

}
