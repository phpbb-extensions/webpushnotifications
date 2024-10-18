<?php
/**
 *
 * phpBB Browser Push Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, phpBB Limited <https://www.phpbb.com>
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
	'HELP_FAQ_WPN'					=> 'Web Push Notifications for Browsers',
	'HELP_FAQ_WPN_WHAT_QUESTION'	=> 'What are web push notifications?',
	'HELP_FAQ_WPN_WHAT_ANSWER'		=> 'Web push notifications enhance phpBB’s notifications by enabling web browsers to push real-time system notifications directly to your desktop or mobile device, even when you are not actively browsing this site. These notifications appear like app alerts and can deliver real-time updates for private messages, post interactions, moderation actions, etc.',
	'HELP_FAQ_WPN_HOW_QUESTION'		=> 'How can I get forum notification alerts on my computer or mobile device?',
	'HELP_FAQ_WPN_HOW_ANSWER'		=> 'Go to the ‘Notification options’ in the UCP and click ‘Enable web push notifications’. Once enabled, your browser may prompt you to allow this forum to send push notifications to your device, which you should allow. Note: if you still are not receiving notifications, you may need to visit your device‘s system settings to enable/allow notifications from your browser.',
	'HELP_FAQ_WPN_IOS_QUESTION'		=> 'How does it work on mobile devices (iOS/Android)?',
	'HELP_FAQ_WPN_IOS_ANSWER'		=> 'On some devices, like those running iOS or iPadOS, users need to add the forum’s site to their Home Screen for push notifications to work. This will make the site function like a stand-alone web application. Refer to your specific device’s OS for instructions on enabling web push notifications for <a href="https://izooto.com/blog/enable-safari-push-notifications-on-ios-step-by-step-guide" target="_blank">iOS / iPadOS</a> or <a href="https://support.google.com/chrome/answer/3220216?hl=en&co=GENIE.Platform%3DAndroid&oco=0" target="_blank">Android</a>.',
	'HELP_FAQ_WPN_SESSION_QUESTION'	=> 'Will I receive notifications if I am logged out?',
	'HELP_FAQ_WPN_SESSION_ANSWER'	=> 'Yes, notifications will continue to be sent even if you are logged out.',
	'HELP_FAQ_WPN_SUBBING_QUESTION'	=> 'Why are the “Enable Push Notifications” buttons disabled?',
	'HELP_FAQ_WPN_SUBBING_ANSWER'	=> 'If the “Enable Push Notifications” buttons are visible but cannot be clicked, it’s likely because the browser or device you’re using doesn’t support push notifications. To resolve this, try using a different browser or device that supports this feature.',
	'HELP_FAQ_WPN_GENERAL_QUESTION'	=> 'I am still having trouble getting notifications after enabling them?',
	'HELP_FAQ_WPN_GENERAL_ANSWER'	=> 'Ensure that this site is allowed to send notifications in your browser settings. Ensure that your computer or device is configured to receive notifications from your web browser or web application in your device system settings. Some browsers may deliver notifications even when closed, while others may only do so when the browser is open. <a href="https://github.com/phpbb-extensions/webpushnotifications?tab=readme-ov-file#browser-support" target="_blank">Here is a table of supported behavior among the most common browsers.</a>',
]);
