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
	'HELP_FAQ_WPN'					=> 'Браузерные уведомления',
	'HELP_FAQ_WPN_WHAT_QUESTION'	=> 'Что такое браузерные уведомления?',
	'HELP_FAQ_WPN_WHAT_ANSWER'		=> 'Браузерные уведомления отправляются непосредственно на настольное или мобильное устройство получателя, даже если он не находится на данной конференции. Такие уведомления работают как и уведомления от приложений, обеспечивая немедленное информирование о личных сообщениях, событиях в форумах, модераторских активностях и так далее.',
	'HELP_FAQ_WPN_HOW_QUESTION'		=> 'Как получать браузерные уведомления на компьютер или мобильное устройство?',
	'HELP_FAQ_WPN_HOW_ANSWER'		=> 'Откройте Личный раздел и на закладке «Личный настройки» перейдите в блок «Изменить настройки уведомлений». Щёлкните кнопку «Включить» рядом с заголовком «Браузерные push—уведомления». При этом может поступить запрос от браузера на разрешение получения уведомлений от данного сайта. Для получения уведомлений необходимо принять данный запрос. Если после этих настроек браузерные уведомления не работают, убедитесь, что системные настройки устройства разрешают выбранному браузеру отправлять уведомления. Для таких мобильных устройств, как iPhone или iPad, может потребоваться добавить сайт конференции на «Домашний экран» для того, чтобы получать браузерные уведомления. При этом сайт конференции будет работать как самостоятельное приложение. Обратитесь к инструкции по использованию соответствующего мобильного устройства, чтобы включить уведомления для <a href="https://www.xda-developers.com/how-enable-safari-notifications-iphone/" target="_blank">iPhone/iPad</a> или <a href="https://support.google.com/chrome/answer/3220216?hl=en&co=GENIE.Platform%3DAndroid&oco=0" target="_blank">Android</a>.',
	'HELP_FAQ_WPN_SESSION_QUESTION'	=> 'Будут ли приходить браузерные уведомления, если пользователь вышел из своей учётной записи на конференции?',
	'HELP_FAQ_WPN_SESSION_ANSWER'	=> 'Да, браузерные уведомления продолжат поступать, даже если пользователь вышел из своей учётной записи на конференции.',
	'HELP_FAQ_WPN_SUBBING_QUESTION'	=> 'Почему кнопка «Включить» рядом с заголовком «Браузерные push—уведомления» неактивна?',
	'HELP_FAQ_WPN_SUBBING_ANSWER'	=> 'Если кнопка «Включить» рядом с заголовком «Браузерные push—уведомления» неактивна, возможно, выбранный браузер или мобильное устройство не поддерживают браузерные уведомления. Попробуйте использовать другой браузер или устройство, поддерживающие данную функцию.',
	'HELP_FAQ_WPN_GENERAL_QUESTION'	=> 'Браузерные уведомления включены и настроены, но всё равно не поступают. Что делать?',
	'HELP_FAQ_WPN_GENERAL_ANSWER'	=> 'Убедитесь, что сайту конференции разрешено отправлять уведомления в настройках выбранного браузера, а также что в системных настройках самого устройства отправка уведомлений разрешена для данного браузера или приложения сайта. В некоторых браузерах уведомления действуют, только если браузер открыт или работает в фоновом режиме. Для получения более подробной инфомации <a href="https://caniuse.com/push-api" target="_blank">обратитесь к таблице.</a> Если используется блокировщик рекламы, проверьте его настройки и убедитесь, что он не блокирует браузерные уведомления.',
]);
