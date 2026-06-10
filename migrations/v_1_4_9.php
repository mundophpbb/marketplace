<?php
/**
 * Marketplace / Classificados Extension for phpBB.
 * Adds direct PayPal purchase requests for public ads.
 */

namespace mundophpbb\marketplace\migrations;

class v_1_4_9 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_8'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.9', '>=');
	}

	public function update_schema()
	{
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_purchases'))
		{
			return [];
		}

		return [
			'add_tables' => [
				$this->table_prefix . 'marketplace_purchases' => [
					'COLUMNS' => [
						'purchase_id'           => ['UINT', null, 'auto_increment'],
						'ad_id'                 => ['UINT', 0],
						'buyer_user_id'         => ['UINT', 0],
						'seller_user_id'        => ['UINT', 0],
						'purchase_status'       => ['TINT:1', 3],
						'purchase_amount_cents' => ['BINT', 0],
						'purchase_currency'     => ['VCHAR:10', ''],
						'payment_provider'      => ['VCHAR:50', 'paypal'],
						'payment_reference'     => ['VCHAR:255', ''],
						'purchase_created'      => ['TIMESTAMP', 0],
						'purchase_decided'      => ['TIMESTAMP', 0],
						'purchase_decided_by'   => ['UINT', 0],
						'purchase_note'         => ['TEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'purchase_id',
					'KEYS' => [
						'ad_id' => ['INDEX', 'ad_id'],
						'buyer_user_id' => ['INDEX', 'buyer_user_id'],
						'seller_user_id' => ['INDEX', 'seller_user_id'],
						'status' => ['INDEX', 'purchase_status'],
						'payment_reference' => ['INDEX', 'payment_reference'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'marketplace_purchases',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_direct_purchase_enabled', 0]],
			['config.update', ['marketplace_version', '1.4.9']],
		];
	}
}
