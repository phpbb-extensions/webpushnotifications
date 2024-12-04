<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\json;

use phpbb\request\type_cast_helper;

/**
 * JSON sanitizer class
 */
class sanitizer
{
	/**
	 * Sanitize json data
	 *
	 * @param array $data Data to sanitize
	 *
	 * @return array Sanitized data
	 */
	public static function sanitize(array $data) : array
	{
		if (!empty($data))
		{
			$json_sanitizer = function (&$value)
			{
				$type_cast_helper = new type_cast_helper();
				$type_cast_helper->set_var($value, $value, gettype($value), true);
			};
			array_walk_recursive($data, $json_sanitizer);
		}

		return $data;
	}

	/**
	 * Decode and sanitize json data
	 *
	 * @param string $json JSON data string
	 *
	 * @return array Data array
	 */
	public static function decode(string $json) : array
	{
		$data = json_decode($json, true);
		return !empty($data) ? self::sanitize($data) : [];
	}

	/**
	 * Remove emoji from a string
	 * Basic emoji (U+1F300 to U+1F64F)
	 * Transport and map symbols (U+1F680 to U+1F6FF)
	 * Miscellaneous symbols and pictographs (U+1F300 to U+1F5FF)
	 * Additional emoji symbols (U+1F600 to U+1F64F)
	 *
	 * @param string $string
	 * @return string
	 */
	public static function strip_emoji(string $string) : string
	{
		return preg_replace(
			'/[\x{1F000}-\x{1F9FF}]|[\x{2600}-\x{27FF}]|[\x{1F300}-\x{1F64F}]|[\x{1F680}-\x{1F6FF}]/u',
			'',
			html_entity_decode($string, ENT_QUOTES, 'UTF-8')
		);
	}
}
