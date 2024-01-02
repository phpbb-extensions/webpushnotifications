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

		$this->assertContainsLang('ACP_WEBPUSH_SETTINGS', $crawler->filter('div.main > h1')->text());
		$this->assertContainsLang('ACP_WEBPUSH_SETTINGS_EXPLAIN', $crawler->filter('div.main > p')->text());
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
			$this->assertEquals($config_value, $crawler->filter('input[name="' . $config_name . '"]')->attr('value'));
		}
	}

	public function test_ucp_module()
	{
		$this->login();

		$this->add_lang_ext('phpbb/webpushnotifications', 'webpushnotifications_module_ucp');

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');

		$this->assertContainsLang('NOTIFY_WEBPUSH_ENABLE', $crawler->filter('label[for="subscribe_webpush"]')->text());
		$this->assertContainsLang('PHPBB_WPN_NOTIFICATION_METHOD_WEBPUSH', $crawler->filter('th.mark')->eq(2)->text());
	}
}
