<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\notification;

use phpbb\webpushnotifications\notification\method\webpush;
use phpbb_database_test_case;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once __DIR__ . '/../../../../../../tests/notification/base.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // load the composer dependencies for this extension

/**
 * @group slow
 */
class notification_method_webpush_test extends \phpbb_tests_notification_base
{
	/** @var string[] VAPID keys for testing purposes */
	public const VAPID_KEYS = [
		'publicKey'		=> 'BIcGkq1Ncj3a2-J0UW-1A0NETLjvxZzNLiYBiPVMKNjgwmwPi5jyK87VfS4FZn9n7S9pLMQzjV3LmFuOnRSOvmI',
		'privateKey'	=> 'SrlbBEVgibWmKHYbDPu4Y2XvDWPjeGcc9fC16jq01xU',
	];

	/** @var webpush */
	protected $notification_method_webpush;

	protected static function setup_extensions()
	{
		return ['phpbb/webpushnotifications'];
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/webpush_notification.type.post.xml');
	}

	protected function get_notification_methods()
	{
		return [
			'notification.method.phpbb.wpn.webpush',
		];
	}

	public static function setUpBeforeClass(): void
	{
		self::start_webpush_testing();
		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass(): void
	{
		self::stop_webpush_testing();
		parent::tearDownAfterClass();
	}

	protected static function start_webpush_testing(): void
	{
		// Stop first to ensure port is available
		self::stop_webpush_testing();

		$process = new \Symfony\Component\Process\Process(['phpBB/ext/phpbb/webpushnotifications/node_modules/.bin/web-push-testing', '--port', '9012', 'start']);
		$process->run();
		if (!$process->isSuccessful())
		{
			self::fail('Starting web push testing service failed: ' . $process->getErrorOutput());
		}
	}

	protected static function stop_webpush_testing(): void
	{
		$process = new \Symfony\Component\Process\Process(['phpBB/ext/phpbb/webpushnotifications/node_modules/.bin/web-push-testing', '--port', '9012', 'stop']);
		$process->run();
	}

	protected function setUp(): void
	{
		phpbb_database_test_case::setUp();

		global $phpbb_root_path, $phpEx;

		include_once(__DIR__ . '/../../../../../../tests/notification/ext/test/notification/type/test.' . $phpEx);

		global $db, $config, $user, $auth, $cache, $phpbb_container, $phpbb_dispatcher;

		$db = $this->db = $this->new_dbal();
		$config = $this->config = new \phpbb\config\config([
			'allow_privmsg'			=> true,
			'allow_bookmarks'		=> true,
			'allow_topic_notify'	=> true,
			'allow_forum_notify'	=> true,
			'allow_board_notifications'	=> true,
			'wpn_webpush_vapid_public'	=> self::VAPID_KEYS['publicKey'],
			'wpn_webpush_vapid_private'	=> self::VAPID_KEYS['privateKey'],
		]);
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user = $user;
		$this->user_loader = new \phpbb\user_loader($this->db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$auth = $this->auth = new \phpbb_mock_notifications_auth();
		$this->phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$phpbb_dispatcher = $this->phpbb_dispatcher;
		$cache_driver = new \phpbb\cache\driver\dummy();
		$cache = $this->cache = new \phpbb\cache\service(
			$cache_driver,
			$this->config,
			$this->db,
			$this->phpbb_dispatcher,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_container = $this->container = new ContainerBuilder();
		$loader     = new YamlFileLoader($phpbb_container, new FileLocator(__DIR__ . '/../../../../../../tests/notification/fixtures'));
		$loader->load('services_notification.yml');
		$phpbb_container->set('user_loader', $this->user_loader);
		$phpbb_container->set('user', $user);
		$phpbb_container->set('language', $lang);
		$phpbb_container->set('config', $this->config);
		$phpbb_container->set('dbal.conn', $this->db);
		$phpbb_container->set('auth', $auth);
		$phpbb_container->set('cache.driver', $cache_driver);
		$phpbb_container->set('cache', $cache);
		$phpbb_container->set('log', new \phpbb\log\dummy());
		$phpbb_container->set('text_formatter.utils', new \phpbb\textformatter\s9e\utils());
		$phpbb_container->set('dispatcher', $this->phpbb_dispatcher);
		$phpbb_container->setParameter('core.root_path', $phpbb_root_path);
		$phpbb_container->setParameter('core.php_ext', $phpEx);
		$phpbb_container->setParameter('tables.notifications', 'phpbb_notifications');
		$phpbb_container->setParameter('tables.user_notifications', 'phpbb_user_notifications');
		$phpbb_container->setParameter('tables.notification_types', 'phpbb_notification_types');
		$phpbb_container->setParameter('tables.notification_emails', 'phpbb_notification_emails');
		$phpbb_container->setParameter('tables.phpbb.wpn.notification_push', 'phpbb_wpn_notification_push');
		$phpbb_container->setParameter('tables.phpbb.wpn.push_subscriptions', 'phpbb_wpn_push_subscriptions');

		$this->notification_method_webpush = new webpush(
			$phpbb_container->get('config'),
			$phpbb_container->get('dbal.conn'),
			$phpbb_container->get('log'),
			$phpbb_container->get('user_loader'),
			$phpbb_container->get('user'),
			$phpbb_root_path,
			$phpEx,
			$phpbb_container->getParameter('tables.phpbb.wpn.notification_push'),
			$phpbb_container->getParameter('tables.phpbb.wpn.push_subscriptions')
		);

		$phpbb_container->set('notification.method.phpbb.wpn.webpush', $this->notification_method_webpush);

		$this->notifications = new \phpbb_notification_manager_helper(
			array(),
			array(),
			$this->container,
			$this->user_loader,
			$this->phpbb_dispatcher,
			$this->db,
			$this->cache,
			$lang,
			$this->user,
			'phpbb_notification_types',
			'phpbb_user_notifications'
		);

		$phpbb_container->set('notification_manager', $this->notifications);

		$phpbb_container->compile();

		$this->notifications->setDependencies($this->auth, $this->config);

		$types = array();
		foreach ($this->get_notification_types() as $type)
		{
			$class = $this->build_type($type);

			$types[$type] = $class;
		}

		$this->notifications->set_var('notification_types', $types);

		$methods = array();
		foreach ($this->get_notification_methods() as $method)
		{
			$class = $this->container->get($method);

			$methods[$method] = $class;
		}

		$this->notifications->set_var('notification_methods', $methods);
	}

	public function data_notification_webpush()
	{
		return [
			/**
			 * Normal post
			 *
			 * User => State description
			 *	2	=> Topic id=1 and id=2 subscribed, should receive a new topics post notification
			 *	3	=> Topic id=1 subscribed, should receive a new topic post notification
			 *	4	=> Topic id=1 subscribed, should receive a new topic post notification
			 *	5	=> Topic id=1 subscribed, post id=1 already notified, should receive a new topic post notification
			 *	6	=> Topic id=1 and forum id=1 subscribed, should receive a new topic/forum post notification
			 *	7	=> Forum id=1 subscribed, should NOT receive a new topic post but a forum post notification
			 *	8	=> Forum id=1 subscribed, post id=1 already notified, should NOT receive a new topic post but a forum post notification
			 */
			[
				'notification.type.post',
				[
					'forum_id'		=> '1',
					'post_id'		=> '2',
					'topic_id'		=> '1',
				],
				[
					2 => ['user_id' => '2'],
					3 => ['user_id' => '3'],
					4 => ['user_id' => '4'],
					5 => ['user_id' => '5'],
					6 => ['user_id' => '6'],
				],
			],
			[
				'notification.type.forum',
				[
					'forum_id'		=> '1',
					'post_id'		=> '3',
					'topic_id'		=> '1',
				],
				[
					6 => ['user_id' => '6'],
					7 => ['user_id' => '7'],
					8 => ['user_id' => '8']
				],
			],
			[
				'notification.type.post',
				[
					'forum_id'		=> '1',
					'post_id'		=> '4',
					'topic_id'		=> '2',
				],
				[
					2 => ['user_id' => '2'],
				],
			],
			[
				'notification.type.forum',
				[
					'forum_id'		=> '1',
					'post_id'		=> '5',
					'topic_id'		=> '2',
				],
				[
					6 => ['user_id' => '6'],
					7 => ['user_id' => '7'],
					8 => ['user_id' => '8'],
				],
			],
			[
				'notification.type.post',
				[
					'forum_id'		=> '2',
					'post_id'		=> '6',
					'topic_id'		=> '3',
				],
				[
				],
			],
			[
				'notification.type.forum',
				[
					'forum_id'		=> '2',
					'post_id'		=> '6',
					'topic_id'		=> '3',
				],
				[
				],
			],
		];
	}

	/**
	 * @dataProvider data_notification_webpush
	 */
	public function test_notification_webpush($notification_type, $post_data, $expected_users)
	{
		$post_data = array_merge([
			'post_time' => 1349413322,
			'poster_id' => 1,
			'topic_title' => '',
			'post_subject' => '',
			'post_username' => '',
			'forum_name' => '',
		],

			$post_data);
		$notification_options = [
			'item_id'			=> $post_data['post_id'],
			'item_parent_id'	=> $post_data['topic_id'],
		];

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertCount(0, $notified_users, 'Assert no user has been notified yet');

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users, 'Assert that expected users have been notified');

		$post_data['post_id']++;
		$notification_options['item_id'] = $post_data['post_id'];
		$post_data['post_time'] = 1349413323;

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users2 = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users2, 'Assert that expected users stay the same after replying to same topic');
	}

	/**
	 * @dataProvider data_notification_webpush
	 */
	public function test_get_subscription($notification_type, $post_data, $expected_users): void
	{
		$subscription_info = [];
		foreach ($expected_users as $user_id => $user_data)
		{
			$subscription_info[$user_id][] = $this->create_subscription_for_user($user_id);
		}

		// Create second subscription for first user ID passed
		if (count($expected_users))
		{
			$first_user_id = array_key_first($expected_users);
			$subscription_info[$first_user_id][] = $this->create_subscription_for_user($first_user_id);
		}

		$post_data = array_merge([
			'post_time' => 1349413322,
			'poster_id' => 1,
			'topic_title' => '',
			'post_subject' => '',
			'post_username' => '',
			'forum_name' => '',
		],

			$post_data);
		$notification_options = [
			'item_id'			=> $post_data['post_id'],
			'item_parent_id'	=> $post_data['topic_id'],
		];

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertCount(0, $notified_users, 'Assert no user has been notified yet');

		foreach ($expected_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertEmpty($messages);
		}

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users, 'Assert that expected users have been notified');

		foreach ($expected_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertNotEmpty($messages);
		}
	}

