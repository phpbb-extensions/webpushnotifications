<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\subscription;

use phpbb\webpushnotifications\subscription\validator;

require_once __DIR__ . '/../../vendor/autoload.php';

class validator_test extends \phpbb_test_case
{
	private const VALID_PUBLIC_KEY = 'BGsX0fLhLEJH-Lzm5WOkQPJ3A32BLeszoPShOUXYmMKWT-NC4v4af5uO5-tKfA-eFivOM1drMV7Oy7ZAaDe_UfU';
	private const VALID_AUTH_TOKEN = 'AAAAAAAAAAAAAAAAAAAAAA';

	public function valid_key_data(): array
	{
		return [
			'unpadded' => [self::VALID_PUBLIC_KEY, self::VALID_AUTH_TOKEN],
			'padded' => [self::VALID_PUBLIC_KEY . '=', self::VALID_AUTH_TOKEN . '=='],
		];
	}

	/**
	 * @dataProvider valid_key_data
	 */
	public function test_valid_keys(string $public_key, string $auth_token): void
	{
		self::assertTrue(validator::is_valid($public_key, $auth_token));
	}

	public function invalid_key_data(): array
	{
		$off_curve_key = rtrim(strtr(base64_encode("\x04" . str_repeat("\x00", 64)), '+/', '-_'), '=');

		return [
			'invalid alphabet' => ['***', self::VALID_AUTH_TOKEN],
			'non-canonical encoding' => [self::VALID_PUBLIC_KEY . '==', self::VALID_AUTH_TOKEN],
			'short public key' => ['YQ', self::VALID_AUTH_TOKEN],
			'compressed public key' => [rtrim(strtr(base64_encode("\x02" . str_repeat("\x00", 32)), '+/', '-_'), '='), self::VALID_AUTH_TOKEN],
			'off-curve public key' => [$off_curve_key, self::VALID_AUTH_TOKEN],
			'short auth token' => [self::VALID_PUBLIC_KEY, 'YQ'],
		];
	}

	/**
	 * @dataProvider invalid_key_data
	 */
	public function test_invalid_keys(string $public_key, string $auth_token): void
	{
		self::assertFalse(validator::is_valid($public_key, $auth_token));
	}
}
