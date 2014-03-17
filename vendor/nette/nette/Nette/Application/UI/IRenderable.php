<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application\UI
 */



/**
 * Component with ability to repaint.
 *
 * @author     David Grudl
 * @package Nette\Application\UI
 */
interface IRenderable
{

	/**
	 * Forces control to repaint.
	 * @return void
	 */
	function invalidateControl();

	/**
	 * Is required to repaint the control?
	 * @return bool
	 */
	function isControlInvalid();

}