	protected function create_subscription_for_user($user_id): array
	{
		$client = new \GuzzleHttp\Client();
		try
		{
			$response = $client->request('POST', 'http://localhost:9012/subscribe', ['form_params' => [
				'applicationServerKey'	=> self::VAPID_KEYS['publicKey'],
			]]);
		}
		catch (\GuzzleHttp\Exception\GuzzleException $exception)
		{
			$this->fail('Failed getting subscription from web-push-testing client: ' . $exception->getMessage());
		}

		$subscription_return = \phpbb\webpushnotifications\json\sanitizer::decode((string) $response->getBody());
		$subscription_data = $subscription_return['data'];
		$this->assertNotEmpty($subscription_data['endpoint']);
		$this->assertStringStartsWith('http://localhost:9012/notify/', $subscription_data['endpoint']);
		$this->assertIsArray($subscription_data['keys']);

		// Add subscription data to admin user (user id 2)

		$push_subscriptions_table = $this->container->getParameter('tables.phpbb.wpn.push_subscriptions');

		$sql = 'INSERT INTO ' . $push_subscriptions_table  . ' ' . $this->db->sql_build_array('INSERT', [
				'user_id'		=> $user_id,
				'endpoint'		=> $subscription_data['endpoint'],
				'p256dh'		=> $subscription_data['keys']['p256dh'],
				'auth'			=> $subscription_data['keys']['auth'],
			]);
		$this->db->sql_query($sql);

		return $subscription_data;
	}

