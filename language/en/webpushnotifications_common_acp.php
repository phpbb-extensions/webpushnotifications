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
	'PWA_SETTINGS'				=> 'Progressive web application options',
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
]);
