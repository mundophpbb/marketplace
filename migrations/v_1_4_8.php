<?php
/**
 * Marketplace / Classificados Extension for phpBB.
 * Adds PayPal payment handoff for promotion packages.
 */

namespace mundophpbb\marketplace\migrations;

class v_1_4_8 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_7'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.8', '>=');
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_paypal_enabled', 0]],
			['config.add', ['marketplace_paypal_sandbox', 1]],
			['config.add', ['marketplace_paypal_business', '']],
			['config.add', ['marketplace_paypal_currency', 'BRL']],
			['config.update', ['marketplace_version', '1.4.8']],
		];
	}
}
