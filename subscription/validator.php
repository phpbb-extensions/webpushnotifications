<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\subscription;

use Base64Url\Base64Url;
use Brick\Math\BigInteger;
use Jose\Component\Core\Util\Ecc\NistCurve;
use Minishlink\WebPush\Utils;

/**
 * Web Push subscription key validator
 */
class validator
{
	/**
	 * Validate Web Push subscription keys
	 *
	 * @param string $public_key P-256 public key encoded with Base64URL
	 * @param string $auth_token Authentication secret encoded with Base64URL
	 * @return bool
	 */
	public static function is_valid(string $public_key, string $auth_token): bool
	{
		try
		{
			$decoded_public_key = self::decode_base64url($public_key);
			$decoded_auth_token = self::decode_base64url($auth_token);

			if (Utils::safeStrlen($decoded_public_key) !== 65
				|| $decoded_public_key[0] !== "\x04"
				|| Utils::safeStrlen($decoded_auth_token) !== 16)
			{
				return false;
			}

			[$x, $y] = Utils::unserializePublicKey($decoded_public_key);
			NistCurve::curve256()->getPublicKeyFrom(
				BigInteger::fromBase(bin2hex($x), 16),
				BigInteger::fromBase(bin2hex($y), 16)
			);

			return true;
		}
		catch (\InvalidArgumentException|\RuntimeException $exception)
		{
			return false;
		}
	}

	/**
	 * Strictly decode a Base64URL value
	 *
	 * @param string $value Base64URL encoded value
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	private static function decode_base64url(string $value): string
	{
		if (!preg_match('/^[A-Za-z0-9_-]+={0,2}$/D', $value))
		{
			throw new \InvalidArgumentException('Invalid Base64URL value');
		}

		$decoded = Base64Url::decode($value);
		if (Base64Url::encode($decoded) !== rtrim($value, '='))
		{
			throw new \InvalidArgumentException('Non-canonical Base64URL value');
		}

		return $decoded;
	}
}
