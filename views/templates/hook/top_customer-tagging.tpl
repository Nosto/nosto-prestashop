{*
* 2013-2015 Nosto Solutions Ltd
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
* @copyright 2013-2015 Nosto Solutions Ltd
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if isset($nosto_customer) && is_object($nosto_customer)}
	<div class="nosto_customer" style="display:none">
		<span class="first_name">{$nosto_customer->first_name|escape:'htmlall':'UTF-8'}</span>
		<span class="last_name">{$nosto_customer->last_name|escape:'htmlall':'UTF-8'}</span>
		<span class="email">{$nosto_customer->email|escape:'htmlall':'UTF-8'}</span>
	</div>
{/if}
