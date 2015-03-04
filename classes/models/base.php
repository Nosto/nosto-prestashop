<?php
/**
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
 */

/**
 * Base model for all tagging model classes.
 */
abstract class NostoTaggingModel
{
	/**
	 * Returns an array of required items in the block.
	 *
	 * @return array the list of required items.
	 */
	abstract public function getRequiredItems();

	/**
	 * Validates the tagging block, i.e. if all the required data is set.
	 *
	 * @param array $attributes optional list of attributes to validate (used to validate only specific attributes).
	 * @return bool
	 */
	public function validate(array $attributes = array())
	{
		foreach ($this->getRequiredItems() as $attribute)
			if ((empty($attributes) || in_array($attribute, $attributes)) && empty($this->{$attribute}))
				return false;
		return true;
	}
}
