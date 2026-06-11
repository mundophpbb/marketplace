<?php
/**
 * Marketplace 1.4.13 - PayPal IPN payment audit log.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_13 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_12'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.13', '>=');
	}

	public function update_schema()
	{
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_payment_logs'))
		{
			return [];
		}

		return [
			'add_tables' => [
				$this->table_prefix . 'marketplace_payment_logs' => [
					'COLUMNS' => [
						'payment_log_id' => ['UINT', null, 'auto_increment'],
						'promotion_id' => ['UINT', 0],
						'payment_provider' => ['VCHAR:50', 'paypal'],
						'payment_reference' => ['VCHAR:255', ''],
						'payment_transaction_id' => ['VCHAR:255', ''],
						'payment_status' => ['VCHAR:50', ''],
						'payment_verification_status' => ['VCHAR:50', ''],
						'payment_validation_status' => ['VCHAR:100', ''],
						'payment_amount_cents' => ['BINT', 0],
						'payment_currency' => ['VCHAR:10', ''],
						'payment_receiver' => ['VCHAR:255', ''],
						'payment_raw' => ['MTEXT_UNI', ''],
						'payment_created' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'payment_log_id',
					'KEYS' => [
						'promotion_id' => ['INDEX', 'promotion_id'],
						'payment_reference' => ['INDEX', 'payment_reference'],
						'payment_transaction_id' => ['INDEX', 'payment_transaction_id'],
						'payment_created' => ['INDEX', 'payment_created'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'marketplace_payment_logs',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['marketplace_version', '1.4.13']],
		];
	}
}
