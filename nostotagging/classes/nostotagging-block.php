<?php

/**
 * Base class for all tagging block classes.
 */
abstract class NostoTaggingBlock
{
	/**
	 * @var Context the context to create the tagging block for.
	 */
	protected $context;

	/**
	 * @var object the object used as data source for the tagging block.
	 */
	protected $object;

	/**
	 * Constructor.
	 *
	 * @param Context $context the context to create the tagging block for.
	 * @param object $object the object used as data source for the tagging block.
	 */
	public function __construct(Context $context, $object)
	{
		$this->context = $context;
		$this->object = $object;
	}

	/**
	 * Returns an array of required items in the block.
	 *
	 * @return array the list of required items.
	 */
	abstract public function getRequiredItems();

	/**
	 * Populates the tagging block with data from the
	 */
	abstract public function populate();

	/**
	 * Validates the tagging block, i.e. if all the required data is set.
	 *
	 * @return bool
	 */
	public function validate()
	{
		foreach ($this->getRequiredItems() as $item)
			if (empty($this->{$item}))
				return false;
		return true;
	}
}
