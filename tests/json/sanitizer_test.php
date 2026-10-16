<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\json;

use phpbb\webpushnotifications\json\sanitizer;

class sanitizer_test extends \phpbb_test_case
{
	public function non_array_json_data(): array
	{
		return [
			'boolean' => ['true'],
			'number' => ['42'],
			'string' => ['"value"'],
			'null' => ['null'],
			'invalid' => ['not-json'],
		];
	}

	/**
	 * @dataProvider non_array_json_data
	 */
	public function test_decode_returns_empty_array_for_non_array_json(string $json): void
	{
		self::assertSame([], sanitizer::decode($json));
	}

	public function test_decode_sanitizes_array_json(): void
	{
		self::assertSame(['value' => '&lt;script&gt;'], sanitizer::decode('{"value":"<script>"}'));
	}
}
