<?php
/**
 * Marketplace v1.4.25 - internal user conversations and message anti-spam.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_25 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_24'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.25', '>=');
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'marketplace_conversations' => [
					'COLUMNS' => [
						'conversation_id' => ['UINT', null, 'auto_increment'],
						'ad_id' => ['UINT', 0],
						'buyer_user_id' => ['UINT', 0],
						'seller_user_id' => ['UINT', 0],
						'conversation_status' => ['TINT:1', 0],
						'conversation_created' => ['TIMESTAMP', 0],
						'conversation_updated' => ['TIMESTAMP', 0],
						'last_message_time' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'conversation_id',
					'KEYS' => [
						'ad_buyer_seller' => ['UNIQUE', ['ad_id', 'buyer_user_id', 'seller_user_id']],
						'buyer' => ['INDEX', 'buyer_user_id'],
						'seller' => ['INDEX', 'seller_user_id'],
						'updated' => ['INDEX', 'last_message_time'],
					],
				],
				$this->table_prefix . 'marketplace_messages' => [
					'COLUMNS' => [
						'message_id' => ['UINT', null, 'auto_increment'],
						'conversation_id' => ['UINT', 0],
						'ad_id' => ['UINT', 0],
						'sender_user_id' => ['UINT', 0],
						'recipient_user_id' => ['UINT', 0],
						'message_text' => ['TEXT_UNI', ''],
						'message_ip' => ['VCHAR:40', ''],
						'message_time' => ['TIMESTAMP', 0],
						'message_read' => ['BOOL', 0],
						'message_reported' => ['BOOL', 0],
					],
					'PRIMARY_KEY' => 'message_id',
					'KEYS' => [
						'conversation' => ['INDEX', 'conversation_id'],
						'ad_id' => ['INDEX', 'ad_id'],
						'sender' => ['INDEX', 'sender_user_id'],
						'recipient_read' => ['INDEX', ['recipient_user_id', 'message_read']],
						'time' => ['INDEX', 'message_time'],
					],
				],
				$this->table_prefix . 'marketplace_message_blocks' => [
					'COLUMNS' => [
						'block_id' => ['UINT', null, 'auto_increment'],
						'blocker_user_id' => ['UINT', 0],
						'blocked_user_id' => ['UINT', 0],
						'ad_id' => ['UINT', 0],
						'block_reason' => ['TEXT_UNI', ''],
						'block_time' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'block_id',
					'KEYS' => [
						'block_pair_ad' => ['UNIQUE', ['blocker_user_id', 'blocked_user_id', 'ad_id']],
						'blocked_user' => ['INDEX', 'blocked_user_id'],
					],
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_message_limit_per_hour', 10]],
			['custom', [[$this, 'install_conversations_ucp_mode_safely']]],
			['config.update', ['marketplace_version', '1.4.25']],
		];
	}

	public function install_conversations_ucp_mode_safely()
	{
		global $phpbb_container;
		if (!isset($phpbb_container))
		{
			return;
		}
		if ($this->ucp_marketplace_mode_exists('conversations'))
		{
			return;
		}
		$module_tool = $phpbb_container->get('migrator.tool.module');
		if (!$this->ucp_module_langname_exists('UCP_MARKETPLACE_TITLE'))
		{
			$module_tool->add('ucp', '', 'UCP_MARKETPLACE_TITLE');
		}
		$module_tool->add('ucp', 'UCP_MARKETPLACE_TITLE', [
			'module_basename' => '\\mundophpbb\\marketplace\\ucp\\main_module',
			'modes' => ['conversations'],
		]);
	}

	protected function ucp_module_langname_exists($langname)
	{
		$sql = 'SELECT module_id FROM ' . MODULES_TABLE . " WHERE module_class = 'ucp' AND module_langname = '" . $this->db->sql_escape($langname) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$module_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);
		return $module_id > 0;
	}

	protected function ucp_marketplace_mode_exists($mode)
	{
		$basename = '\\mundophpbb\\marketplace\\ucp\\main_module';
		$sql = 'SELECT module_id FROM ' . MODULES_TABLE . " WHERE module_class = 'ucp' AND module_basename = '" . $this->db->sql_escape($basename) . "' AND module_mode = '" . $this->db->sql_escape($mode) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$module_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);
		return $module_id > 0;
	}
}
