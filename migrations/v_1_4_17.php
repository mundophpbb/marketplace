<?php
/**
 * Marketplace 1.4.17 - Register ACP financial reports module.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_17 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_16'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.17', '>=');
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'install_financial_reports_module_safely']]],
			['config.update', ['marketplace_version', '1.4.17']],
		];
	}


	public function install_financial_reports_module_safely()
	{
		global $phpbb_container;

		if (!isset($phpbb_container) || $this->acp_marketplace_mode_exists('financial_reports'))
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
			'modes' => ['financial_reports'],
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

	public function revert_data()
	{
		return [];
	}
}
