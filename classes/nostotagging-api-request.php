<?php
/**
 * 2013-2014 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2014 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for doing API requests to the Nosto REST API.
 */
class NostoTaggingApiRequest extends NostoTaggingHttpRequest
{
	const PATH_ORDER_TAGGING = '/visits/order/confirm/{m}/{cid}';
	const PATH_UNMATCHED_ORDER_TAGGING = '/visits/order/unmatched/{m}';
	const PATH_SIGN_UP = '/accounts/create/{lang}';
	const PATH_SSO_AUTH = '/users/sso/{email}';
	const PATH_PRODUCT_RE_CRAWL = '/products/recrawl';
	const PATH_ACCOUNT_DELETED = ''; // todo

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
