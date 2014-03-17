<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application
 */



/**
 * Any response returned by presenter.
 *
 * @author     David Grudl
 * @package Nette\Application
 */
interface IPresenterResponse
{

	/**
	 * Sends response to output.
	 * @return void
	 */
	function send(IHttpRequest $httpRequest, IHttpResponse $httpResponse);

}
