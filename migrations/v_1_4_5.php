<?php
/**
 * Marketplace 1.4.5 - boosted ads separated from bump.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_5 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_4'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_boosted_ads']);
	}

	public function update_schema()
	{
		$ads_table = $this->table_prefix . 'marketplace_ads';
		$schema = [];

		if ($this->db_tools->sql_table_exists($ads_table))
		{
			if (!$this->db_tools->sql_column_exists($ads_table, 'ad_boosted_until'))
			{
				$schema['add_columns'][$ads_table]['ad_boosted_until'] = ['TIMESTAMP', 0];
			}

			if (!$this->db_tools->sql_column_exists($ads_table, 'ad_boosted_by'))
			{
				$schema['add_columns'][$ads_table]['ad_boosted_by'] = ['UINT', 0];
			}

			if (!$this->db_tools->sql_index_exists($ads_table, 'boosted'))
			{
				$schema['add_index'][$ads_table]['boosted'] = ['ad_boosted_until'];
			}
		}

		return $schema;
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_allow_boosted', 1]],
			['config.add', ['marketplace_boosted_days', 7]],
			['config.add', ['marketplace_boosted_ads', 1]],
			['config.update', ['marketplace_version', '1.4.5']],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'marketplace_ads' => ['boosted'],
			],
			'drop_columns' => [
				$this->table_prefix . 'marketplace_ads' => [
					'ad_boosted_until',
					'ad_boosted_by',
				],
			],
		];
	}

	public function revert_data()
	{
		return [];
	}
}
