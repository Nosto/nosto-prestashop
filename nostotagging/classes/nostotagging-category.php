<?php

/**
 * Block for tagging categories.
 */
class NostoTaggingCategory extends NostoTaggingBlock
{
	/**
	 * @var string the built category string.
	 */
	public $category_string;

	/**
	 * @inheritdoc
	 */
	public function getRequiredItems()
	{
		return array('category_string');
	}

	/**
	 * Populates the block with data from the category.
	 *
	 * @param Category $category the category object.
	 */
	public function populate(Category $category)
	{
		if (Validate::isLoadedObject($category))
			$this->category_string = self::buildCategoryString($category->id, $this->module->context->language->id);
	}

	/**
	 * Builds a tagging string of the given category including all its parent categories.
	 *
	 * @param int $category_id
	 * @param int $lang_id
	 * @return string
	 */
	public static function buildCategoryString($category_id, $lang_id)
	{
		$category_list = array();

		$category = new Category((int)$category_id, $lang_id);

		if (Validate::isLoadedObject($category) && (int)$category->active === 1)
			foreach ($category->getParentsCategories($lang_id) as $parent_category)
				if (isset($parent_category['name'], $parent_category['active']) && (int)$parent_category['active'] === 1)
					$category_list[] = (string)$parent_category['name'];

		if (empty($category_list))
			return '';

		return DS.implode(DS, array_reverse($category_list));
	}
}
