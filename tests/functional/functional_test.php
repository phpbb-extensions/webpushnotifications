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
}
