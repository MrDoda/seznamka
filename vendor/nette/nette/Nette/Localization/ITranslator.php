<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Localization
 */



/**
 * Translator adapter.
 *
 * @author     David Grudl
 * @package Nette\Localization
 */
interface ITranslator
{

	/**
	 * Translates the given string.
	 * @param  string   message
	 * @param  int      plural count
	 * @return string
	 */
	function translate($message, $count = NULL);

}
