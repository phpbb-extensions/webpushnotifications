<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications\migrations;

use phpbb\db\migration\container_aware_migration;
use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem;

class setup_site_icons extends container_aware_migration
{
	private const NEW_ICON_DIR = 'images/site_icons';
	private const OLD_ICON_DIR = 'images/icons';

	/* @var filesystem $filesystem */
	private $filesystem;

	public function effectively_installed()
	{
		return $this->get_filesystem()->exists($this->container->getParameter('core.root_path') . self::NEW_ICON_DIR);
	}

	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_acp_pwa_configs'];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'configure_site_icons']]],
		];
	}

	/**
	 * Create a site_icons directory in the images directory (and copy existing PWA the icons there)
	 *
	 * @return void
	 */
	public function configure_site_icons()
	{
		// Cache frequently used services and values
		$filesystem = $this->get_filesystem();
		$root_path = $this->container->getParameter('core.root_path');
		$user = $this->container->get('user');
		$log = $this->container->get('log');

		// Prepare paths once
		$new_icon_path = $root_path . self::NEW_ICON_DIR;
		$old_icon_path = $root_path . self::OLD_ICON_DIR;

		// Batch get config values
		$icons = [
			'small' => $this->config->offsetGet('pwa_icon_small'),
			'large' => $this->config->offsetGet('pwa_icon_large')
		];

		try
		{
			// Create directory only if needed (give an empty index.htm too)
			if (!$filesystem->exists($new_icon_path))
			{
				$filesystem->mkdir($new_icon_path, 0755);
				$filesystem->touch($new_icon_path . '/index.htm');
			}

			// Process icons
			$copied = false;
			foreach ($icons as $icon)
			{
				$old_file = $old_icon_path . '/' . $icon;
				$new_file = $new_icon_path . '/' . $icon;

				if (!empty($icon) && $filesystem->exists($old_file))
				{
					$filesystem->copy($old_file, $new_file);
					$copied = true;
				}
			}

			// Set up a log message result
			$result = [
				'lang_key'	=> $copied ? 'LOG_WEBPUSH_ICON_COPY_SUCCESS' : 'LOG_WEBPUSH_ICON_DIR_SUCCESS',
				'params'	=> [$new_icon_path],
			];
		}
		catch (filesystem_exception $e)
		{
			$result = [
				'lang_key'	=> 'LOG_WEBPUSH_ICON_DIR_FAIL',
				'params'	=> [$e->get_filename(), $e->getMessage()]
			];
		}

		// Log result
		$log->add('admin', $user->data['user_id'], $user->ip, $result['lang_key'], false, $result['params']);
	}

	/**
	 * Get the filesystem object
	 *
	 * @return filesystem
	 */
	protected function get_filesystem()
	{
		if ($this->filesystem === null)
		{
			$this->filesystem = $this->container->get('filesystem');
		}

		return $this->filesystem;
	}
}
