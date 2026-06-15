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
			$this->add_notification((int) $ad['user_id'], (int) $ad['ad_id'], 'ad_expired', $this->language->lang('MARKETPLACE_NOTIFICATION_EXPIRED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_EXPIRED_MESSAGE', $ad['ad_title']));
		}

		$this->notify_ads_expiring_soon($now);
		$this->expire_promotions($now);

		$this->config->set('marketplace_cron_last_expire', $now);
	}

	private function notify_ads_expiring_soon($now)
	{
		$soon = (int) $now + (3 * 86400);
		$sql = 'SELECT ad_id, user_id, ad_title, ad_expires
			FROM ' . $this->table_ads . '
			WHERE ad_status = 1
				AND ad_expires > ' . (int) $now . '
				AND ad_expires <= ' . (int) $soon;
		$result = $this->db->sql_query($sql);
		while ($ad = $this->db->sql_fetchrow($result))
		{
			if (!$this->notification_exists((int) $ad['ad_id'], (int) $ad['user_id'], 'ad_expiring_soon'))
			{
				$this->add_notification((int) $ad['user_id'], (int) $ad['ad_id'], 'ad_expiring_soon', $this->language->lang('MARKETPLACE_NOTIFICATION_EXPIRING_SOON_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_EXPIRING_SOON_MESSAGE', $ad['ad_title']));
			}
		}
		$this->db->sql_freeresult($result);
	}

	private function expire_promotions($now)
	{
		$sql = 'SELECT ad_id, user_id, ad_title, ad_featured_until, ad_boosted_until
			FROM ' . $this->table_ads . '
			WHERE (ad_featured_until > 0 AND ad_featured_until < ' . (int) $now . ')
				OR (ad_boosted_until > 0 AND ad_boosted_until < ' . (int) $now . ')';
		$result = $this->db->sql_query($sql);
		$reset_featured = [];
		$reset_boosted = [];
		while ($ad = $this->db->sql_fetchrow($result))
		{
			if (!empty($ad['ad_featured_until']) && (int) $ad['ad_featured_until'] < $now)
			{
				if (!$this->notification_exists((int) $ad['ad_id'], (int) $ad['user_id'], 'promotion_expired_featured'))
				{
					$this->add_notification((int) $ad['user_id'], (int) $ad['ad_id'], 'promotion_expired_featured', $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_EXPIRED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_EXPIRED_MESSAGE', $ad['ad_title']));
				}
				$reset_featured[] = (int) $ad['ad_id'];
			}
			if (!empty($ad['ad_boosted_until']) && (int) $ad['ad_boosted_until'] < $now)
			{
				if (!$this->notification_exists((int) $ad['ad_id'], (int) $ad['user_id'], 'promotion_expired_boosted'))
				{
					$this->add_notification((int) $ad['user_id'], (int) $ad['ad_id'], 'promotion_expired_boosted', $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_EXPIRED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_EXPIRED_MESSAGE', $ad['ad_title']));
				}
				$reset_boosted[] = (int) $ad['ad_id'];
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($reset_featured))
		{
			$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ad_featured_until = 0, ad_featured_by = 0 WHERE ' . $this->db->sql_in_set('ad_id', $reset_featured));
		}
		if (!empty($reset_boosted))
		{
			$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ad_boosted_until = 0, ad_boosted_by = 0 WHERE ' . $this->db->sql_in_set('ad_id', $reset_boosted));
		}
	}

	private function notification_exists($ad_id, $user_id, $type)
	{
		$sql = 'SELECT notification_id
			FROM ' . $this->table_notifications . "
			WHERE ad_id = " . (int) $ad_id . '
				AND user_id = ' . (int) $user_id . "
				AND notification_type = '" . $this->db->sql_escape($type) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$notification_id = (int) $this->db->sql_fetchfield('notification_id');
		$this->db->sql_freeresult($result);

		return $notification_id > 0;
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
