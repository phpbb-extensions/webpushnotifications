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
	'ACP_WEBPUSH_SETTINGS_EXPLAIN'	=> 'Здесь вы можете включить браузерные push—уведомления. Браузерные push—уведомления — это протокол мгновенной доставки пользователю сообщений о различных событиях. Они совместимы с большинством современных браузеров как на настольных, так и на мобильных устройствах. Пользователи могут включить их в своих браузерах и выбрать предпочтительные виды браузерных уведомлений в личном разделе.<br>Для работы браузерных push—уведомлений необходимо ввести или сгенерировать ключи идентификации ниже.<br><br>Для того, чтобы браузерные push—уведомления стали доступны на мобильных устройствах Apple, конференция должна функционировать как прогрессивное веб—приложение (PWA). Параметры прогрессивного веб—приложения (краткое имя сайта и значки для его отображения на домашнем экране) можно задать в разделе <strong>Настройки конференции</strong>. После этого пользователи смогут  добавлять сайт конференции на домашний экран своего мобильного устройства Apple и получать браузерные push—уведомления.',
	'WEBPUSH_ENABLE'				=> 'Включить браузерные push—уведомления',
	'WEBPUSH_ENABLE_EXPLAIN'		=> 'Включение возможности получения браузерных push—уведомлений для всех пользователей. Для использования браузерных push—уведомлений необходимо задать или сгенерировать корректные ключи идентификации VAPID.',
	'WEBPUSH_GENERATE_VAPID_KEYS'	=> 'Сгенерировать ключи идентификации',
	'WEBPUSH_VAPID_PUBLIC'			=> 'Публичный ключ идентификации сервера',
	'WEBPUSH_VAPID_PUBLIC_EXPLAIN'	=> 'Публичный ключ идентификации сервера VAPID (Voluntary Application Server Identification) необходим для аутентификации отправки push—уведомлений с вашей конференции.<br><em><strong>Внимание:</strong> изменение публичного ключа VAPID приведёт к автоматической отмене всех действующих подписок на push—уведомления.</em>',
	'WEBPUSH_VAPID_PRIVATE'			=> 'Приватный ключ идентификации сервера',
	'WEBPUSH_VAPID_PRIVATE_EXPLAIN'	=> 'Приватный ключ идентификации сервера VAPID (Voluntary Application Server Identification) необходим для создания push—уведомлений, отправляемых с вашей конференции. Приватный ключ VAPID <strong>должен</strong> составлять корректную пару ключей вместе с публичным ключом VAPID.<br><em><strong>Внимание:</strong> изменение приватного ключа VAPID приведёт к автоматической отмене всех действующих подписок на push—уведомления.</em>',
	'WEBPUSH_METHOD_ENABLED'		=> 'Включить все типы уведомлений по умолчанию',
	'WEBPUSH_METHOD_ENABLED_EXPLAIN'=> 'Если включено, то пользователи, подписавшиеся на браузерные push—уведомления, будут автоматически получать все их типы. Если отключено, то пользователи не будут получать браузерные push—уведомления до тех пор, пока хотя бы один их тип не выбран.<br><br>Отключить нежелательные или выбрать нужные типы браузерных push—уведомлений можно в настройках уведомлений в Личном разделе.',
	'WEBPUSH_DROPDOWN_SUBSCRIBE'	=> 'Показать кнопку «Подписаться» в выпадающем меню уведомлений',
	'WEBPUSH_DROPDOWN_SUBSCRIBE_EXPLAIN'=> 'Включить или отключить отображение кнопки «Подписаться» в выпадающем списке уведомлений. Если включено, то пользователи смогут подписываться на браузерные push-уведомления с любой страницы конференции.',
	'WEBPUSH_INSECURE_SERVER_ERROR' => 'На данной конференции не применяется защищённый протокол SSL/HTTPS, без которого использование браузерных push—уведомлений невозможно, либо соответствующие переменные серверного окружения неверно сконфигурированы. Убедитесь, что значения переменных серверного окружения <em>HTTPS</em> и/или <em>HEADER_CLIENT_PROTO</em> заданы верно.',
]);
