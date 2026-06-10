<?php
/**
 * Marketplace 1.4.11 - seller PayPal direct payments for ad purchases.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_11 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_10'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.11', '>=');
	}

	public function update_schema()
	{
		$ads_table = $this->table_prefix . 'marketplace_ads';

		if (!$this->db_tools->sql_table_exists($ads_table) || $this->db_tools->sql_column_exists($ads_table, 'ad_paypal_email'))
		{
			return [];
		}

		return [
			'add_columns' => [
				$ads_table => [
					'ad_paypal_email' => ['VCHAR:255', ''],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'marketplace_ads' => ['ad_paypal_email'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['marketplace_version', '1.4.11']],
		];
	}
}
