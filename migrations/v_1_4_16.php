<?php
/**
 * Marketplace 1.4.16 - Register ACP Marketplace notifications module.
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
			['module.add', [
				'acp',
				'MARKETPLACE_TITLE',
				[
					'module_basename' => '\mundophpbb\marketplace\acp\main_module',
					'modes' => ['notifications'],
				],
			]],
			['config.update', ['marketplace_version', '1.4.16']],
		];
	}

	public function revert_data()
	{
		return [];
	}
}
