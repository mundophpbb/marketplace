<?php
/**
 * Marketplace / Classificados Extension for phpBB.
 *
 * Expands ad_price_cents to BIGINT so high-value ads such as real estate and
 * vehicles can be stored safely in cents without overflowing phpBB's UINT type.
 */

namespace mundophpbb\marketplace\migrations;

class v_1_4_2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['marketplace_price_cents_bigint']);
	}

	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_1'];
	}

	public function update_schema()
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'marketplace_ads' => [
					'ad_price_cents' => ['BINT', 0],
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_price_cents_bigint', 1]],
			['config.update', ['marketplace_version', '1.4.2']],
		];
	}

	public function revert_data()
	{
		return [];
	}
}
