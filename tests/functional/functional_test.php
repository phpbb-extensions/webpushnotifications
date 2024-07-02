<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\functional;

/**
 * @group functional
 */
class functional_test extends \phpbb_functional_test_case
{
	protected static function setup_extensions()
	{
		return array('phpbb/webpushnotifications');
	}

	public function test_extension_enabled()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);

		$this->assertStringContainsString('phpBB Browser Push Notifications', $crawler->filter('.ext_enabled')->eq(0)->text());
		$this->assertContainsLang('EXTENSION_DISABLE', $crawler->filter('.ext_enabled')->eq(0)->text());
	}

	public function test_acp_module()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('phpbb/webpushnotifications', ['info_acp_webpushnotifications', 'webpushnotifications_module_acp']);

		$crawler = self::request('GET', 'adm/index.php?i=-phpbb-webpushnotifications-acp-wpn_acp_module&mode=webpush&sid=' . $this->sid);

		$this->assertContainsLang('ACP_WEBPUSH_EXT_SETTINGS', $crawler->filter('div.main > h1')->text());
		$this->assertContainsLang('ACP_WEBPUSH_SETTINGS_EXPLAIN', $crawler->filter('div.main > p')->html());
		$this->assertContainsLang('WEBPUSH_GENERATE_VAPID_KEYS', $crawler->filter('input[type="button"]')->attr('value'));

		$form_data = [
			'config[wpn_webpush_enable]'	=> 1,
			'config[wpn_webpush_vapid_public]'	=> 'BDnYSJHVZBxq834LqDGr893IfazEez7q-jYH2QBNlT0ji2C9UwGosiqz8Dp_ZN23lqAngBZyRjXVWF4ZLA8X2zI',
			'config[wpn_webpush_vapid_private]'	=> 'IE5OYlmfWsMbBU1lzvr0bxrxVAXIteSkAnwGlZIhmRk',
		];
		$form = $crawler->selectButton('submit')->form($form_data);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('CONFIG_UPDATED'), $crawler->filter('.successbox')->text());

		$crawler = self::request('GET', 'adm/index.php?i=-phpbb-webpushnotifications-acp-wpn_acp_module&mode=webpush&sid=' . $this->sid);

		foreach ($form_data as $config_name => $config_value)
		{
			$config_value = ($config_name === 'config[wpn_webpush_vapid_private]') ? \phpbb\webpushnotifications\acp\wpn_acp_module::MASKED_PRIVATE_KEY : $config_value;
			$this->assertEquals($config_value, $crawler->filter('input[name="' . $config_name . '"]')->attr('value'));
		}
	}

	public function test_ucp_module()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('phpbb/webpushnotifications', 'webpushnotifications_module_ucp');

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');

		$this->assertContainsLang('NOTIFY_WEBPUSH_NOTIFICATIONS', $crawler->filter('label[for="subscribe_webpush"]')->text());
		$this->assertContainsLang('NOTIFICATION_METHOD_PHPBB_WPN_WEBPUSH', $crawler->filter('th.mark')->eq(2)->text());

		// Assert checkbox is unchecked by default
		$wp_list = $crawler->filter('.table1');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.quote_notification.method.phpbb.wpn.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.pm_notification.method.phpbb.wpn.webpush');

		$this->set_acp_option('wpn_webpush_method_enabled', 1);

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');

		// Assert checkbox is checked
		$wp_list = $crawler->filter('.table1');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.quote_notification.method.phpbb.wpn.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.pm_notification.method.phpbb.wpn.webpush');
	}

	public function test_dropdown_subscribe_button()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('phpbb/webpushnotifications', 'webpushnotifications_module_ucp');

		// Assert subscribe dropdown is not present by default
		$crawler = self::request('GET', 'index.php');
		$this->assertCount(0, $crawler->filter('.wpn-notification-dropdown-footer'));

		$this->set_acp_option('wpn_webpush_dropdown_subscribe', 1);

		// Assert subscribe dropdown is present
		$crawler = self::request('GET', 'index.php');
		$this->assertCount(1, $crawler->filter('.wpn-notification-dropdown-footer'));
		$this->assertContainsLang('NOTIFY_WEBPUSH_SUBSCRIBE', $crawler->filter('.wpn-notification-dropdown-footer #subscribe_webpush')->html());
		$this->assertContainsLang('NOTIFY_WEBPUSH_UNSUBSCRIBE', $crawler->filter('.wpn-notification-dropdown-footer #unsubscribe_webpush')->html());

		// Assert subscribe button is not displayed in UCP when dropdown subscribe is present
		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');
		$this->assertCount(0, $crawler->filter('.wpn-notification-dropdown-footer'));
	}

	public function test_manifest()
	{
		$expected = [
			'name'			=> 'yourdomain.com',
			'short_name'	=> 'yourdomain',
			'display'		=> 'standalone',
			'orientation'	=> 'portrait',
			'dir'			=> 'ltr',
			'start_url'		=> '/',
			'scope'			=> '/',
		];

		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=settings&sid=' . $this->sid);

		$form_data = [
			'config[pwa_short_name]'	=> $expected['short_name'],
		];
		$form = $crawler->selectButton('submit')->form($form_data);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('CONFIG_UPDATED'), $crawler->filter('.successbox')->text());

		self::request('GET', 'app.php/manifest', [], false);
		$this->assertEquals(json_encode($expected), self::get_content());
	}

	protected function set_acp_option($option, $value)
	{
		$crawler = self::request('GET', 'adm/index.php?i=-phpbb-webpushnotifications-acp-wpn_acp_module&mode=webpush&sid=' . $this->sid);
		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();
		$values["config[{$option}]"] = $value;
		$form->setValues($values);
		$crawler = self::submit($form);
		$this->assertEquals(1, $crawler->filter('.successbox')->count());
	}
}
