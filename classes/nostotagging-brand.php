<?php

/**
 * Block for tagging manufacturers (brands).
 */
class NostoTaggingBrand extends NostoTaggingBlock
{
	/**
	 * @var string the built brand string.
	 */
	public $brand_string;

	/**
	 * @inheritdoc
	 */
	public function getRequiredItems()
	{
		return array('brand_string');
	}

	/**
	 * @inheritdoc
	 */
	public function populate()
	{
		$manufacturer = $this->object;
		if (Validate::isLoadedObject($manufacturer))
			$this->brand_string = DS.$manufacturer->name;
	}
} 