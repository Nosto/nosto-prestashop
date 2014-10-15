<?php

/**
 * Base class for all tagging block classes.
 */
abstract class NostoTaggingBlock
{
	/**
	 * @var NostoTagging the nosto module.
	 */
	protected $module;

	/**
	 * Constructor.
	 *
	 * @param NostoTagging $module the nosto module.
	 */
	public function __construct(NostoTagging $module)
	{
		$this->module = $module;
	}

	/**
	 * Returns an array of required items in the block.
	 *
	 * @return array the list of required items.
	 */
	abstract public function getRequiredItems();

	/**
	 * Checks if this tagging block is empty, i.e. if all the required data is not set.
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		foreach ($this->getRequiredItems() as $item)
			if (empty($this->{$item}))
				return true;
		return false;
	}
}
