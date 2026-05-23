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
	// Web push settings
	'ACP_WEBPUSH_SETTINGS_EXPLAIN'	=> 'Here you can enable Web Push for board notifications. Web Push is a protocol for the real-time delivery of events to user agents, commonly referred to as push messages. It is compatible with the majority of modern browsers on both desktop and mobile devices. Users can opt to receive Web Push alerts in their browser by subscribing and enabling their preferred notifications in the UCP.<br><br>To enable push notifications on Apple mobile devices, your site needs to function as a Progressive Web Application (PWA). This requires users to add your site to their device’s home screen. Configure app metadata and install behaviour under <strong>PWA settings</strong>.',
	'WEBPUSH_ENABLE'				=> 'Enable Web Push',
	'WEBPUSH_ENABLE_EXPLAIN'		=> 'Allow users to receive notifications in their browser or device via Web Push. To utilise Web Push, you must input or generate valid VAPID identification keys.',
	'WEBPUSH_GENERATE_VAPID_KEYS'	=> 'Generate Identification keys',
	'WEBPUSH_VAPID_PUBLIC'			=> 'Server identification public key',
	'WEBPUSH_VAPID_PUBLIC_EXPLAIN'	=> 'The Voluntary Application Server Identification (VAPID) public key is shared to authenticate push messages from your site.<br><em><strong>Caution:</strong> Modifying the VAPID public key will automatically render all Web Push subscriptions invalid.</em>',
	'WEBPUSH_VAPID_PRIVATE'			=> 'Server identification private key',
	'WEBPUSH_VAPID_PRIVATE_EXPLAIN'	=> 'The Voluntary Application Server Identification (VAPID) private key is used to generate authenticated push messages dispatched from your site. The VAPID private key <strong>must</strong> form a valid public-private key pair alongside the VAPID public key.<br><em><strong>Caution:</strong> Modifying the VAPID private key will automatically render all Web Push subscriptions invalid.</em>',
	'WEBPUSH_METHOD_ENABLED'		=> 'Enable user-based web push notifications by default',
	'WEBPUSH_METHOD_ENABLED_EXPLAIN'=> 'When this setting is enabled, users who have also enabled and allowed browser notifications will start receiving them automatically. They can visit the UCP Notification settings to disable any unwanted notifications.<br><br>If this setting is disabled, users will not receive any notifications, even if they have enabled push notifications, until they visit the UCP Notification settings to allow the specific notifications they wish to receive.',
	'WEBPUSH_DROPDOWN_SUBSCRIBE'	=> 'Show web push settings in the notification dropdown',
	'WEBPUSH_DROPDOWN_SUBSCRIBE_EXPLAIN'=> 'Show or hide the “Enable Web Push” toggle switch in the notification dropdown. This allows users to easily enable or disable push notifications from any page of the forum.',
	'WEBPUSH_POPUP_PROMPT'			=> 'Show popup prompt for unsubscribed members',
	'WEBPUSH_POPUP_PROMPT_EXPLAIN'	=> 'Display a popup message asking registered members if they want to receive push notifications. The popup will only appear to members who are not currently subscribed and have not previously denied.',
	'WEBPUSH_INSECURE_SERVER_ERROR' => 'This board is not using a secure SSL/HTTPS protocol, which is required for enabling web push notifications. Alternatively, the server environment might be misconfigured. Ensure that the <em>HTTPS</em> and <em>HEADER_CLIENT_PROTO</em> server environment variables are correctly configured.',

	// PWA Settings
	'ACP_PWA_SETTINGS_EXPLAIN'	=> 'Here you can configure Progressive Web App behaviour, including app manifest metadata and install banner display.',
	'PWA_SHORT_NAME'			=> 'Short site name',
	'PWA_SHORT_NAME_EXPLAIN'	=> 'Your site name in 12 characters or fewer, which may be used as a label for an icon on a mobile device’s home screen. (If this field is left empty, the first 12 characters of the <samp>Site name</samp> will be used.)',
	'PWA_SHORT_NAME_INVALID'	=> '“Short site name” exceeds the 12 character limit.',
	'PWA_ICON_SMALL'			=> 'Small mobile device icon',
	'PWA_ICON_SMALL_EXPLAIN'	=> 'File name of a 192px x 192px PNG image. This file must be uploaded to your board’s <samp>' . \phpbb\webpushnotifications\ext::PWA_ICON_DIR . '</samp> directory.',
	'PWA_ICON_LARGE'			=> 'Large mobile device icon',
	'PWA_ICON_LARGE_EXPLAIN'	=> 'File name of a 512px x 512px PNG image. This file must be uploaded to your board’s <samp>' . \phpbb\webpushnotifications\ext::PWA_ICON_DIR . '</samp> directory.',
	'PWA_ICON_SIZE_INVALID'		=> '“%s” does not have the correct image dimensions.',
	'PWA_ICON_MIME_INVALID'		=> '“%s” must be a PNG image file.',
	'PWA_ICON_INVALID'			=> '“%s” is not a valid image file or is missing from the expected location. Verify the file name and location are correct.',
	'PWA_ICON_NOT_PROVIDED'		=> '%s field must not be empty. All icon fields must contain an image.',
	'PWA_COLOURS'				=> 'Colours',
	'PWA_THEME_COLOUR'			=> 'Theme colour',
	'PWA_BACKGROUND_COLOUR'		=> 'Background colour',
	'PWA_SHOW_INSTALL_BANNER'	=> 'Show install banner',
	'PWA_SHOW_INSTALL_BANNER_EXPLAIN'	=> 'Display a mobile install prompt when the browser reports that your board can be installed as an app.',
]);
