<?php
/**
 * Marketplace 1.4.18 - Expand Marketplace notifications in ACP and UCP.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_18 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_17'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.18', '>=');
	}

	public function update_data()
	{
		return [
			['config.update', ['marketplace_version', '1.4.18']],
		];
	}

	public function revert_data()
	{
		return [];
	}
}
