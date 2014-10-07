<?php

/**
 * Helper class that represents a oauth2 access token.
 */
class NostoTaggingOAuth2Token
{
	/**
	 * @var string the access token string.
	 */
	public $access_token;

	/**
	 * @var string the type of token, e.g. "bearer".
	 */
	public $token_type;

	/**
	 * @var int the amount of time this token is valid for.
	 */
	public $expires_in;

	/**
	 * Creates a new token instance and populates it with the given data.
	 *
	 * @param array $data the data to put in the token.
	 * @return NostoTaggingOAuth2Token
	 */
	public static function create(array $data)
	{
		$token = new NostoTaggingOAuth2Token();
		foreach ($data as $key => $value)
			if (isset($token->{$key}))
				$token->{$key} = $value;
		return $token;
	}
}