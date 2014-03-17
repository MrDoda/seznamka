<?php



/**
 * Users authenticator.
 */
class Authenticator extends NObject implements IAuthenticator
{
	const
		TABLE_NAME = 'users',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'username',
		COLUMN_PASSWORD = 'password',
		COLUMN_ROLE = 'role',
		PASSWORD_MAX_LENGTH = 4096;

	/** @var NConnection */
	private $database;


	public function __construct(NConnection $database)
	{
		$this->database = $database;
	}


	/**
	 * Performs an authentication.
	 * @return NIdentity
	 * @throws NAuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

		if (!$row) {
			throw new NAuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		} elseif (!self::verifyPassword($password, $row[self::COLUMN_PASSWORD])) {
			throw new NAuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD]);
		return new NIdentity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
	}


	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public static function hashPassword($password, $options = NULL)
	{
		if ($password === NStrings::upper($password)) { // perhaps caps lock is on
			$password = NStrings::lower($password);
		}
		$password = substr($password, 0, self::PASSWORD_MAX_LENGTH);
		$options = ($tmp=$options) ? $tmp : implode('$', array(
			'algo' => '$2a', // blowfish
			'cost' => '07',
			'salt' => NStrings::random(22),
		));
		return crypt($password, $options);
	}


	/**
	 * Verifies that a password matches a hash.
	 * @return bool
	 */
	public static function verifyPassword($password, $hash)
	{
		return self::hashPassword($password, $hash) === $hash
			|| (PHP_VERSION_ID >= 50307 && substr($hash, 0, 3) === '$2a' && self::hashPassword($password, $tmp = '$2x' . substr($hash, 3)) === $tmp);
	}

}
