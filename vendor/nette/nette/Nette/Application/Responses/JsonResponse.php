<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application\Responses
 */



/**
 * JSON response used mainly for AJAX requests.
 *
 * @author     David Grudl
 *
 * @property-read array|\stdClass $payload
 * @property-read string $contentType
 * @package Nette\Application\Responses
 */
class NJsonResponse extends NObject implements IPresenterResponse
{
	/** @var array|\stdClass */
	private $payload;

	/** @var string */
	private $contentType;


	/**
	 * @param  array|\stdClass  payload
	 * @param  string    MIME content type
	 */
	public function __construct($payload, $contentType = NULL)
	{
		if (!is_array($payload) && !is_object($payload)) {
			throw new InvalidArgumentException("Payload must be array or object class, " . gettype($payload) . " given.");
		}
		$this->payload = $payload;
		$this->contentType = $contentType ? $contentType : 'application/json';
	}


	/**
	 * @return array|\stdClass
	 */
	public function getPayload()
	{
		return $this->payload;
	}


	/**
	 * Returns the MIME content type of a downloaded file.
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(IHttpRequest $httpRequest, IHttpResponse $httpResponse)
	{
		$httpResponse->setContentType($this->contentType);
		$httpResponse->setExpiration(FALSE);
		echo NJson::encode($this->payload);
	}

}
