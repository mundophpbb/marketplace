<?php
/**
 * Marketplace 1.4.22 - Plans, packages and monetization.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_22 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_21'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.22', '>=')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_coupons')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_promo_periods')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_group_freebies');
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'marketplace_promotion_packages' => [
					'package_boosts' => ['UINT', 0],
					'package_ad_limit' => ['UINT', 0],
					'package_billing_cycle' => ['VCHAR:20', 'none'],
					'package_auto_renew' => ['BOOL', 0],
					'package_is_professional' => ['BOOL', 0],
				],
			],
			'add_tables' => [
				$this->table_prefix . 'marketplace_coupons' => [
					'COLUMNS' => [
						'coupon_id' => ['UINT', null, 'auto_increment'],
						'coupon_code' => ['VCHAR:50', ''],
						'coupon_desc' => ['TEXT_UNI', ''],
						'discount_type' => ['VCHAR:20', 'percent'],
						'discount_value' => ['BINT', 0],
						'coupon_currency' => ['VCHAR:10', 'BRL'],
						'coupon_starts' => ['TIMESTAMP', 0],
						'coupon_ends' => ['TIMESTAMP', 0],
						'coupon_usage_limit' => ['UINT', 0],
						'coupon_used_count' => ['UINT', 0],
						'coupon_enabled' => ['BOOL', 1],
						'coupon_created' => ['TIMESTAMP', 0],
						'coupon_updated' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'coupon_id',
					'KEYS' => ['coupon_code' => ['UNIQUE', 'coupon_code'], 'enabled' => ['INDEX', 'coupon_enabled']],
				],
				$this->table_prefix . 'marketplace_promo_periods' => [
					'COLUMNS' => [
						'period_id' => ['UINT', null, 'auto_increment'],
						'period_title' => ['VCHAR:255', ''],
						'period_package_type' => ['VCHAR:20', 'all'],
						'discount_type' => ['VCHAR:20', 'percent'],
						'discount_value' => ['BINT', 0],
						'period_starts' => ['TIMESTAMP', 0],
						'period_ends' => ['TIMESTAMP', 0],
						'period_enabled' => ['BOOL', 1],
					],
					'PRIMARY_KEY' => 'period_id',
					'KEYS' => ['type_enabled' => ['INDEX', ['period_package_type', 'period_enabled']]],
				],
				$this->table_prefix . 'marketplace_group_freebies' => [
					'COLUMNS' => [
						'free_id' => ['UINT', null, 'auto_increment'],
						'group_id' => ['UINT', 0],
						'free_featured' => ['BOOL', 0],
						'free_boosted' => ['BOOL', 0],
						'free_seller_plan' => ['BOOL', 0],
					],
					'PRIMARY_KEY' => 'free_id',
					'KEYS' => ['group_id' => ['UNIQUE', 'group_id']],
				],
			],
		];
	}

	public function update_data()
	{
		$currency = isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL';
		$now = time();

		return [
			['custom', [[$this, 'insert_package'], ['Destaque 15 dias', 'Destaca o anúncio por 15 dias.', 'featured', 15, 0, 0, 1500, $currency, 'none', 0, 0, 15, $now]]],
			['custom', [[$this, 'insert_package'], ['Impulsionamento diário', 'Impulso de prioridade por 1 dia.', 'boosted', 1, 1, 0, 500, $currency, 'none', 0, 0, 25, $now]]],
			['custom', [[$this, 'insert_package'], ['Pacote com 5 impulsionamentos', 'Crédito com múltiplos impulsionamentos.', 'boost_bundle', 30, 5, 0, 2000, $currency, 'none', 0, 0, 35, $now]]],
			['custom', [[$this, 'insert_package'], ['Pacote 10 anúncios', 'Permite publicar até 10 anúncios.', 'ad_quota', 30, 0, 10, 3000, $currency, 'none', 0, 0, 45, $now]]],
			['custom', [[$this, 'insert_package'], ['Vendedor profissional mensal', 'Plano mensal para vendedor profissional.', 'seller_plan', 30, 0, 50, 4990, $currency, 'monthly', 1, 1, 55, $now]]],
			['custom', [[$this, 'insert_package'], ['Vendedor profissional anual', 'Plano anual para vendedor profissional.', 'seller_plan', 365, 0, 600, 49900, $currency, 'annual', 1, 1, 65, $now]]],
			['config.update', ['marketplace_version', '1.4.22']],
		];
	}

	public function insert_package($title, $desc, $type, $days, $boosts, $ad_limit, $amount_cents, $currency, $billing_cycle, $auto_renew, $professional, $order, $now)
	{
		$table = $this->table_prefix . 'marketplace_promotion_packages';
		$sql = 'SELECT package_id FROM ' . $table . " WHERE package_type = '" . $this->db->sql_escape((string) $type) . "' AND package_title = '" . $this->db->sql_escape((string) $title) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$package_id = (int) $this->db->sql_fetchfield('package_id');
		$this->db->sql_freeresult($result);
		if ($package_id)
		{
			return;
		}

		$sql_ary = [
			'package_title' => (string) $title,
			'package_desc' => (string) $desc,
			'package_type' => (string) $type,
			'package_days' => (int) $days,
			'package_boosts' => (int) $boosts,
			'package_ad_limit' => (int) $ad_limit,
			'package_amount_cents' => (int) $amount_cents,
			'package_currency' => (string) $currency,
			'package_billing_cycle' => (string) $billing_cycle,
			'package_auto_renew' => (int) $auto_renew,
			'package_is_professional' => (int) $professional,
			'package_enabled' => 1,
			'package_order' => (int) $order,
			'package_created' => (int) $now,
			'package_updated' => (int) $now,
		];
		$this->db->sql_query('INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'marketplace_promotion_packages' => ['package_boosts', 'package_ad_limit', 'package_billing_cycle', 'package_auto_renew', 'package_is_professional'],
			],
			'drop_tables' => [
				$this->table_prefix . 'marketplace_coupons',
				$this->table_prefix . 'marketplace_promo_periods',
				$this->table_prefix . 'marketplace_group_freebies',
			],
		];
	}
}
