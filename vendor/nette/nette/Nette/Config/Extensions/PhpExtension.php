<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Config\Extensions
 */



/**
 * PHP directives definition.
 *
 * @author     David Grudl
 * @package Nette\Config\Extensions
 */
class NPhpExtension extends NConfigCompilerExtension
{

	public function afterCompile(NPhpClassType $class)
	{
		$initialize = $class->methods['initialize'];
		foreach ($this->getConfig() as $name => $value) {
			if (!is_scalar($value)) {
				throw new InvalidStateException("Configuration value for directive '$name' is not scalar.");

			} elseif ($name === 'include_path') {
				$initialize->addBody('set_include_path(?);', array(str_replace(';', PATH_SEPARATOR, $value)));

			} elseif ($name === 'ignore_user_abort') {
				$initialize->addBody('ignore_user_abort(?);', array($value));

			} elseif ($name === 'max_execution_time') {
				$initialize->addBody('set_time_limit(?);', array($value));

			} elseif ($name === 'date.timezone') {
				$initialize->addBody('date_default_timezone_set(?);', array($value));

			} elseif (function_exists('ini_set')) {
				$initialize->addBody('ini_set(?, ?);', array($name, $value));

			} elseif (ini_get($name) != $value) { // intentionally ==
				throw new NotSupportedException('Required function ini_set() is disabled.');
			}
		}
	}

}
