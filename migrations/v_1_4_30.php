<?php
/**
 * Marketplace 1.4.30 - PIX QR Code support.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_30 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.30', '>=');
	}

	static public function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_29'];
	}

	public function update_data()
	{
		return [
			['config.update', ['marketplace_version', '1.4.30']],
		];
	}
}
