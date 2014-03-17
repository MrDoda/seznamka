<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application
 */



/**
 * The bi-directional router.
 *
 * @author     David Grudl
 * @package Nette\Application
 */
interface IRouter
{
	/** only matching route */
	const ONE_WAY = 1;

	/** HTTPS route */
	const SECURED = 2;

	/**
	 * Maps HTTP request to a Request object.
	 * @return NPresenterRequest|NULL
	 */
	function match(IHttpRequest $httpRequest);

	/**
	 * Constructs absolute URL from Request object.
	 * @return string|NULL
	 */
	function constructUrl(NPresenterRequest $appRequest, NUrl $refUrl);

}
