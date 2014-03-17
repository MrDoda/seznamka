<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 * @package Nette\Mail
 */



/**
 * Mailer interface.
 *
 * @author     David Grudl
 * @package Nette\Mail
 */
interface IMailer
{

	/**
	 * Sends email.
	 * @return void
	 */
	function send(NMail $mail);

}
