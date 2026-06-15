<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace\ucp;

/**
 * Marketplace UCP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\mundophpbb\marketplace\ucp\main_module',
			'title'		=> 'UCP_MARKETPLACE_TITLE',
			'modes'		=> [
				'overview'	=> [
					'title'	=> 'UCP_MARKETPLACE_OVERVIEW',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_u_marketplace_view',
					'cat'	=> ['UCP_MARKETPLACE_TITLE'],
				],

				'ads'	=> [
					'title'	=> 'UCP_MARKETPLACE_ADS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_u_marketplace_view',
					'cat'	=> ['UCP_MARKETPLACE_TITLE'],
				],
				'promotions'	=> [
					'title'	=> 'UCP_MARKETPLACE_PROMOTIONS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_u_marketplace_view',
					'cat'	=> ['UCP_MARKETPLACE_TITLE'],
				],
				'notifications'	=> [
					'title'	=> 'UCP_MARKETPLACE_NOTIFICATIONS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_u_marketplace_view',
					'cat'	=> ['UCP_MARKETPLACE_TITLE'],
				],
				'purchases'	=> [
					'title'	=> 'UCP_MARKETPLACE_PURCHASES',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_u_marketplace_view',
					'cat'	=> ['UCP_MARKETPLACE_TITLE'],
				],
				'sales'	=> [
					'title'	=> 'UCP_MARKETPLACE_SALES',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_u_marketplace_view',
					'cat'	=> ['UCP_MARKETPLACE_TITLE'],
				],

				'favorites'	=> [
					'title'	=> 'UCP_MARKETPLACE_FAVORITES',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_u_marketplace_view',
					'cat'	=> ['UCP_MARKETPLACE_TITLE'],
				],
				'conversations'	=> [
					'title'	=> 'UCP_MARKETPLACE_CONVERSATIONS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_u_marketplace_view',
					'cat'	=> ['UCP_MARKETPLACE_TITLE'],
				],

				'payments'	=> [
					'title'	=> 'UCP_MARKETPLACE_PAYMENTS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_u_marketplace_view',
					'cat'	=> ['UCP_MARKETPLACE_TITLE'],
				],
			],
		];
	}
}
