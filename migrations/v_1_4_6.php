<?php
/**
 * Marketplace / Classificados Extension for phpBB.
 * Adds the promotion request foundation.
 */

namespace mundophpbb\marketplace\migrations;

class v_1_4_6 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_5'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.6', '>=');
	}

	public function update_schema()
	{
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_promotions'))
		{
			return [];
		}

		return [
			'add_tables' => [
				$this->table_prefix . 'marketplace_promotions' => [
					'COLUMNS' => [
						'promotion_id'        => ['UINT', null, 'auto_increment'],
						'ad_id'               => ['UINT', 0],
						'user_id'             => ['UINT', 0],
						'promotion_type'      => ['VCHAR:20', ''],
						'promotion_status'    => ['TINT:1', 0],
						'promotion_days'      => ['UINT', 0],
						'promotion_amount_cents' => ['BINT', 0],
						'promotion_currency'  => ['VCHAR:10', ''],
						'payment_provider'    => ['VCHAR:50', 'manual'],
						'payment_reference'   => ['VCHAR:255', ''],
						'promotion_requested'=> ['TIMESTAMP', 0],
						'promotion_decided'  => ['TIMESTAMP', 0],
						'promotion_decided_by' => ['UINT', 0],
						'promotion_note'     => ['TEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'promotion_id',
					'KEYS' => [
						'ad_id' => ['INDEX', 'ad_id'],
						'user_id' => ['INDEX', 'user_id'],
						'status' => ['INDEX', 'promotion_status'],
						'type_status' => ['INDEX', ['promotion_type', 'promotion_status']],
					],
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_allow_promotion_requests', 1]],
			['config.update', ['marketplace_version', '1.4.6']],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'marketplace_promotions',
			],
		];
	}
}
