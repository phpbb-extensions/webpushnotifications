<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\migrations;

use Minishlink\WebPush\VAPID;
use phpbb\db\migration\migration;

class generate_vapid_keys extends migration
{
	public function effectively_installed(): bool
	{
		return !empty($this->config['wpn_webpush_vapid_public'])
			|| !empty($this->config['wpn_webpush_vapid_private']);
	}

	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_webpush'];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'generate_keys']]],
		];
	}

	/**
	 * Generate and store a VAPID key pair when no key material exists.
	 */
	public function generate_keys(): void
	{
		if ($this->effectively_installed())
		{
			return;
		}

		try
		{
			$keys = $this->create_vapid_keys();
		}
		catch (\Throwable $e)
		{
			// Keep installation usable. Keys can still be generated from the ACP.
			return;
		}

		if (empty($keys['publicKey']) || empty($keys['privateKey']))
		{
			return;
		}

		$this->config->set('wpn_webpush_vapid_public', $keys['publicKey']);
		$this->config->set('wpn_webpush_vapid_private', $keys['privateKey']);
	}

	/**
	 * Create a VAPID key pair.
	 *
	 * @return string[]
	 * @throws \ErrorException
	 */
	protected function create_vapid_keys(): array
	{
		if (!class_exists(VAPID::class))
		{
			require_once __DIR__ . '/../vendor/autoload.php';
		}

		return VAPID::createVapidKeys();
	}
}
