<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application\UI
 */



/**
 * Component with ability to save and load its state.
 *
 * @author     David Grudl
 * @package Nette\Application\UI
 */
interface IStatePersistent
{

	/**
	 * Loads state informations.
	 * @return void
	 */
	function loadState(array $params);

	/**
	 * Saves state informations for next request.
	 * @return void
	 */
	function saveState(array & $params);

}
