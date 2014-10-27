<?php

/**
 * Helper class for formatting data.
 */
class NostoTaggingFormatter
{
	/**
	 * Formats price into Nosto format (e.g. 1000.99).
	 *
	 * @param string|int|float $price
	 * @return string
	 */
	public static function formatPrice($price)
	{
		return number_format((float)$price, 2, '.', '');
	}

	/**
	 * Formats date into Nosto format, i.e. Y-m-d.
	 *
	 * @param string $date
	 * @return string
	 */
	public static function formatDate($date)
	{
		return date('Y-m-d', strtotime((string)$date));
	}
}
