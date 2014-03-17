<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application\Responses
 */



/**
 * String output response.
 *
 * @author     David Grudl
 *
 * @property-read mixed $source
 * @package Nette\Application\Responses
 */
class NTextResponse extends NObject implements IPresenterResponse
{
	/** @var mixed */
	private $source;


	/**
	 * @param  mixed  renderable variable
	 */
	public function __construct($source)
	{
		$this->source = $source;
	}


	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(IHttpRequest $httpRequest, IHttpResponse $httpResponse)
	{
		if ($this->source instanceof ITemplate) {
			$this->source->render();

		} else {
			echo $this->source;
		}
	}

}
