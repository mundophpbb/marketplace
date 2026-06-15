<?php
/**
 * Marketplace 1.4.21 - Moderation and security.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_21 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_20'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.21', '>=')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_forbidden_terms')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_moderation_logs');
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'marketplace_categories' => [
					'cat_require_approval' => ['BOOL', 0],
				],
				$this->table_prefix . 'marketplace_ads' => [
					'ad_suspicious' => ['BOOL', 0],
					'ad_refusal_reason' => ['TEXT_UNI', ''],
					'ad_removed_at' => ['TIMESTAMP', 0],
					'ad_removed_by' => ['UINT', 0],
				],
				$this->table_prefix . 'marketplace_reports' => [
					'report_type' => ['VCHAR:20', 'ad'],
					'target_user_id' => ['UINT', 0],
					'review_id' => ['UINT', 0],
				],
			],
			'add_tables' => [
				$this->table_prefix . 'marketplace_forbidden_terms' => [
					'COLUMNS' => [
						'term_id' => ['UINT', null, 'auto_increment'],
						'term_text' => ['VCHAR:255', ''],
						'term_enabled' => ['BOOL', 1],
						'term_created' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'term_id',
					'KEYS' => ['enabled' => ['INDEX', 'term_enabled']],
				],
				$this->table_prefix . 'marketplace_user_limits' => [
					'COLUMNS' => [
						'limit_id' => ['UINT', null, 'auto_increment'],
						'user_id' => ['UINT', 0],
						'max_ads' => ['UINT', 0],
					],
					'PRIMARY_KEY' => 'limit_id',
					'KEYS' => ['user_id' => ['UNIQUE', 'user_id']],
				],
				$this->table_prefix . 'marketplace_group_limits' => [
					'COLUMNS' => [
						'limit_id' => ['UINT', null, 'auto_increment'],
						'group_id' => ['UINT', 0],
						'max_ads' => ['UINT', 0],
					],
					'PRIMARY_KEY' => 'limit_id',
					'KEYS' => ['group_id' => ['UNIQUE', 'group_id']],
				],
				$this->table_prefix . 'marketplace_user_security' => [
					'COLUMNS' => [
						'user_id' => ['UINT', 0],
						'seller_suspended' => ['BOOL', 0],
						'publish_blocked' => ['BOOL', 0],
						'verified_seller' => ['BOOL', 0],
						'security_note' => ['TEXT_UNI', ''],
						'updated_at' => ['TIMESTAMP', 0],
						'updated_by' => ['UINT', 0],
					],
					'PRIMARY_KEY' => 'user_id',
					'KEYS' => ['seller_suspended' => ['INDEX', 'seller_suspended'], 'publish_blocked' => ['INDEX', 'publish_blocked']],
				],
				$this->table_prefix . 'marketplace_ad_edit_history' => [
					'COLUMNS' => [
						'history_id' => ['UINT', null, 'auto_increment'],
						'ad_id' => ['UINT', 0],
						'user_id' => ['UINT', 0],
						'edit_time' => ['TIMESTAMP', 0],
						'edit_summary' => ['TEXT_UNI', ''],
						'old_data' => ['MTEXT_UNI', ''],
						'new_data' => ['MTEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'history_id',
					'KEYS' => ['ad_id' => ['INDEX', 'ad_id'], 'user_id' => ['INDEX', 'user_id'], 'edit_time' => ['INDEX', 'edit_time']],
				],
				$this->table_prefix . 'marketplace_moderation_logs' => [
					'COLUMNS' => [
						'log_id' => ['UINT', null, 'auto_increment'],
						'ad_id' => ['UINT', 0],
						'target_user_id' => ['UINT', 0],
						'admin_user_id' => ['UINT', 0],
						'log_action' => ['VCHAR:50', ''],
						'log_note' => ['TEXT_UNI', ''],
						'log_time' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'log_id',
					'KEYS' => ['ad_id' => ['INDEX', 'ad_id'], 'target_user_id' => ['INDEX', 'target_user_id'], 'log_time' => ['INDEX', 'log_time']],
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_security_auto_flag_forbidden', 1]],
			['custom', [[$this, 'install_security_module_safely']]],
			['config.update', ['marketplace_version', '1.4.21']],
		];
	}


	public function install_security_module_safely()
	{
		global $phpbb_container;

		if (!isset($phpbb_container) || $this->acp_marketplace_mode_exists('security'))
		{
			return;
		}

		$module_tool = $phpbb_container->get('migrator.tool.module');

		if (!$this->acp_module_langname_exists('MARKETPLACE_TITLE'))
		{
			$module_tool->add('acp', 'ACP_CAT_DOT_MODS', 'MARKETPLACE_TITLE');
		}

		$module_tool->add('acp', 'MARKETPLACE_TITLE', [
			'module_basename' => '\mundophpbb\marketplace\acp\main_module',
			'modes' => ['security'],
		]);
	}

	protected function acp_module_langname_exists($langname)
	{
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'acp'
				AND module_langname = '" . $this->db->sql_escape($langname) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$module_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id > 0;
	}

	protected function acp_marketplace_mode_exists($mode)
	{
		$basename = '\mundophpbb\marketplace\acp\main_module';
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'acp'
				AND module_basename = '" . $this->db->sql_escape($basename) . "'
				AND module_mode = '" . $this->db->sql_escape($mode) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$module_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id > 0;
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'marketplace_categories' => ['cat_require_approval'],
				$this->table_prefix . 'marketplace_ads' => ['ad_suspicious', 'ad_refusal_reason', 'ad_removed_at', 'ad_removed_by'],
				$this->table_prefix . 'marketplace_reports' => ['report_type', 'target_user_id', 'review_id'],
			],
			'drop_tables' => [
				$this->table_prefix . 'marketplace_forbidden_terms',
				$this->table_prefix . 'marketplace_user_limits',
				$this->table_prefix . 'marketplace_group_limits',
				$this->table_prefix . 'marketplace_user_security',
				$this->table_prefix . 'marketplace_ad_edit_history',
				$this->table_prefix . 'marketplace_moderation_logs',
			],
		];
	}
}
