<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\notification\method
{
	function phpbb_get_banned_user_ids($user_ids)
	{
		return $GLOBALS['phpbb_wpn_test_banned_users'] ?? \phpbb_get_banned_user_ids($user_ids);
	}
}

namespace phpbb\webpushnotifications\tests\notification
{
	class webpush_branches_test extends \phpbb_test_case
	{
		protected function tearDown(): void
		{
			unset($GLOBALS['phpbb_wpn_test_banned_users']);
			parent::tearDown();
		}

		public function test_all_banned_users_empty_queue_before_delivery(): void
		{
			$GLOBALS['phpbb_wpn_test_banned_users'] = [2];
			$method = $this->create_method();
			$notification = $this->create_notification(2);
			$method->add_to_queue($notification);

			$method->send_queued_notifications();

			self::assertSame([], $method->get_queue());
		}

		public function test_invalid_subscription_is_removed_and_logged(): void
		{
			$GLOBALS['phpbb_wpn_test_banned_users'] = [];
			$log = $this->createMock(\phpbb\log\log_interface::class);
			$log->expects(self::once())->method('add')->with(
				'user',
				2,
				'127.0.0.1',
				'LOG_WEBPUSH_SUBSCRIPTION_REMOVED'
			);
			$user_loader = $this->createMock(\phpbb\user_loader::class);
			$user_loader->method('get_user')->with(2)->willReturn([
				'user_id' => 2,
				'user_type' => USER_NORMAL,
				'user_inactive_reason' => 0,
				'user_form_salt' => 'salt',
				'user_ip' => '127.0.0.1',
				'username' => 'Tester',
			]);
			$method = $this->create_method($log, $user_loader, str_repeat('x', 5000));
			$method->subscriptions = [
				2 => [[
					'subscription_id' => 9,
					'endpoint' => 'https://updates.push.services.mozilla.com/test',
					'p256dh' => 'invalid',
					'auth' => 'invalid',
				]],
			];
			$method->add_to_queue($this->create_notification(2));
			$method->set_push_token(1, 10, 2, 'token');

			$method->send_queued_notifications();

			self::assertSame([9], $method->removed_subscriptions);
		}

		private function create_method($log = null, $user_loader = null, $assets_version = 1): testable_webpush
		{
			global $phpbb_root_path, $phpEx;

			$config = new \phpbb\config\config([
				'wpn_webpush_vapid_public' => notification_method_webpush_test::VAPID_KEYS['publicKey'],
				'wpn_webpush_vapid_private' => notification_method_webpush_test::VAPID_KEYS['privateKey'],
				'assets_version' => $assets_version,
			]);

			return new testable_webpush(
				$config,
				$this->createMock(\phpbb\db\driver\driver_interface::class),
				$log ?: $this->createMock(\phpbb\log\log_interface::class),
				$user_loader ?: $this->createMock(\phpbb\user_loader::class),
				$this->createMock(\phpbb\user::class),
				$phpbb_root_path,
				$phpEx,
				'phpbb_wpn_notification_push',
				'phpbb_wpn_push_subscriptions'
			);
		}

		private function create_notification($user_id)
		{
			$notification = $this->createMock(\phpbb\notification\type\type_interface::class);
			$notification->user_id = $user_id;
			$notification->notification_type_id = 1;
			$notification->item_id = 10;
			return $notification;
		}
	}

	class testable_webpush extends \phpbb\webpushnotifications\notification\method\webpush
	{
		public $subscriptions = [];
		public $removed_subscriptions = [];

		public function send_queued_notifications(): void
		{
			$this->notify_using_webpush();
		}

		public function get_queue(): array
		{
			return $this->queue;
		}

		public function set_push_token($type_id, $item_id, $user_id, $token): void
		{
			$property = new \ReflectionProperty(\phpbb\webpushnotifications\notification\method\webpush::class, 'push_token_map');
			$property->setAccessible(true);
			$property->setValue($this, [$type_id => [$item_id => [$user_id => $token]]]);
		}

		protected function get_user_subscription_map(array $notify_users): array
		{
			return $this->subscriptions;
		}

		public function remove_subscriptions(array $subscription_ids): void
		{
			$this->removed_subscriptions = $subscription_ids;
		}
	}
}
