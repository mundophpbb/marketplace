<?php
/**
 * Marketplace 1.4.16 - ACP Marketplace notifications module version marker.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_16 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_15'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.16', '>=');
	}

	public function update_data()
	{
		return [
			// The notifications ACP mode is already registered by install_data.php in this package.
			// Keeping another module.add here makes fresh installs fail with "module already exists".
			['config.update', ['marketplace_version', '1.4.16']],
		];
	}

	public function revert_data()
	{
		return [];
	}
}
