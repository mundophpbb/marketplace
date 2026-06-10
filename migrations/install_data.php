<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace\migrations;

class install_data extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['marketplace_enabled']);
	}

	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\install_schema'];
	}

	public function update_data()
	{
		// Default config values
		$data = [
			['config.add', ['marketplace_enabled', 1]],
			['config.add', ['marketplace_require_approval', 1]],
			['config.add', ['marketplace_max_ads_per_user', 10]],
			['config.add', ['marketplace_ad_expiration_days', 30]],
			['config.add', ['marketplace_max_images', 5]],
			['config.add', ['marketplace_items_per_page', 20]],
			['config.add', ['marketplace_allow_images', 1]],
			['config.add', ['marketplace_enable_price', 1]],
			['config.add', ['marketplace_currency_default', 'R$']],
			['config.add', ['marketplace_show_sold_ads', 0]],
			['config.add', ['marketplace_sold_visible_days', 15]],
			['config.add', ['marketplace_allow_reports', 1]],
			['config.add', ['marketplace_allow_bump', 1]],
			['config.add', ['marketplace_bump_interval_days', 7]],
			['config.add', ['marketplace_allow_featured', 1]],
			['config.add', ['marketplace_featured_days', 14]],
			['config.add', ['marketplace_allow_boosted', 1]],
			['config.add', ['marketplace_boosted_days', 7]],
			['config.add', ['marketplace_version', '1.4.12']],
			['config.add', ['marketplace_allow_follows', 1]],
			['config.add', ['marketplace_paypal_enabled', 0]],
			['config.add', ['marketplace_direct_purchase_enabled', 0]],
			['config.add', ['marketplace_paypal_sandbox', 1]],
			['config.add', ['marketplace_paypal_business', '']],
			['config.add', ['marketplace_paypal_sandbox_business', '']],
			['config.add', ['marketplace_paypal_currency', 'BRL']],
			['config.add', ['marketplace_allow_promotion_requests', 1]],
			['config.add', ['marketplace_quantity_support', 1]],

			// Add ACP module
			['custom', [[$this, 'insert_default_package'], ['Destaque 7 dias', 'Destaca o anúncio visualmente por 7 dias.', 'featured', 7, 0, 'R$', 10]]],
			['custom', [[$this, 'insert_default_package'], ['Destaque 30 dias', 'Destaca o anúncio visualmente por 30 dias.', 'featured', 30, 0, 'R$', 20]]],
			['custom', [[$this, 'insert_default_package'], ['Impulso 7 dias', 'Dá prioridade de ordenação ao anúncio por 7 dias.', 'boosted', 7, 0, 'R$', 30]]],
			['custom', [[$this, 'insert_default_package'], ['Impulso 30 dias', 'Dá prioridade de ordenação ao anúncio por 30 dias.', 'boosted', 30, 0, 'R$', 40]]],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'MARKETPLACE_TITLE'
			]],
			['module.add', [
				'acp',
				'MARKETPLACE_TITLE',
				[
					'module_basename'	=> '\mundophpbb\marketplace\acp\main_module',
					'modes'				=> ['dashboard', 'settings', 'categories', 'ads', 'packages', 'reports'],
				],
			]],

			// Permissions - User
			['permission.add', ['u_marketplace_view']],
			['permission.add', ['u_marketplace_post']],
			['permission.add', ['u_marketplace_edit_own']],
			['permission.add', ['u_marketplace_delete_own']],
			['permission.add', ['u_marketplace_report']],
			['permission.add', ['u_marketplace_bump_own']],

			// Permissions - Moderator
			['permission.add', ['m_marketplace_approve']],
			['permission.add', ['m_marketplace_edit']],
			['permission.add', ['m_marketplace_delete']],
			['permission.add', ['m_marketplace_feature']],
			['permission.add', ['m_marketplace_reports']],

			// Assign default permissions
			['permission.permission_set', ['REGISTERED', 'u_marketplace_view', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_marketplace_post', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_marketplace_edit_own', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_marketplace_delete_own', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_marketplace_report', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_marketplace_bump_own', 'group']],

			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_marketplace_approve', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_marketplace_edit', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_marketplace_delete', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_marketplace_feature', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_marketplace_reports', 'group']],

			['permission.permission_set', ['ADMINISTRATORS', 'm_marketplace_approve', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'm_marketplace_edit', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'm_marketplace_delete', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'm_marketplace_feature', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'm_marketplace_reports', 'group']],

			// Default categories (language-key based)
			['custom', [[$this, 'add_sample_categories']]],
		];

		return $data;
	}

	public function revert_data()
	{
		return [];
	}


	/**
	 * Add language-key based default categories.
	 */
	public function add_sample_categories()
	{
		$table = $this->table_prefix . 'marketplace_categories';

		if (!$this->db_tools->sql_table_exists($table))
		{
			return;
		}

		$categories = [
			['MARKETPLACE_CAT_VEHICLES', 'MARKETPLACE_CAT_VEHICLES_DESC'],
			['MARKETPLACE_CAT_REAL_ESTATE', 'MARKETPLACE_CAT_REAL_ESTATE_DESC'],
			['MARKETPLACE_CAT_ELECTRONICS', 'MARKETPLACE_CAT_ELECTRONICS_DESC'],
			['MARKETPLACE_CAT_HOME_GARDEN', 'MARKETPLACE_CAT_HOME_GARDEN_DESC'],
			['MARKETPLACE_CAT_FASHION_BEAUTY', 'MARKETPLACE_CAT_FASHION_BEAUTY_DESC'],
			['MARKETPLACE_CAT_SERVICES', 'MARKETPLACE_CAT_SERVICES_DESC'],
			['MARKETPLACE_CAT_JOBS_OPPORTUNITIES', 'MARKETPLACE_CAT_JOBS_OPPORTUNITIES_DESC'],
			['MARKETPLACE_CAT_SPORTS_LEISURE', 'MARKETPLACE_CAT_SPORTS_LEISURE_DESC'],
			['MARKETPLACE_CAT_PETS', 'MARKETPLACE_CAT_PETS_DESC'],
			['MARKETPLACE_CAT_OTHER', 'MARKETPLACE_CAT_OTHER_DESC'],
		];

		$order = 10;
		foreach ($categories as $cat)
		{
			$sql = 'SELECT cat_id FROM ' . $table . " WHERE cat_name = '" . $this->db->sql_escape($cat[0]) . "'";
			$result = $this->db->sql_query_limit($sql, 1);
			$cat_id = (int) $this->db->sql_fetchfield('cat_id');
			$this->db->sql_freeresult($result);

			if (!$cat_id)
			{
				$sql_ary = [
					'cat_name'    => $cat[0],
					'cat_desc'    => $cat[1],
					'cat_order'   => $order,
					'cat_enabled' => 1,
				];

				$this->db->sql_query('INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
			}

			$order += 10;
		}
	}



	public function insert_default_package($title, $desc, $type, $days, $amount_cents, $currency, $order)
	{
		$now = time();
		$sql_ary = [
			'package_title' => (string) $title,
			'package_desc' => (string) $desc,
			'package_type' => (string) $type,
			'package_days' => (int) $days,
			'package_amount_cents' => (int) $amount_cents,
			'package_currency' => (string) $currency,
			'package_enabled' => 1,
			'package_order' => (int) $order,
			'package_created' => (int) $now,
			'package_updated' => (int) $now,
		];

		$this->db->sql_query('INSERT INTO ' . $this->table_prefix . 'marketplace_promotion_packages ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}
}
