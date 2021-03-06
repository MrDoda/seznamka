<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application\Responses
 */



/**
 * Forwards to new request.
 *
 * @author     David Grudl
 *
 * @property-read NPresenterRequest $request
 * @package Nette\Application\Responses
 */
class NForwardResponse extends NObject implements IPresenterResponse
{
	/** @var NPresenterRequest */
	private $request;


	public function __construct(NPresenterRequest $request)
	{
		$this->request = $request;
	}


	/**
	 * @return NPresenterRequest
	 */
	public function getRequest()
	{
		return $this->request;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(IHttpRequest $httpRequest, IHttpResponse $httpResponse)
	{
	}

}
