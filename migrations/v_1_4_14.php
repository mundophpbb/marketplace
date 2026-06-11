<?php
/**
 * Marketplace 1.4.14 - Repair promotion columns for upgraded installations.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_14 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_13'];
	}

	public function effectively_installed()
	{
		$ads_table = $this->table_prefix . 'marketplace_ads';

		return $this->db_tools->sql_table_exists($ads_table)
			&& $this->db_tools->sql_column_exists($ads_table, 'ad_featured_until')
			&& $this->db_tools->sql_column_exists($ads_table, 'ad_featured_by')
			&& $this->db_tools->sql_column_exists($ads_table, 'ad_boosted_until')
			&& $this->db_tools->sql_column_exists($ads_table, 'ad_boosted_by')
			&& isset($this->config['marketplace_version'])
			&& version_compare($this->config['marketplace_version'], '1.4.14', '>=');
	}

	public function update_schema()
	{
		$ads_table = $this->table_prefix . 'marketplace_ads';
		$schema = [];

		if (!$this->db_tools->sql_table_exists($ads_table))
		{
			return [];
		}

		if (!$this->db_tools->sql_column_exists($ads_table, 'ad_featured_until'))
		{
			$schema['add_columns'][$ads_table]['ad_featured_until'] = ['TIMESTAMP', 0];
		}

		if (!$this->db_tools->sql_column_exists($ads_table, 'ad_featured_by'))
		{
			$schema['add_columns'][$ads_table]['ad_featured_by'] = ['UINT', 0];
		}

		if (!$this->db_tools->sql_column_exists($ads_table, 'ad_boosted_until'))
		{
			$schema['add_columns'][$ads_table]['ad_boosted_until'] = ['TIMESTAMP', 0];
		}

		if (!$this->db_tools->sql_column_exists($ads_table, 'ad_boosted_by'))
		{
			$schema['add_columns'][$ads_table]['ad_boosted_by'] = ['UINT', 0];
		}

		if (!$this->db_tools->sql_index_exists($ads_table, 'featured'))
		{
			$schema['add_index'][$ads_table]['featured'] = ['ad_featured_until'];
		}

		if (!$this->db_tools->sql_index_exists($ads_table, 'boosted'))
		{
			$schema['add_index'][$ads_table]['boosted'] = ['ad_boosted_until'];
		}

		return $schema;
	}

	public function update_data()
	{
		return [
			['config.update', ['marketplace_version', '1.4.14']],
		];
	}

	public function revert_schema()
	{
		return [];
	}

	public function revert_data()
	{
		return [];
	}
}
