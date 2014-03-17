<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\DI
 */



/**
 * Service not found exception.
 * @package Nette\DI
 */
class NMissingServiceException extends InvalidStateException
{
}


/**
 * Service creation exception.
 * @package Nette\DI
 */
class NServiceCreationException extends InvalidStateException
{
}
