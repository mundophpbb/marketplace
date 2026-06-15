<?php
/**
 * Marketplace 1.4.28 - Payment gateway settings and PayPal/IPN ACP tools.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_28 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_27'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.28', '>=');
	}

	public function update_data()
	{
		return [
			['config.add', ['marketplace_gateway_paypal_enabled', 1]],
			['config.add', ['marketplace_gateway_stripe_enabled', 0]],
			['config.add', ['marketplace_gateway_stripe_public_key', '']],
			['config.add', ['marketplace_gateway_stripe_secret_key', '']],
			['config.add', ['marketplace_gateway_pix_enabled', 0]],
			['config.add', ['marketplace_gateway_pix_key', '']],
			['config.add', ['marketplace_gateway_mercadopago_enabled', 0]],
			['config.add', ['marketplace_gateway_mercadopago_public_key', '']],
			['config.add', ['marketplace_gateway_mercadopago_access_token', '']],
			['config.update', ['marketplace_version', '1.4.28']],
		];
	}
}
