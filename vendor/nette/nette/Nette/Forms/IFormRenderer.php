<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Forms
 */



/**
 * Defines method that must implement form renderer.
 *
 * @author     David Grudl
 * @package Nette\Forms
 */
interface IFormRenderer
{

	/**
	 * Provides complete form rendering.
	 * @return string
	 */
	function render(NForm $form);

}
