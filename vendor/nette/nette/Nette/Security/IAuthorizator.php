<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Security
 */



/**
 * Authorizator checks if a given role has authorization
 * to access a given resource.
 *
 * @author     David Grudl
 * @package Nette\Security
 */
interface IAuthorizator
{
	/** Set type: all */
	const ALL = NULL;

	/** Permission type: allow */
	const ALLOW = TRUE;

	/** Permission type: deny */
	const DENY = FALSE;


	/**
	 * Performs a role-based authorization.
	 * @param  string  role
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	function isAllowed($role= self::ALL, $resource= self::ALL, $privilege= self::ALL);

}
