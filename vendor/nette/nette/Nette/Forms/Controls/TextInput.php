<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Forms\Controls
 */



/**
 * Single line text input control.
 *
 * @author     David Grudl
 * @property-write $type
 * @package Nette\Forms\Controls
 */
class NTextInput extends NTextBase
{

	/**
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 */
	public function __construct($label = NULL, $cols = NULL, $maxLength = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'text';
		$this->control->size = $cols;
		$this->control->maxlength = $maxLength;
	}


	/**
	 * Changes control's type attribute.
	 * @param  string
	 * @return self
	 */
	public function setType($type)
	{
		$this->control->type = $type;
		return $this;
	}


	/** @deprecated */
	public function setPasswordMode($mode = TRUE)
	{
		$this->control->type = $mode ? 'password' : 'text';
		return $this;
	}


	/**
	 * Generates control's HTML element.
	 * @return NHtml
	 */
	public function getControl()
	{
		$control = parent::getControl();
		foreach ($this->getRules() as $rule) {
			if ($rule->isNegative || $rule->type !== NRule::VALIDATOR) {

			} elseif ($rule->operation === NForm::RANGE && $control->type !== 'text') {
				list($control->min, $control->max) = $rule->arg;

			} elseif ($rule->operation === NForm::PATTERN) {
				$control->pattern = $rule->arg;
			}
		}
		if ($control->type !== 'password') {
			$control->value = $this->getValue() === '' ? $this->translate($this->emptyValue) : $this->value;
		}
		return $control;
	}

}
