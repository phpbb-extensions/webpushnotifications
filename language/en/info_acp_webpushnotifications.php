<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'ACP_WEBPUSH_EXT_SETTINGS'			=> 'Web Push settings',
	'ACP_WEBPUSH_REMOVE_WARNING'		=> 'Web Push Notifications are now built into phpBB',
	'ACP_WEBPUSH_REMOVE_NOTICE'			=> 'The extension “phpBB Browser Push Notifications” is no longer needed and should be uninstalled and removed.<br>All settings and user preferences associated with the extension will be migrated into phpBB’s built-in push notifications when you uninstall the extension.',
	'LOG_CONFIG_WEBPUSH'				=> '<strong>Altered Web Push settings</strong>',
	'LOG_WEBPUSH_MESSAGE_FAIL'			=> '<strong>Web Push message could not be sent:</strong><br>» %s',
	'LOG_WEBPUSH_SUBSCRIPTION_REMOVED'	=> '<strong>Removed Web Push subscription:</strong><br>» %s',
	'LOG_WEBPUSH_ICON_DIR_FAIL'			=> '<strong>Webpush Notifications could not migrate the following item in phpBB’s images directory:</strong><br>» %1$s » %2$s',
	'LOG_WEBPUSH_ICON_DIR_SUCCESS'		=> '<strong>Webpush Notifications added the following directory:</strong><br>» <samp>%s</samp>',
	'LOG_WEBPUSH_ICON_COPY_SUCCESS'		=> '<strong>Webpush Notifications copied existing PWA touch icons to:</strong><br>» <samp>%s</samp>',
]);
