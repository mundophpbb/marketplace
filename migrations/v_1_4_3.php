<?php
/**
 * Marketplace 1.4.3 - simple stock quantity support.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_3 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_2'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_quantity_support'])
			&& $this->db_tools->sql_column_exists($this->table_prefix . 'marketplace_ads', 'ad_quantity');
	}

	public function update_schema()
	{
		$table = $this->table_prefix . 'marketplace_ads';
		$schema = [];

		if ($this->db_tools->sql_table_exists($table))
		{
			if (!$this->db_tools->sql_column_exists($table, 'ad_quantity'))
			{
				$schema['add_columns'][$table]['ad_quantity'] = ['UINT', 1];
			}

			if (!$this->db_tools->sql_index_exists($table, 'quantity'))
			{
				$schema['add_index'][$table]['quantity'] = ['ad_quantity'];
			}
		}

		return $schema;
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_quantity_support', 1]],
			['config.update', ['marketplace_version', '1.4.3']],
		];
	}

	public function revert_data()
	{
		return [];
	}
}
