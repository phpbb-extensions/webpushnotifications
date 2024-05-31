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

use phpbb\db\migration\migration;

class update_user_notifications extends migration
{
	/**
	 * @inheritDoc
	 */
	public static function depends_on()
	{
		return ['\phpbb\webpushnotifications\migrations\add_webpush'];
	}

	/**
	 * @inheritDoc
	 */
	public function effectively_installed()
	{
		$sql = 'SELECT method
			FROM ' . $this->table_prefix . "user_notifications
			WHERE method = '" . $this->db->sql_escape('notification.method.phpbb.wpn.webpush') . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row !== false;
	}

	/**
	 * @inheritDoc
	 */
	public function update_data()
	{
		return [
			['custom', [[$this, 'update_notifications']]],
		];
	}

	/**
	 * Add default push notifications for users in chunks
	 *
	 * @param $start int Start value for the update
	 * @return int|true Next start value or true if complete
	 */
	public function update_notifications($start)
	{
		$start = (int) $start;
		$limit = 500;
		$updated = 0;

		$sql_ary = [];

		$sql = 'SELECT user_id
			FROM ' . $this->table_prefix . 'users
			WHERE user_type <> ' . USER_IGNORE . '
			ORDER BY user_id ASC';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql_ary[] = [
				'item_type'		=> 'notification.type.pm',
				'item_id'		=> 0,
				'user_id'		=> (int) $row['user_id'],
				'notify'		=> 1,
				'method'		=> 'notification.method.phpbb.wpn.webpush',
			];
			$sql_ary[] = [
				'item_type'		=> 'notification.type.quote',
				'item_id'		=> 0,
				'user_id'		=> (int) $row['user_id'],
				'notify'		=> 1,
				'method'		=> 'notification.method.phpbb.wpn.webpush',
			];
			$updated++;
		}
		$this->db->sql_freeresult($result);

		$this->db->sql_multi_insert($this->table_prefix . 'user_notifications', $sql_ary);

		return ($updated === $limit) ? $start + $limit : true;
	}
}
