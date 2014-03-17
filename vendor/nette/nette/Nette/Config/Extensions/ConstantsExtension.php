<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Config\Extensions
 */



/**
 * Constant definitions.
 *
 * @author     David Grudl
 * @package Nette\Config\Extensions
 */
class NConstantsExtension extends NConfigCompilerExtension
{

	public function afterCompile(NPhpClassType $class)
	{
		foreach ($this->getConfig() as $name => $value) {
			$class->methods['initialize']->addBody('define(?, ?);', array($name, $value));
		}
	}

}
