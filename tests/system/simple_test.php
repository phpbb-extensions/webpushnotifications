<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\system;

class simple_test extends \phpbb_test_case
{
	/** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\finder */
	protected $extension_finder;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\db\migrator */
	protected $migrator;

	/**
	 * @inheritdoc
	 */
	protected function setUp(): void
	{
		parent::setUp();

		// Stubs
		$this->container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')->disableOriginalConstructor()->getMock();
		$this->extension_finder = $this->getMockBuilder('\phpbb\finder')->disableOriginalConstructor()->getMock();
		$this->migrator = $this->getMockBuilder('\phpbb\db\migrator')->disableOriginalConstructor()->getMock();
	}

	/**
	 * Test the extension can only be enabled when the minimum
	 * phpBB version requirement is satisfied.
	 */
	public function test_ext()
	{
		$ext = new \phpbb\webpushnotifications\ext(
			$this->container,
			$this->extension_finder,
			$this->migrator,
			'phpbb/webpushnotifications',
			''
		);

		self::assertTrue($ext->is_enableable(), 'Asserting that the extension is enable-able.');
	}
}
