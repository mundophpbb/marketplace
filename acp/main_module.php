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
 * Marketplace ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Main ACP module entry point
	 *
	 * @param int    $id   Module ID
	 * @param string $mode Mode (dashboard, settings, categories, ads, packages, reports)
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');
		$language->add_lang('acp', 'mundophpbb/marketplace');

		/** @var \mundophpbb\marketplace\controller\acp_controller $acp_controller */
		$acp_controller = $phpbb_container->get('mundophpbb.marketplace.controller.acp');

		$acp_controller->set_page_url($this->u_action);

		switch ($mode)
		{
			case 'dashboard':
				$this->tpl_name = 'acp_marketplace_dashboard';
				$this->page_title = $language->lang('MARKETPLACE_ACP_DASHBOARD');
				$acp_controller->display_dashboard();
			break;

			case 'settings':
				$this->tpl_name = 'acp_marketplace_settings';
				$this->page_title = $language->lang('MARKETPLACE_ACP_SETTINGS');
				$acp_controller->display_settings();
			break;

			case 'categories':
				$this->tpl_name = 'acp_marketplace_categories';
				$this->page_title = $language->lang('MARKETPLACE_ACP_CATEGORIES');
				$acp_controller->manage_categories();
			break;

			case 'ads':
				$this->tpl_name = 'acp_marketplace_ads';
				$this->page_title = $language->lang('MARKETPLACE_ACP_ADS');
				$acp_controller->manage_ads();
			break;

			case 'notifications':
				$this->tpl_name = 'acp_marketplace_notifications';
				$this->page_title = $language->lang('MARKETPLACE_ACP_NOTIFICATIONS');
				$acp_controller->display_notifications();
			break;

			case 'payments':
				$this->tpl_name = 'acp_marketplace_payments';
				$this->page_title = $language->lang('MARKETPLACE_ACP_PAYMENTS');
				$acp_controller->display_payments();
			break;

			case 'promotions':
				$this->tpl_name = 'acp_marketplace_promotions';
				$this->page_title = $language->lang('MARKETPLACE_ACP_PROMOTIONS');
				$acp_controller->display_promotions();
			break;

			case 'financial_reports':
				$this->tpl_name = 'acp_marketplace_financial_reports';
				$this->page_title = $language->lang('MARKETPLACE_ACP_FINANCIAL_REPORTS');
				$acp_controller->display_financial_reports();
			break;

			case 'packages':
				$this->tpl_name = 'acp_marketplace_packages';
				$this->page_title = $language->lang('MARKETPLACE_ACP_PACKAGES');
				$acp_controller->manage_packages();
			break;

			case 'reports':
				$this->tpl_name = 'acp_marketplace_reports';
				$this->page_title = $language->lang('MARKETPLACE_ACP_REPORTS');
				$acp_controller->manage_reports();
			break;

			case 'security':
				$this->tpl_name = 'acp_marketplace_security';
				$this->page_title = $language->lang('MARKETPLACE_ACP_SECURITY');
				$acp_controller->display_security();
			break;

			case 'admin_logs':
				$this->tpl_name = 'acp_marketplace_admin_logs';
				$this->page_title = $language->lang('MARKETPLACE_ACP_ADMIN_LOGS');
				$acp_controller->display_admin_logs();
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}
}
