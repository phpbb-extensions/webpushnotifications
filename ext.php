<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\webpushnotifications;

/**
 * phpBB Browser Push Notifications Extension base
 */
class ext extends \phpbb\extension\base
{
	/**
	 * Require phpBB 3.3.12 due to new template and core events.
	 */
	public const PHPBB_MIN_VERSION = '3.3.12';

	/**
	 * Should not be installed in phpBB 4 because it already has push notifications.
	 */
	public const PHPBB_MAX_VERSION = '4.0.0-dev';

	/**
	 * Require PHP 7.3 due to 3rd party libraries included.
	 */
	public const PHP_MIN_VERSION = '7.3';

	/**
	 * @var array An array of installation error messages
	 */
	protected $errors = [];

	/**
	 * {@inheritdoc}
	 */
	public function is_enableable()
	{
		return $this->check_phpbb_version()
			->check_php_version()
			->check_php_requirements()
			->result();
	}

	/**
	 * Check the installed phpBB version meets this extension's requirements.
	 *
	 * @return \phpbb\webpushnotifications\ext
	 */
	protected function check_phpbb_version()
	{
		if (phpbb_version_compare(PHPBB_VERSION, self::PHPBB_MIN_VERSION, '<'))
		{
			$this->errors[] = 'PHPBB_VERSION_MIN_ERROR';
		}

		if (phpbb_version_compare(PHPBB_VERSION, self::PHPBB_MAX_VERSION, '>='))
		{
			$this->errors[] = 'PHPBB_VERSION_MAX_ERROR';
		}

		return $this;
	}

	/**
	 * Check the server PHP version meets this extension's requirements.
	 *
	 * @return \phpbb\webpushnotifications\ext
	 */
	protected function check_php_version()
	{
		if (phpbb_version_compare(PHP_VERSION_ID, '70300', '<'))
		{
			$this->errors[] = 'PHP_VERSION_ERROR';
		}

		return $this;
	}

	/**
	 * Check the installed PHP extensions meet this extension's requirements.
	 *
	 * @return \phpbb\webpushnotifications\ext
	 */
	protected function check_php_requirements()
	{
		foreach (['curl', 'mbstring', 'openssl'] as $extension)
		{
			if (!extension_loaded($extension))
			{
				$this->errors[] = ['PHP_EXT_MISSING', $extension];
			}
		}

		return $this;
	}

	/**
	 * Return the is_enableable result. Either true, or the best enable failed
	 * response for the current phpBB environment: array of error messages
	 * in phpBB 3.3 or newer, false otherwise.
	 *
	 * @return array|bool
	 */
	protected function result()
	{
		if (empty($this->errors))
		{
			return true;
		}

		if (phpbb_version_compare(PHPBB_VERSION, '3.3.0-b1', '>='))
		{
			$language = $this->container->get('language');
			$language->add_lang('install', 'phpbb/webpushnotifications');
			return array_map(static function($error) use ($language) {
				return call_user_func_array([$language, 'lang'], (array) $error);
			}, $this->errors);
		}

		return false;
	}
}
