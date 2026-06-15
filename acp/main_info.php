<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace\acp;

/**
 * Marketplace ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\\mundophpbb\\marketplace\\acp\\main_module',
			'title'		=> 'MARKETPLACE_TITLE',
			'modes'		=> [
				'dashboard'	=> [
					'title'	=> 'MARKETPLACE_ACP_DASHBOARD',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'settings'	=> [
					'title'	=> 'MARKETPLACE_ACP_SETTINGS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'categories'	=> [
					'title'	=> 'MARKETPLACE_ACP_CATEGORIES',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'ads'	=> [
					'title'	=> 'MARKETPLACE_ACP_ADS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'notifications'	=> [
					'title'	=> 'MARKETPLACE_ACP_NOTIFICATIONS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'payments'	=> [
					'title'	=> 'MARKETPLACE_ACP_PAYMENTS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'promotions'	=> [
					'title'	=> 'MARKETPLACE_ACP_PROMOTIONS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'financial_reports'	=> [
					'title'	=> 'MARKETPLACE_ACP_FINANCIAL_REPORTS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'packages'	=> [
					'title'	=> 'MARKETPLACE_ACP_PACKAGES',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'reports'	=> [
					'title'	=> 'MARKETPLACE_ACP_REPORTS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'security'	=> [
					'title'	=> 'MARKETPLACE_ACP_SECURITY',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
				'admin_logs'	=> [
					'title'	=> 'MARKETPLACE_ACP_ADMIN_LOGS',
					'auth'	=> 'ext_mundophpbb/marketplace && acl_a_board',
					'cat'	=> ['MARKETPLACE_TITLE'],
				],
			],
		];
	}
}
