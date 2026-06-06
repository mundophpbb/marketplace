<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace\cron\task;

/**
 * Automatically expires active marketplace ads whose expiration timestamp passed.
 */
class expire_ads extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var string */
	protected $table_ads;

	/** @var string */
	protected $table_notifications;

	/** @var int */
	protected $interval = 3600;

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, $table_ads, $table_notifications)
	{
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->table_ads = $table_ads;
		$this->table_notifications = $table_notifications;
	}

	public function run()
	{
		$now = time();
		$expired_ads = [];

		$sql = 'SELECT ad_id, user_id, ad_title
			FROM ' . $this->table_ads . '
			WHERE ad_status = 1
				AND ad_expires > 0
				AND ad_expires < ' . (int) $now;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$expired_ads[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = 'UPDATE ' . $this->table_ads . '
			SET ad_status = 3,
				ad_expired_at = ' . (int) $now . ',
				ad_updated = ' . (int) $now . '
			WHERE ad_status = 1
				AND ad_expires > 0
				AND ad_expires < ' . (int) $now;
		$this->db->sql_query($sql);

		foreach ($expired_ads as $ad)
		{
			$this->add_notification((int) $ad['user_id'], (int) $ad['ad_id'], 'expired', $this->language->lang('MARKETPLACE_NOTIFICATION_EXPIRED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_EXPIRED_MESSAGE', $ad['ad_title']));
		}

		$this->config->set('marketplace_cron_last_expire', $now);
	}

	private function add_notification($user_id, $ad_id, $type, $title, $message)
	{
		if ($user_id <= 0 || $user_id === ANONYMOUS)
		{
			return;
		}

		$sql_ary = [
			'user_id'              => $user_id,
			'ad_id'                => $ad_id,
			'notification_type'    => substr((string) $type, 0, 50),
			'notification_title'   => substr((string) $title, 0, 255),
			'notification_message' => (string) $message,
			'notification_read'    => 0,
			'notification_time'    => time(),
		];

		$this->db->sql_query('INSERT INTO ' . $this->table_notifications . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}

	public function should_run()
	{
		if (empty($this->config['marketplace_enabled']))
		{
			return false;
		}

		if (!isset($this->config['marketplace_version']) || version_compare((string) $this->config['marketplace_version'], '1.3.0', '<'))
		{
			return false;
		}

		$last_run = isset($this->config['marketplace_cron_last_expire']) ? (int) $this->config['marketplace_cron_last_expire'] : 0;

		return $last_run < time() - $this->interval;
	}
}
