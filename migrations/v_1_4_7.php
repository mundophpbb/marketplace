<?php
/**
 * Marketplace / Classificados Extension for phpBB.
 * Adds configurable promotion packages.
 */

namespace mundophpbb\marketplace\migrations;

class v_1_4_7 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_6'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.7', '>=');
	}

	public function update_schema()
	{
		$schema = [];
		$packages_table = $this->table_prefix . 'marketplace_promotion_packages';
		$promotions_table = $this->table_prefix . 'marketplace_promotions';

		if (!$this->db_tools->sql_table_exists($packages_table))
		{
			$schema['add_tables'][$packages_table] = [
				'COLUMNS' => [
					'package_id'           => ['UINT', null, 'auto_increment'],
					'package_title'        => ['VCHAR:255', ''],
					'package_desc'         => ['TEXT_UNI', ''],
					'package_type'         => ['VCHAR:20', 'featured'],
					'package_days'         => ['UINT', 7],
					'package_amount_cents' => ['BINT', 0],
					'package_currency'     => ['VCHAR:10', ''],
					'package_enabled'      => ['BOOL', 1],
					'package_order'        => ['UINT', 0],
					'package_created'      => ['TIMESTAMP', 0],
					'package_updated'      => ['TIMESTAMP', 0],
				],
				'PRIMARY_KEY' => 'package_id',
				'KEYS' => [
					'type_enabled' => ['INDEX', ['package_type', 'package_enabled']],
					'package_order' => ['INDEX', 'package_order'],
				],
			];
		}

		if ($this->db_tools->sql_table_exists($promotions_table) && !$this->db_tools->sql_column_exists($promotions_table, 'package_id'))
		{
			$schema['add_columns'][$promotions_table]['package_id'] = ['UINT', 0];
		}

		if ($this->db_tools->sql_table_exists($promotions_table) && $this->db_tools->sql_column_exists($promotions_table, 'package_id') && !$this->db_tools->sql_index_exists($promotions_table, 'package_id'))
		{
			$schema['add_index'][$promotions_table]['package_id'] = ['package_id'];
		}

		return $schema;
	}

	public function update_data()
	{
		$now = time();
		$currency = isset($this->config['marketplace_currency_default']) ? (string) $this->config['marketplace_currency_default'] : 'R$';

		return [
			['custom', [[$this, 'insert_default_package'], ['Destaque 7 dias', 'Destaca o anúncio visualmente por 7 dias.', 'featured', 7, 0, $currency, 10, $now]]],
			['custom', [[$this, 'insert_default_package'], ['Destaque 30 dias', 'Destaca o anúncio visualmente por 30 dias.', 'featured', 30, 0, $currency, 20, $now]]],
			['custom', [[$this, 'insert_default_package'], ['Impulso 7 dias', 'Dá prioridade de ordenação ao anúncio por 7 dias.', 'boosted', 7, 0, $currency, 30, $now]]],
			['custom', [[$this, 'insert_default_package'], ['Impulso 30 dias', 'Dá prioridade de ordenação ao anúncio por 30 dias.', 'boosted', 30, 0, $currency, 40, $now]]],
			['config.update', ['marketplace_version', '1.4.7']],
		];
	}

	public function insert_default_package($title, $desc, $type, $days, $amount_cents, $currency, $order, $now)
	{
		$table = $this->table_prefix . 'marketplace_promotion_packages';

		if (!$this->db_tools->sql_table_exists($table))
		{
			return;
		}

		$sql = 'SELECT package_id
			FROM ' . $table . "
			WHERE package_type = '" . $this->db->sql_escape((string) $type) . "'
				AND package_days = " . (int) $days . '
				AND package_order = ' . (int) $order;
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
			'package_amount_cents' => (int) $amount_cents,
			'package_currency' => (string) $currency,
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
			'drop_tables' => [
				$this->table_prefix . 'marketplace_promotion_packages',
			],
			'drop_columns' => [
				$this->table_prefix . 'marketplace_promotions' => [
					'package_id',
				],
			],
		];
	}
}
