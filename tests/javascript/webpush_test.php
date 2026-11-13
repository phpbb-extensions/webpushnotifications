<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\tests\javascript;

use Symfony\Component\Process\Process;

class webpush_test extends \phpbb_test_case
{
	public function test_subscription_ownership_and_refresh_behaviour(): void
	{
		$process = new Process([
			'node',
			__DIR__ . '/webpush_test.js',
			__DIR__ . '/../../styles/all/template/webpush.js',
		]);
		$process->setTimeout(10);
		$process->run();

		self::assertSame(0, $process->getExitCode(), $process->getErrorOutput() . $process->getOutput());
	}
}
