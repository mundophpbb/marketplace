<?php
namespace mundophpbb\marketplace\migrations;

class v_1_4_23 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare((string) $this->config['marketplace_version'], '1.4.23', '>=') && $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_favorites');
	}

	static public function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_22'];
	}

	public function update_schema()
	{
		$changes = [];
		if (!$this->db_tools->sql_column_exists($this->table_prefix . 'marketplace_ads', 'ad_contact_count'))
		{
			$changes['add_columns'][$this->table_prefix . 'marketplace_ads']['ad_contact_count'] = ['UINT', 0];
		}

		$tables = [];
		if (!$this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_favorites'))
		{
			$tables[$this->table_prefix . 'marketplace_favorites'] = [
				'COLUMNS' => [
					'favorite_id' => ['UINT', null, 'auto_increment'],
					'user_id' => ['UINT', 0],
					'ad_id' => ['UINT', 0],
					'favorite_time' => ['TIMESTAMP', 0],
				],
				'PRIMARY_KEY' => 'favorite_id',
				'KEYS' => [
					'user_ad' => ['UNIQUE', ['user_id', 'ad_id']],
					'user_id' => ['INDEX', 'user_id'],
					'ad_id' => ['INDEX', 'ad_id'],
				],
			];
		}
		if (!$this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_compare'))
		{
			$tables[$this->table_prefix . 'marketplace_compare'] = [
				'COLUMNS' => [
					'compare_id' => ['UINT', null, 'auto_increment'],
					'user_id' => ['UINT', 0],
					'ad_id' => ['UINT', 0],
					'compare_time' => ['TIMESTAMP', 0],
				],
				'PRIMARY_KEY' => 'compare_id',
				'KEYS' => [
					'user_ad' => ['UNIQUE', ['user_id', 'ad_id']],
					'user_id' => ['INDEX', 'user_id'],
					'ad_id' => ['INDEX', 'ad_id'],
				],
			];
		}
		if ($tables)
		{
			$changes['add_tables'] = $tables;
		}
		return $changes;
	}

	public function update_data()
	{
		return [['config.update', ['marketplace_version', '1.4.23']]];
	}
}
