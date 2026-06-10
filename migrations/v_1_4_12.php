<?php
/**
 * Marketplace 1.4.12 - follow sellers and follower notifications.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_12 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_11'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.12', '>=');
	}

	public function update_schema()
	{
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_follows'))
		{
			return [];
		}

		return [
			'add_tables' => [
				$this->table_prefix . 'marketplace_follows' => [
					'COLUMNS' => [
						'follow_id'        => ['UINT', null, 'auto_increment'],
						'follower_user_id' => ['UINT', 0],
						'followed_user_id' => ['UINT', 0],
						'follow_created'   => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'follow_id',
					'KEYS' => [
						'follower' => ['INDEX', 'follower_user_id'],
						'followed' => ['INDEX', 'followed_user_id'],
						'pair'     => ['INDEX', ['follower_user_id', 'followed_user_id']],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'marketplace_follows',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_allow_follows', 1]],
			['config.update', ['marketplace_version', '1.4.12']],
		];
	}
}
