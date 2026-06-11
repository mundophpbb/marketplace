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
			case 'notifications':
				$this->tpl_name = 'ucp_marketplace_notifications';
				$this->page_title = $language->lang('UCP_MARKETPLACE_NOTIFICATIONS');
				$ucp_controller->notifications();
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
