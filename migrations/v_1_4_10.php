<?php
/**
 * Marketplace 1.4.10 - separate PayPal Sandbox Business account.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_10 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\v_1_4_9'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.10', '>=');
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_paypal_sandbox_business', '']],
			['config.update', ['marketplace_version', '1.4.10']],
		];
	}
}