	protected function get_messages_for_subscription($client_hash): array
	{
		$client = new \GuzzleHttp\Client();
		try
		{
			$response = $client->request('POST', 'http://localhost:9012/get-notifications', ['form_params' => [
				'clientHash'	=> $client_hash,
			]]);
		}
		catch (\GuzzleHttp\Exception\GuzzleException $exception)
		{
			$this->fail('Failed getting messages from web-push-testing client');
		}

		$response_data = json_decode($response->getBody()->getContents(), true);
		$this->assertNotEmpty($response_data);
		$this->assertArrayHasKey('data', $response_data);
		$this->assertArrayHasKey('messages', $response_data['data']);

		return $response_data['data']['messages'];
	}

	/**
	 * @depends test_get_subscription
	 */
	public function test_export_data_with_migration(): void
	{
		global $phpbb_root_path, $phpEx;

		require_once __DIR__ . '/../../migrations/handle_subscriptions.php'; // load the extension migration

		$this->config['wpn_webpush_enable'] = '1';
		$this->config['webpush_enable'] = '';
		$this->config['webpush_vapid_public'] = '';
		$this->config['webpush_vapid_private'] = '';

		$config_db = new \phpbb\config\db($this->container->get('dbal.conn'), $this->container->get('cache.driver'), 'phpbb_config');
		foreach ($this->config as $config_name => $config_value)
		{
			$config_db->set($config_name, $config_value);
		}

		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($this->db);

		$phpbb_notification_push_data = [
			'COLUMNS'	=> [
				'notification_type_id'	=> ['USINT', 0],
				'item_id'				=> ['ULINT', 0],
				'item_parent_id'		=> ['ULINT', 0],
				'user_id'				=> ['ULINT', 0],
				'push_data'				=> ['MTEXT', ''],
				'notification_time'		=> ['TIMESTAMP', 0]
			],
			'PRIMARY_KEY' => ['notification_type_id', 'item_id', 'item_parent_id', 'user_id'],
		];
		$db_tools->sql_create_table('phpbb_notification_push', $phpbb_notification_push_data);

		$phpbb_push_subscriptions_data = [
			'COLUMNS'	=> [
				'subscription_id'	=> ['ULINT', null, 'auto_increment'],
				'user_id'			=> ['ULINT', 0],
				'endpoint'			=> ['TEXT', ''],
				'expiration_time'	=> ['TIMESTAMP', 0],
				'p256dh'			=> ['VCHAR', ''],
				'auth'				=> ['VCHAR', ''],
			],
			'PRIMARY_KEY' => ['subscription_id', 'user_id'],
		];
		$db_tools->sql_create_table('phpbb_push_subscriptions', $phpbb_push_subscriptions_data);

		$config_tool = new \phpbb\db\migration\tool\config($config_db);
		$this->container->set('migrator.tool.config', $config_tool);
		$tools_collection = new \phpbb\di\service_collection($this->container);
		$tools_collection->add('migrator.tool.config');

		$migrator = new \phpbb\db\migrator(
			$this->container,
			$config_db,
			$this->db,
			$db_tools,
			'phpbb_migrations',
			$phpbb_root_path,
			$phpEx,
			'phpbb_',
			$tools_collection,
			new \phpbb\db\migration\helper()
		);
		$migrator->create_migrations_table();

		$migration_class = '\phpbb\webpushnotifications\migrations\handle_subscriptions';
		$migrator->populate_migrations([$migration_class]);

		// Revert migration data
		while ($migrator->migration_state($migration_class) !== false)
		{
			$migrator->revert($migration_class);
		}

		// Test reverting data results
		$sql = 'SELECT config_name, config_value FROM ' . CONFIG_TABLE . "
			WHERE config_name IN('webpush_enable', 'webpush_vapid_public', 'webpush_vapid_private')";
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$exported_config_data = [];
		foreach ($data as $row)
		{
			$exported_config_data[$row['config_name']] = $row['config_value'];
		}

		$this->assertEquals($this->config['wpn_webpush_enable'], $exported_config_data['webpush_enable']);
		$this->assertEquals($this->config['wpn_webpush_vapid_public'], $exported_config_data['webpush_vapid_public']);
		$this->assertEquals($this->config['wpn_webpush_vapid_private'], $exported_config_data['webpush_vapid_private']);

		$sql = "SELECT * FROM phpbb_user_notifications
			WHERE method = '" . $this->db->sql_escape('notification.method.webpush') . "'";
		$result = $this->db->sql_query($sql);
		$this->assertGreaterThan(0, count($this->db->sql_fetchrowset($result)));
		$this->db->sql_freeresult($result);

		$sql = "SELECT * FROM phpbb_user_notifications
			WHERE method = '" . $this->db->sql_escape('notification.method.phpbb.wpn.webpush') . "'";
		$result = $this->db->sql_query($sql);
		$this->assertCount(0, $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);

		$this->assertEquals(
			$this->db->sql_fetchrowset($this->db->sql_query('SELECT * FROM phpbb_wpn_notification_push')),
			$this->db->sql_fetchrowset($this->db->sql_query('SELECT * FROM phpbb_notification_push'))
		);

		$this->assertEquals(
			$this->db->sql_fetchrowset($this->db->sql_query('SELECT * FROM phpbb_wpn_push_subscriptions')),
			$this->db->sql_fetchrowset($this->db->sql_query('SELECT * FROM phpbb_push_subscriptions'))
		);
	}
}
