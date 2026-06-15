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
 * Marketplace UCP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');
		$language->add_lang(['common', 'ucp'], 'mundophpbb/marketplace');

		/** @var \mundophpbb\marketplace\controller\ucp_controller $ucp_controller */
		$ucp_controller = $phpbb_container->get('mundophpbb.marketplace.controller.ucp');

		$ucp_controller->set_page_url($this->u_action);

		switch ($mode)
		{

			case 'ads':
				$this->tpl_name = 'ucp_marketplace_ads';
				$this->page_title = $language->lang('UCP_MARKETPLACE_ADS');
				$ucp_controller->ads();
			break;

			case 'promotions':
				$this->tpl_name = 'ucp_marketplace_promotions';
				$this->page_title = $language->lang('UCP_MARKETPLACE_PROMOTIONS');
				$ucp_controller->promotions();
			break;

			case 'purchases':
				$this->tpl_name = 'ucp_marketplace_purchases';
				$this->page_title = $language->lang('UCP_MARKETPLACE_PURCHASES');
				$ucp_controller->purchases();
			break;

			case 'sales':
				$this->tpl_name = 'ucp_marketplace_sales';
				$this->page_title = $language->lang('UCP_MARKETPLACE_SALES');
				$ucp_controller->sales();
			break;

			case 'notifications':
				$this->tpl_name = 'ucp_marketplace_notifications';
				$this->page_title = $language->lang('UCP_MARKETPLACE_NOTIFICATIONS');
				$ucp_controller->notifications();
			break;


			case 'favorites':
				$this->tpl_name = 'ucp_marketplace_favorites';
				$this->page_title = $language->lang('UCP_MARKETPLACE_FAVORITES');
				$ucp_controller->favorites();
			break;

			case 'conversations':
				$this->tpl_name = 'ucp_marketplace_conversations';
				$this->page_title = $language->lang('UCP_MARKETPLACE_CONVERSATIONS');
				$ucp_controller->conversations();
			break;


			case 'payments':
				$this->tpl_name = 'ucp_marketplace_payments';
				$this->page_title = $language->lang('UCP_MARKETPLACE_PAYMENTS');
				$ucp_controller->payments();
			break;

			case 'overview':
			default:
				$this->tpl_name = 'ucp_marketplace_overview';
				$this->page_title = $language->lang('UCP_MARKETPLACE_TITLE');
				$ucp_controller->main();
			break;
		}
	}
}
