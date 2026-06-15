<?php
/**
 * Marketplace v1.4.26 - organized UCP sections.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_26 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_25'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.26', '>=');
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'install_ucp_modes_safely']]],
			['config.update', ['marketplace_version', '1.4.26']],
		];
	}

	public function install_ucp_modes_safely()
	{
		global $phpbb_container;

		if (!isset($phpbb_container))
		{
			return;
		}

		$module_tool = $phpbb_container->get('migrator.tool.module');

		if (!$this->ucp_module_langname_exists('UCP_MARKETPLACE_TITLE'))
		{
			$module_tool->add('ucp', '', 'UCP_MARKETPLACE_TITLE');
		}

		$modes = [];
		foreach (['overview', 'ads', 'promotions', 'notifications', 'purchases', 'sales', 'favorites', 'conversations', 'payments'] as $mode)
		{
			if (!$this->ucp_marketplace_mode_exists($mode))
			{
				$modes[] = $mode;
			}
		}

		if (!empty($modes))
		{
			$module_tool->add('ucp', 'UCP_MARKETPLACE_TITLE', [
				'module_basename' => '\\mundophpbb\\marketplace\\ucp\\main_module',
				'modes' => $modes,
			]);
		}
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
