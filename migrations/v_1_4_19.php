<?php
/**
 * Marketplace 1.4.19 - Sales and orders UCP areas.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_19 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\\mundophpbb\\marketplace\\migrations\\v_1_4_18',
			'\\mundophpbb\\marketplace\\migrations\\install_ucp_module',
		];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.19', '>=');
	}

	public function update_data()
	{
		return [
			['config.update', ['marketplace_version', '1.4.19']],
		];
	}
	public function revert_data()
	{
		return [];
	}
}
