<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace\service;

/**
 * Marketplace service - common functions and helpers.
 */
class marketplace_service
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	protected $table_ads;
	protected $table_cats;
	protected $table_images;
	protected $cache;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		$table_ads,
		$table_cats,
		$table_images,
		\phpbb\cache\driver\driver_interface $cache
	)
	{
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->table_ads = $table_ads;
		$this->table_cats = $table_cats;
		$this->table_images = $table_images;
		$this->cache = $cache;
	}

	/**
	 * Get all active categories (cached)
	 */
	public function get_active_categories()
	{
		$cache_key = 'marketplace_categories';

		if (($categories = $this->cache->get($cache_key)) === false)
		{
			$sql = 'SELECT * FROM ' . $this->table_cats . '
					WHERE cat_enabled = 1
					ORDER BY cat_order ASC, cat_id ASC';
			$result = $this->db->sql_query($sql);

			$categories = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$categories[$row['cat_id']] = $row;
			}
			$this->db->sql_freeresult($result);

			$this->cache->put($cache_key, $categories, 3600);
		}

		return $categories;
	}

	/**
	 * Get status name
	 */
	public function get_status_name($status)
	{
		$statuses = [
			0 => 'MARKETPLACE_STATUS_PENDING',
			1 => 'MARKETPLACE_STATUS_ACTIVE',
			2 => 'MARKETPLACE_STATUS_SOLD',
			3 => 'MARKETPLACE_STATUS_EXPIRED',
			4 => 'MARKETPLACE_STATUS_HIDDEN',
		];

		return $this->language->lang($statuses[$status] ?? 'MARKETPLACE_STATUS_UNKNOWN');
	}

	/**
	 * Clean expired ads (can be called from cron later)
	 */
	public function clean_expired_ads()
	{
		$now = time();
		$sql = 'UPDATE ' . $this->table_ads . '
				SET ad_status = 3,
					ad_expired_at = ' . (int) $now . ',
					ad_updated = ' . (int) $now . '
				WHERE ad_status = 1
					AND ad_expires > 0
					AND ad_expires < ' . (int) $now;
		$this->db->sql_query($sql);
	}
}
