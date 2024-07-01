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
	'PWA_SETTINGS'				=> 'Web application options',
	'PWA_SHORT_NAME'			=> 'Short site name',
	'PWA_SHORT_NAME_EXPLAIN'	=> 'Your site name in 12 characters or less, which may be used as a label for an icon on a mobile device’s home screen.',
	'PWA_ICON_SMALL'			=> 'Small mobile device icon',
	'PWA_ICON_SMALL_EXPLAIN'	=> 'File name of a 192px x 192px PNG image. This file must be uploaded to your <samp>icons</samp> directory, e.g. <samp>./images/icons</samp>',
	'PWA_ICON_LARGE'			=> 'Large mobile device icon',
	'PWA_ICON_LARGE_EXPLAIN'	=> 'File name of a 512px x 512px PNG image. This file must be uploaded to your <samp>icons</samp> directory, e.g. <samp>./images/icons</samp>',
	'PWA_ICON_SIZE_INVALID'		=> '%s does not have the correct image dimensions.',
	'PWA_ICON_MIME_INVALID'		=> '%s must be a PNG image file.',
	'PWA_IMAGE_INVALID'			=> '%s does not appear to be a valid image file.',
	'PWA_IMAGE_NOT_FOUND'		=> '%s could not be found.',
]);
