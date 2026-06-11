<?php
/**
 * Marketplace 1.4.15 - Separate Marketplace notifications areas.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_15 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\\mundophpbb\\marketplace\\migrations\\v_1_4_14',
			'\\mundophpbb\\marketplace\\migrations\\install_ucp_module',
		];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.15', '>=');
	}

	public function update_data()
	{
		return [
			['module.add', [
				'ucp',
				'UCP_MARKETPLACE_TITLE',
				[
					'module_basename' => '\mundophpbb\marketplace\ucp\main_module',
					'modes' => ['notifications'],
				],
			]],
			['config.update', ['marketplace_version', '1.4.15']],
		];
	}

	public function revert_data()
	{
		return [];
	}
}
