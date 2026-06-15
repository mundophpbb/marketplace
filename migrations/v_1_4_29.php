<?php
/**
 * Marketplace 1.4.29 - Manual PIX checkout privacy and gateway settings.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_29 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_28'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.29', '>=');
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_gateway_pix_key_type', 'cpf']],
			['config.add', ['marketplace_gateway_pix_receiver_name', '']],
			['config.add', ['marketplace_gateway_pix_receiver_city', '']],
			['config.add', ['marketplace_gateway_pix_instructions', '']],
			['config.add', ['marketplace_gateway_pix_deadline_minutes', 1440]],
			['config.update', ['marketplace_version', '1.4.29']],
		];
	}
}
