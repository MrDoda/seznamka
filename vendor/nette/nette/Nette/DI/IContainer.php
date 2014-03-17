<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\DI
 */



/**
 * @deprecated
 * @package Nette\DI
 */
interface IDIContainer
{

	/**
	 * Adds the service to the container.
	 * @param  string
	 * @param  mixed  object, class name or callback
	 * @return void
	 */
	function addService($name, $service);

	/**
	 * Gets the service object.
	 * @param  string
	 * @return mixed
	 */
	function getService($name);

	/**
	 * Removes the service from the container.
	 * @param  string
	 * @return void
	 */
	function removeService($name);

	/**
	 * Does the service exist?
	 * @return bool
	 */
	function hasService($name);

}
