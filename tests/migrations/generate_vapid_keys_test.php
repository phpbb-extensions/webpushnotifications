<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\migrations;

use Minishlink\WebPush\VAPID;
use phpbb\config\config;
use phpbb\webpushnotifications\migrations\generate_vapid_keys;

class generate_vapid_keys_test extends \phpbb_test_case
{
	public function test_migration_definition(): void
	{
		$migration = $this->create_migration([]);

		self::assertSame(
			['\phpbb\webpushnotifications\migrations\add_webpush'],
			generate_vapid_keys::depends_on()
		);
		self::assertSame(
			[['custom', [[$migration, 'generate_keys']]]],
			$migration->update_data()
		);
	}

	public function test_empty_configuration_generates_valid_keys(): void
	{
		$config = new config([
			'wpn_webpush_vapid_public' => '',
			'wpn_webpush_vapid_private' => '',
		]);
		$migration = $this->create_migration([], $config);

		self::assertFalse($migration->effectively_installed());

		$migration->generate_keys();

		self::assertNotSame('', $config['wpn_webpush_vapid_public']);
		self::assertNotSame('', $config['wpn_webpush_vapid_private']);
		self::assertTrue($migration->effectively_installed());
		self::assertArrayHasKey('publicKey', VAPID::validate([
			'subject' => 'mailto:admin@example.com',
			'publicKey' => $config['wpn_webpush_vapid_public'],
			'privateKey' => $config['wpn_webpush_vapid_private'],
		]));
	}

	public function existing_key_data(): array
	{
		return [
			'complete pair' => ['existing-public', 'existing-private'],
			'public key only' => ['existing-public', ''],
			'private key only' => ['', 'existing-private'],
		];
	}

	/**
	 * @dataProvider existing_key_data
	 */
	public function test_existing_key_material_is_preserved(string $public_key, string $private_key): void
	{
		$config = new config([
			'wpn_webpush_vapid_public' => $public_key,
			'wpn_webpush_vapid_private' => $private_key,
		]);
		$migration = $this->create_migration([
			'publicKey' => 'generated-public',
			'privateKey' => 'generated-private',
		], $config);

		self::assertTrue($migration->effectively_installed());

		$migration->generate_keys();

		self::assertSame($public_key, $config['wpn_webpush_vapid_public']);
		self::assertSame($private_key, $config['wpn_webpush_vapid_private']);
	}

	public function invalid_generation_data(): array
	{
		return [
			'missing public key' => [['privateKey' => 'generated-private'], false],
			'missing private key' => [['publicKey' => 'generated-public'], false],
			'generation failure' => [[], true],
		];
	}

	/**
	 * @dataProvider invalid_generation_data
	 */
	public function test_generation_failure_leaves_configuration_empty(array $keys, bool $throw): void
	{
		$config = new config([
			'wpn_webpush_vapid_public' => '',
			'wpn_webpush_vapid_private' => '',
		]);
		$migration = $this->create_migration($keys, $config, $throw);

		$migration->generate_keys();

		self::assertSame('', $config['wpn_webpush_vapid_public']);
		self::assertSame('', $config['wpn_webpush_vapid_private']);
	}

	protected function create_migration(array $keys, config $config = null, bool $throw = false): testable_generate_vapid_keys
	{
		return new testable_generate_vapid_keys(
			$config ?? new config([]),
			$this->createMock(\phpbb\db\driver\driver_interface::class),
			$this->createMock(\phpbb\db\tools\tools_interface::class),
			'',
			'php',
			'phpbb_',
			$keys,
			$throw
		);
	}
}

class testable_generate_vapid_keys extends generate_vapid_keys
{
	/** @var array */
	private $keys;

	/** @var bool */
	private $throw;

	public function __construct(
		config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\db\tools\tools_interface $db_tools,
		string $phpbb_root_path,
		string $php_ext,
		string $table_prefix,
		array $keys,
		bool $throw
	)
	{
		parent::__construct($config, $db, $db_tools, $phpbb_root_path, $php_ext, $table_prefix);
		$this->keys = $keys;
		$this->throw = $throw;
	}

	protected function create_vapid_keys(): array
	{
		if ($this->throw)
		{
			throw new \RuntimeException('VAPID generation failed');
		}

		return $this->keys ?: parent::create_vapid_keys();
	}
}
