<?php
/**
 * Marketplace v1.4.24 - localization and advanced category fields.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_24 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_23'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.24', '>=');
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'marketplace_ads' => [
					'ad_city' => ['VCHAR:120', ''],
					'ad_region' => ['VCHAR:120', ''],
					'ad_country' => ['VCHAR:120', ''],
					'ad_postal_code' => ['VCHAR:30', ''],
					'ad_location_approx' => ['BOOL', 0],
					'ad_latitude' => ['VCHAR:32', ''],
					'ad_longitude' => ['VCHAR:32', ''],
					'ad_conservation' => ['VCHAR:255', ''],
					'ad_delivery_options' => ['VCHAR:50', ''],
				],
			],
			'add_tables' => [
				$this->table_prefix . 'marketplace_category_fields' => [
					'COLUMNS' => [
						'field_id' => ['UINT', null, 'auto_increment'],
						'cat_id' => ['UINT', 0],
						'field_label' => ['VCHAR:255', ''],
						'field_type' => ['VCHAR:20', 'text'],
						'field_required' => ['BOOL', 0],
						'field_order' => ['UINT', 0],
					],
					'PRIMARY_KEY' => 'field_id',
					'KEYS' => [
						'cat_id' => ['INDEX', 'cat_id'],
					],
				],
				$this->table_prefix . 'marketplace_ad_field_values' => [
					'COLUMNS' => [
						'value_id' => ['UINT', null, 'auto_increment'],
						'ad_id' => ['UINT', 0],
						'field_id' => ['UINT', 0],
						'field_value' => ['TEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'value_id',
					'KEYS' => [
						'ad_field' => ['UNIQUE', ['ad_id', 'field_id']],
						'ad_id' => ['INDEX', 'ad_id'],
						'field_id' => ['INDEX', 'field_id'],
					],
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['marketplace_version', '1.4.24']],
		];
	}
}
