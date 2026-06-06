<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace\migrations;

class install_ucp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['marketplace_ucp_module_installed']);
	}

	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\install_data'];
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_ucp_module_installed', 1]],

			['module.add', [
				'ucp',
				'',
				'UCP_MARKETPLACE_TITLE'
			]],
			['module.add', [
				'ucp',
				'UCP_MARKETPLACE_TITLE',
				[
					'module_basename'	=> '\mundophpbb\marketplace\ucp\main_module',
					'modes'				=> ['overview'],
				],
			]],
		];
	}
	public function revert_data()
	{
		return [];
	}


}
