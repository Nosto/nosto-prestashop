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
 * Model for tagging categories.
 */
class NostoTaggingCategory extends NostoTaggingModel
{
	/**
	 * @var string the built category string.
	 */
	protected $category;

	/**
	 * Sets up this DTO.
	 *
	 * @param Category|CategoryCore $category the PS category model.
	 * @param Context|null $context the PS context model.
	 */
	public function loadData(Category $category, Context $context = null)
	{
		if (!Validate::isLoadedObject($category))
			return;

		if (is_null($context))
			$context = Context::getContext();

		/** @var LanguageCore $language */
		$language = $context->language;
		$this->category = self::buildCategoryString($category->id, $language->id);

		$this->dispatchHookActionObjectLoadAfter(array(
			'nosto_category' => $this,
			'category' => $category,
			'context' => $context
		));
	}

	/**
	 * Builds a tagging string of the given category including all its parent categories.
	 *
	 * @param int $id_category
	 * @param int $id_lang
	 * @return string
	 */
	public static function buildCategoryString($id_category, $id_lang)
	{
		$category_list = array();

		/** @var CategoryCore $category */
		$category = new Category((int)$id_category, $id_lang);

		if (Validate::isLoadedObject($category) && (int)$category->active === 1)
			foreach ($category->getParentsCategories($id_lang) as $parent_category)
				if (isset($parent_category['name'], $parent_category['active']) && (int)$parent_category['active'] === 1)
					$category_list[] = (string)$parent_category['name'];

		if (empty($category_list))
			return '';

		return DS.implode(DS, array_reverse($category_list));
	}

	/**
	 * Returns the category value.
	 *
	 * @return string the category.
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * Sets the category string.
	 *
	 * The category must be a non-empty string, that starts with a "/" character.
	 *
	 * Usage:
	 * $object->setCategory('/Clothes/Winter/Coats');
	 *
	 * @param string $category the category string.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setCategory($category)
	{
		if (!is_string($category) || empty($category))
			throw new InvalidArgumentException('Category must be a non-empty string value.');
		if ($category[0] !== DS)
			throw new InvalidArgumentException(sprintf('Category string must start with a %s character.', DS));

		$this->category = $category;
	}
}
