<?php

/**
 * Helper class for doing API requests to the Nosto REST API.
 */
class NostoTaggingApiRequest extends NostoTaggingHttpRequest
{
	const PATH_ORDER_TAGGING = '/visits/order/confirm/{m}/{cid}';
	const PATH_UNMATCHED_ORDER_TAGGING = '/visits/order/unmatched/{m}';
	const PATH_SIGN_UP = '/accounts/create';
	const PATH_SSO_AUTH = '/users/sso/{email}';
	const PATH_PRODUCT_RE_CRAWL = '/products/recrawl';

	const TOKEN_SIGN_UP = 'JRtgvoZLMl4NPqO9XWhRdvxkTMtN82ITTJij8U7necieJPCvjtZjm5C4fpNrYJ81';

	/**
	 * @var string base url for the nosto api.
	 */
	public static $base_url = 'https://api.nosto.com';

	/**
	 * Setter for the end point path, e.g. one of the PATH_ constants.
	 * The API base url is always prepended.
	 *
	 * @param string $path the endpoint path (use PATH_ constants).
	 */
	public function setPath($path)
	{
		$this->setUrl(self::$base_url.$path);
	}
}
