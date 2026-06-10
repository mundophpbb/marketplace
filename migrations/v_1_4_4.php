<?php
/**
 * Marketplace 1.4.4 - featured ads frontend controls.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_4 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_3'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_featured_frontend']);
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_allow_featured', 1]],
			['config.add', ['marketplace_featured_frontend', 1]],
			['config.update', ['marketplace_version', '1.4.4']],
		];
	}

	public function revert_data()
	{
		return [];
	}
}
