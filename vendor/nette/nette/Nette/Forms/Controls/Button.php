<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Forms\Controls
 */



/**
 * Push button control with no default behavior.
 *
 * @author     David Grudl
 * @package Nette\Forms\Controls
 */
class NButton extends NFormControl
{

	/**
	 * @param  string  caption
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct($caption);
		$this->control->type = 'button';
	}


	/**
	 * Bypasses label generation.
	 * @return void
	 */
	public function getLabel($caption = NULL)
	{
		return NULL;
	}


	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return NHtml
	 */
	public function getControl($caption = NULL)
	{
		$control = parent::getControl();
		$control->value = $this->translate($caption === NULL ? $this->caption : $caption);
		return $control;
	}

}
