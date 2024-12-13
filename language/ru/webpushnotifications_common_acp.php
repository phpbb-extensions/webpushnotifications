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
	'PWA_SETTINGS'				=> 'Настройки прогрессивного веб—приложения (PWA)',
	'PWA_SHORT_NAME'			=> 'Краткое имя сайта',
	'PWA_SHORT_NAME_EXPLAIN'	=> 'Краткое имя сайта длиной не более 12 символов, которое будет использовано в качестве подписи к значку на домашнем экране мобильного устройства. Если оставить пустым, будут использованы первые 12 символов значения настройки, заданной в поле <samp>Название конференции</samp>.',
	'PWA_SHORT_NAME_INVALID'	=> 'Заданное значение в поле «Краткое имя сайта» превышает 12 символов.',
	'PWA_ICON_SMALL'			=> 'Маленький значок для мобильного устройства',
	'PWA_ICON_SMALL_EXPLAIN'	=> 'Имя файла изображения формата PNG размером 192 x 192 пикселя. Файл изображения должен быть загружен на сервер в папку <samp>' . \phpbb\webpushnotifications\ext::PWA_ICON_DIR . '</samp>.',
	'PWA_ICON_LARGE'			=> 'Большой значок для мобильного устройства',
	'PWA_ICON_LARGE_EXPLAIN'	=> 'Имя файла изображения формата PNG размером 512 x 512 пикселей. Файл изображения должен быть загружен на сервер в папку <samp>' . \phpbb\webpushnotifications\ext::PWA_ICON_DIR . '</samp>.',
	'PWA_ICON_SIZE_INVALID'		=> 'Изображение «%s» имеет некорректные размеры.',
	'PWA_ICON_MIME_INVALID'		=> 'Файл изображения «%s» должен иметь формат PNG.',
	'PWA_ICON_INVALID'			=> 'Файл «%s» не является файлом изображения или отсутствует по указанному пути. Проверьте правильность имени файла и его расположения.',
	'PWA_ICON_NOT_PROVIDED'		=> 'Настройка «%s» не может быть пустой. Необходимо задать все пути к файлам значков для мобильных устройств.',
]);
