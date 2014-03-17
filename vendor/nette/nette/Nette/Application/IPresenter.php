<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Application
 */



/**
 * Presenter converts Request to IResponse.
 *
 * @author     David Grudl
 * @package Nette\Application
 */
interface IPresenter
{

	/**
	 * @return IPresenterResponse
	 */
	function run(NPresenterRequest $request);

}
