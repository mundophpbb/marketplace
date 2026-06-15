<?php
/**
 * Marketplace 1.4.27 - ACP organization modules.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_27 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.27', '>=');
	}

	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_26'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'install_acp_organization_modes']]],
			['config.update', ['marketplace_version', '1.4.27']],
		];
	}

	public function install_acp_organization_modes()
	{
		global $phpbb_container;

		if (!isset($phpbb_container))
		{
			return;
		}

		$module_tool = $phpbb_container->get('migrator.tool.module');

		if (!$this->acp_module_langname_exists('MARKETPLACE_TITLE'))
		{
			$module_tool->add('acp', 'ACP_CAT_DOT_MODS', 'MARKETPLACE_TITLE');
		}

		$modes = [];
		foreach (['payments', 'promotions', 'admin_logs'] as $mode)
		{
			if (!$this->acp_marketplace_mode_exists($mode))
			{
				$modes[] = $mode;
			}
		}

		if (!empty($modes))
		{
			$module_tool->add('acp', 'MARKETPLACE_TITLE', [
				'module_basename' => '\\mundophpbb\\marketplace\\acp\\main_module',
				'modes' => $modes,
			]);
		}
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
		$basename = '\\mundophpbb\\marketplace\\acp\\main_module';
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
}
