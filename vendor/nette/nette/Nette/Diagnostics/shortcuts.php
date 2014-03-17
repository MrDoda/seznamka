<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */



/**
 * NDebugger::dump shortcut.
 */
function dump($var)
{
	foreach (func_get_args() as $arg) {
		NDebugger::dump($arg);
	}
	return $var;
}
