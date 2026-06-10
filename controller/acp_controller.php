<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace\controller;

/**
 * Marketplace ACP controller.
 */
class acp_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var string */
	protected $u_action;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $upload_path;

	/** @var string */
	protected $table_ads;

	/** @var string */
	protected $table_cats;

	/** @var string */
	protected $table_images;

	/** @var string */
	protected $table_reports;

	/** @var string */
	protected $table_notifications;

	/** @var string */
	protected $table_promotions;

	/** @var string */
	protected $table_promotion_packages;

	/** @var string */
	protected $table_purchases;


	/** @var array */
	protected $column_exists_cache = [];

	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\controller\helper $helper,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\pagination $pagination,
		$root_path,
		$table_ads,
		$table_cats,
		$table_images,
		$table_reports,
		$table_notifications,
		$table_promotions,
		$table_promotion_packages,
		$table_purchases
	)
	{
		$this->config     = $config;
		$this->language   = $language;
		$this->helper     = $helper;
		$this->log        = $log;
		$this->request    = $request;
		$this->template   = $template;
		$this->user       = $user;
		$this->db         = $db;
		$this->pagination = $pagination;
		$this->root_path  = $root_path;
		$this->upload_path = $root_path . 'files/marketplace/';
		$this->table_ads  = $table_ads;
		$this->table_cats = $table_cats;
		$this->table_images = $table_images;
		$this->table_reports = $table_reports;
		$this->table_notifications = $table_notifications;
		$this->table_promotions = $table_promotions;
		$this->table_promotion_packages = $table_promotion_packages;
		$this->table_purchases = $table_purchases;
	}

	/**
	 * Display and process settings page.
	 */
	public function display_settings()
	{
		$this->language->add_lang('acp', 'mundophpbb/marketplace');

		\add_form_key('mundophpbb_marketplace_acp_settings');

		$errors = [];

		if ($this->request->is_set_post('submit'))
		{
			if (!\check_form_key('mundophpbb_marketplace_acp_settings'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			if (empty($errors))
			{
				$this->config->set('marketplace_enabled', $this->request->variable('marketplace_enabled', 0));
				$this->config->set('marketplace_require_approval', $this->request->variable('marketplace_require_approval', 0));
				$this->config->set('marketplace_max_ads_per_user', $this->request->variable('marketplace_max_ads_per_user', 10));
				$this->config->set('marketplace_ad_expiration_days', $this->request->variable('marketplace_ad_expiration_days', 30));
				$this->config->set('marketplace_max_images', $this->request->variable('marketplace_max_images', 5));
				$this->config->set('marketplace_items_per_page', $this->request->variable('marketplace_items_per_page', 20));
				$this->config->set('marketplace_allow_images', $this->request->variable('marketplace_allow_images', 0));
				$this->config->set('marketplace_enable_price', $this->request->variable('marketplace_enable_price', 0));
				$this->config->set('marketplace_currency_default', $this->request->variable('marketplace_currency_default', 'R$', true));
				$this->config->set('marketplace_show_sold_ads', $this->request->variable('marketplace_show_sold_ads', 0));
				$this->config->set('marketplace_sold_visible_days', max(0, $this->request->variable('marketplace_sold_visible_days', 15)));
				$this->config->set('marketplace_allow_reports', $this->request->variable('marketplace_allow_reports', 1));
				$this->config->set('marketplace_allow_follows', $this->request->variable('marketplace_allow_follows', 1));
				$this->config->set('marketplace_allow_bump', $this->request->variable('marketplace_allow_bump', 1));
				$this->config->set('marketplace_bump_interval_days', max(0, $this->request->variable('marketplace_bump_interval_days', 7)));
				$this->config->set('marketplace_allow_featured', $this->request->variable('marketplace_allow_featured', 1));
				$this->config->set('marketplace_featured_days', max(1, $this->request->variable('marketplace_featured_days', $this->request->variable('marketplace_featured_days_default', 14))));
				$this->config->set('marketplace_allow_boosted', $this->request->variable('marketplace_allow_boosted', 1));
				$this->config->set('marketplace_allow_promotion_requests', $this->request->variable('marketplace_allow_promotion_requests', 1));
				$this->config->set('marketplace_boosted_days', max(1, $this->request->variable('marketplace_boosted_days', $this->request->variable('marketplace_boosted_days_default', 7))));
				$this->config->set('marketplace_paypal_enabled', $this->request->variable('marketplace_paypal_enabled', 0));
				$this->config->set('marketplace_direct_purchase_enabled', $this->request->variable('marketplace_direct_purchase_enabled', 0));
				$this->config->set('marketplace_paypal_sandbox', $this->request->variable('marketplace_paypal_sandbox', 1));
				$this->config->set('marketplace_paypal_business', $this->request->variable('marketplace_paypal_business', '', true));
				$this->config->set('marketplace_paypal_sandbox_business', $this->request->variable('marketplace_paypal_sandbox_business', '', true));
				$paypal_currency = strtoupper($this->request->variable('marketplace_paypal_currency', 'BRL'));
				$paypal_currency_options = array_keys($this->get_common_currency_options());
				if (!in_array($paypal_currency, $paypal_currency_options, true))
				{
					$paypal_currency = 'BRL';
				}
				$this->config->set('marketplace_paypal_currency', $paypal_currency);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_SETTINGS');

				\trigger_error($this->language->lang('MARKETPLACE_SETTINGS_SAVED') . \adm_back_link($this->u_action));
			}
		}

		$s_errors = (bool) count($errors);

		$this->template->assign_vars([
			'S_ERROR'          => $s_errors,
			'ERROR_MSG'        => $s_errors ? implode('<br />', $errors) : '',

			'U_ACTION'         => $this->u_action,

			'MARKETPLACE_ENABLED'            => (bool) $this->config['marketplace_enabled'],
			'MARKETPLACE_REQUIRE_APPROVAL'   => (bool) $this->config['marketplace_require_approval'],
			'MARKETPLACE_MAX_ADS_PER_USER'   => (int) $this->config['marketplace_max_ads_per_user'],
			'MARKETPLACE_AD_EXPIRATION_DAYS' => (int) $this->config['marketplace_ad_expiration_days'],
			'MARKETPLACE_MAX_IMAGES'         => (int) $this->config['marketplace_max_images'],
			'MARKETPLACE_ITEMS_PER_PAGE'     => (int) $this->config['marketplace_items_per_page'],
			'MARKETPLACE_ALLOW_IMAGES'       => (bool) $this->config['marketplace_allow_images'],
			'MARKETPLACE_ENABLE_PRICE'       => (bool) $this->config['marketplace_enable_price'],
			'MARKETPLACE_CURRENCY_DEFAULT'   => $this->config['marketplace_currency_default'],
			'MARKETPLACE_SHOW_SOLD_ADS'       => !empty($this->config['marketplace_show_sold_ads']),
			'MARKETPLACE_SOLD_VISIBLE_DAYS'   => isset($this->config['marketplace_sold_visible_days']) ? (int) $this->config['marketplace_sold_visible_days'] : 15,
			'MARKETPLACE_ALLOW_REPORTS'      => !empty($this->config['marketplace_allow_reports']),
			'MARKETPLACE_ALLOW_FOLLOWS'      => !isset($this->config['marketplace_allow_follows']) || !empty($this->config['marketplace_allow_follows']),
			'MARKETPLACE_ALLOW_BUMP'         => !empty($this->config['marketplace_allow_bump']),
			'MARKETPLACE_BUMP_INTERVAL_DAYS' => isset($this->config['marketplace_bump_interval_days']) ? (int) $this->config['marketplace_bump_interval_days'] : 7,
			'MARKETPLACE_ALLOW_FEATURED'     => !isset($this->config['marketplace_allow_featured']) || !empty($this->config['marketplace_allow_featured']),
			'MARKETPLACE_FEATURED_DAYS'      => isset($this->config['marketplace_featured_days']) ? (int) $this->config['marketplace_featured_days'] : 14,
			'MARKETPLACE_FEATURED_DAYS_DEFAULT' => isset($this->config['marketplace_featured_days']) ? (int) $this->config['marketplace_featured_days'] : 14,
			'MARKETPLACE_ALLOW_BOOSTED'      => !isset($this->config['marketplace_allow_boosted']) || !empty($this->config['marketplace_allow_boosted']),
			'MARKETPLACE_ALLOW_PROMOTION_REQUESTS' => !isset($this->config['marketplace_allow_promotion_requests']) || !empty($this->config['marketplace_allow_promotion_requests']),
			'MARKETPLACE_BOOSTED_DAYS'       => isset($this->config['marketplace_boosted_days']) ? (int) $this->config['marketplace_boosted_days'] : 7,
			'MARKETPLACE_BOOSTED_DAYS_DEFAULT' => isset($this->config['marketplace_boosted_days']) ? (int) $this->config['marketplace_boosted_days'] : 7,
			'MARKETPLACE_PAYPAL_ENABLED' => !empty($this->config['marketplace_paypal_enabled']),
			'MARKETPLACE_DIRECT_PURCHASE_ENABLED' => !empty($this->config['marketplace_direct_purchase_enabled']),
			'MARKETPLACE_PAYPAL_SANDBOX' => !isset($this->config['marketplace_paypal_sandbox']) || !empty($this->config['marketplace_paypal_sandbox']),
			'MARKETPLACE_PAYPAL_BUSINESS' => isset($this->config['marketplace_paypal_business']) ? (string) $this->config['marketplace_paypal_business'] : '',
			'MARKETPLACE_PAYPAL_SANDBOX_BUSINESS' => isset($this->config['marketplace_paypal_sandbox_business']) ? (string) $this->config['marketplace_paypal_sandbox_business'] : '',
			'MARKETPLACE_PAYPAL_CURRENCY' => isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL',
			'PAYPAL_CURRENCY_OPTIONS' => $this->build_currency_select_options(isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL'),
		]);
	}


	/**
	 * Manage promotion packages.
	 */
	public function manage_packages()
	{
		$this->language->add_lang('acp', 'mundophpbb/marketplace');
		\add_form_key('mundophpbb_marketplace_acp_packages');

		$action = $this->request->variable('action', '');
		$package_id = $this->request->variable('package_id', 0);

		if ($action === 'delete' && $package_id)
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_packages'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->db->sql_query('DELETE FROM ' . $this->table_promotion_packages . ' WHERE package_id = ' . (int) $package_id);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_PACKAGE_DELETED', false, ['package_id' => $package_id]);
			\trigger_error($this->language->lang('MARKETPLACE_PACKAGE_DELETED') . \adm_back_link($this->u_action));
		}

		if ($action === 'toggle' && $package_id)
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_packages'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT package_enabled FROM ' . $this->table_promotion_packages . ' WHERE package_id = ' . (int) $package_id;
			$result = $this->db->sql_query($sql);
			$enabled = (int) $this->db->sql_fetchfield('package_enabled');
			$this->db->sql_freeresult($result);

			$this->db->sql_query('UPDATE ' . $this->table_promotion_packages . ' SET package_enabled = ' . ($enabled ? 0 : 1) . ', package_updated = ' . time() . ' WHERE package_id = ' . (int) $package_id);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_PACKAGE_TOGGLED', false, ['package_id' => $package_id]);
			\trigger_error($this->language->lang('MARKETPLACE_PACKAGE_SAVED') . \adm_back_link($this->u_action));
		}

		if ($this->request->is_set_post('submit'))
		{
			if (!\check_form_key('mundophpbb_marketplace_acp_packages'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$now = time();
			$sql_ary = [
				'package_title'        => $this->request->variable('package_title', '', true),
				'package_desc'         => $this->request->variable('package_desc', '', true),
				'package_type'         => $this->request->variable('package_type', 'featured'),
				'package_days'         => max(1, $this->request->variable('package_days', 7)),
				'package_amount_cents' => max(0, $this->request->variable('package_amount_cents', 0)),
				'package_currency'     => $this->normalise_package_currency($this->request->variable('package_currency', isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL')),
				'package_enabled'      => $this->request->variable('package_enabled', 1),
				'package_order'        => max(0, $this->request->variable('package_order', 0)),
				'package_updated'      => $now,
			];

			if (!in_array($sql_ary['package_type'], ['featured', 'boosted', 'renewal'], true))
			{
				$sql_ary['package_type'] = 'featured';
			}

			if ((string) $sql_ary['package_title'] === '')
			{
				\trigger_error($this->language->lang('MARKETPLACE_PACKAGE_TITLE_REQUIRED') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($package_id)
			{
				$this->db->sql_query('UPDATE ' . $this->table_promotion_packages . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE package_id = ' . (int) $package_id);
			}
			else
			{
				$sql_ary['package_created'] = $now;
				$this->db->sql_query('INSERT INTO ' . $this->table_promotion_packages . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
			}

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_PACKAGE_SAVED');
			\trigger_error($this->language->lang('MARKETPLACE_PACKAGE_SAVED') . \adm_back_link($this->u_action));
		}

		$edit_package = $package_id ? $this->get_package($package_id) : [];
		$packages = $this->get_packages();

		$this->template->assign_vars([
			'U_ACTION' => $this->u_action,
			'PACKAGES' => $packages,
			'EDIT_PACKAGE' => $edit_package ?: [
				'package_id' => 0,
				'package_title' => '',
				'package_desc' => '',
				'package_type' => 'featured',
				'package_days' => 7,
				'package_amount_cents' => 0,
				'package_currency' => isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL',
				'package_enabled' => 1,
				'package_order' => 0,
			],
			'PACKAGE_CURRENCY_OPTIONS' => $this->build_currency_select_options($edit_package && !empty($edit_package['package_currency']) ? (string) $edit_package['package_currency'] : (isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL')),
			'S_EDIT_PACKAGE' => !empty($edit_package),
		]);
	}

	private function get_common_currency_options()
	{
		return [
			'BRL' => 'BRL - Real brasileiro',
			'USD' => 'USD - US Dollar',
			'EUR' => 'EUR - Euro',
			'GBP' => 'GBP - British Pound',
			'CAD' => 'CAD - Canadian Dollar',
			'AUD' => 'AUD - Australian Dollar',
			'MXN' => 'MXN - Mexican Peso',
			'ARS' => 'ARS - Argentine Peso',
			'CLP' => 'CLP - Chilean Peso',
			'COP' => 'COP - Colombian Peso',
			'PEN' => 'PEN - Peruvian Sol',
			'UYU' => 'UYU - Uruguayan Peso',
			'JPY' => 'JPY - Japanese Yen',
			'CHF' => 'CHF - Swiss Franc',
		];
	}

	private function build_currency_select_options($selected_currency)
	{
		$selected_currency = strtoupper((string) $selected_currency);
		$options = [];

		foreach ($this->get_common_currency_options() as $code => $label)
		{
			$options[] = [
				'CODE' => $code,
				'LABEL' => $label,
				'S_SELECTED' => $selected_currency === $code,
			];
		}

		return $options;
	}

	private function normalise_package_currency($currency)
	{
		$currency = strtoupper((string) $currency);
		$currency_options = array_keys($this->get_common_currency_options());

		return in_array($currency, $currency_options, true) ? $currency : 'BRL';
	}

	private function get_packages()
	{
		$sql = 'SELECT * FROM ' . $this->table_promotion_packages . ' ORDER BY package_order ASC, package_id ASC';
		$result = $this->db->sql_query($sql);
		$packages = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PACKAGE_TYPE_LANG'] = $this->get_promotion_type_lang($row['package_type']);
			$row['PACKAGE_PRICE_DISPLAY'] = $this->format_package_price((int) $row['package_amount_cents'], $row['package_currency']);
			$row['U_EDIT'] = $this->u_action . '&amp;action=edit&amp;package_id=' . (int) $row['package_id'];
			$packages[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $packages;
	}

	private function get_package($package_id)
	{
		$sql = 'SELECT * FROM ' . $this->table_promotion_packages . ' WHERE package_id = ' . (int) $package_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $row;
	}

	private function format_package_price($amount_cents, $currency)
	{
		if ((int) $amount_cents <= 0)
		{
			return $this->language->lang('MARKETPLACE_PACKAGE_FREE_MANUAL');
		}
		return trim((string) $currency . ' ' . number_format(((int) $amount_cents) / 100, 2, ',', '.'));
	}

	/**
	 * Manage categories.
	 */
	public function manage_categories()
	{
		$this->language->add_lang('acp', 'mundophpbb/marketplace');

		\add_form_key('mundophpbb_marketplace_acp_cats');

		$action = $this->request->variable('action', '');
		$cat_id = $this->request->variable('cat_id', 0);

		if ($action === 'toggle' && $cat_id)
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_cats'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT cat_enabled FROM ' . $this->table_cats . ' WHERE cat_id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$current_enabled = (int) $this->db->sql_fetchfield('cat_enabled');
			$this->db->sql_freeresult($result);

			$new_enabled = $current_enabled ? 0 : 1;
			$this->db->sql_query('UPDATE ' . $this->table_cats . ' SET cat_enabled = ' . (int) $new_enabled . ' WHERE cat_id = ' . (int) $cat_id);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $new_enabled ? 'LOG_MARKETPLACE_CAT_ENABLED' : 'LOG_MARKETPLACE_CAT_DISABLED', false, ['cat_id' => $cat_id]);
			$msg = $new_enabled ? 'MARKETPLACE_CAT_ENABLED_MSG' : 'MARKETPLACE_CAT_DISABLED_MSG';
			\trigger_error($this->language->lang($msg) . \adm_back_link($this->u_action));
		}

		if ($action === 'delete' && $cat_id)
		{
			$is_confirmed = \confirm_box(true);
			if (!$is_confirmed && (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_cats')))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($is_confirmed)
			{
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET cat_id = 0 WHERE cat_id = ' . (int) $cat_id);
				$this->db->sql_query('DELETE FROM ' . $this->table_cats . ' WHERE cat_id = ' . (int) $cat_id);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_CAT_DELETED', false, ['cat_id' => $cat_id]);
				\trigger_error($this->language->lang('MARKETPLACE_CAT_DELETED') . \adm_back_link($this->u_action));
			}
			else
			{
				\confirm_box(false, $this->language->lang('CONFIRM_DELETE_CAT'), \build_hidden_fields([
					'i'       => $this->request->variable('i', ''),
					'mode'    => 'categories',
					'action'  => 'delete',
					'cat_id'  => $cat_id,
				]));
			}
		}

		if ($this->request->is_set_post('submit'))
		{
			if (!\check_form_key('mundophpbb_marketplace_acp_cats'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$cat_name = $this->request->variable('cat_name', '', true);
			$cat_desc = $this->request->variable('cat_desc', '', true);

			// Default bundled categories are stored as language keys in the database.
			// Show translated text in ACP fields, but preserve the raw key when the admin saves
			// without changing that text, so categories remain multilingual.
			if ($cat_id)
			{
				$sql = 'SELECT cat_name, cat_desc FROM ' . $this->table_cats . ' WHERE cat_id = ' . (int) $cat_id;
				$result = $this->db->sql_query($sql);
				$current_cat = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($current_cat)
				{
					$current_name = isset($current_cat['cat_name']) ? $current_cat['cat_name'] : '';
					$current_desc = isset($current_cat['cat_desc']) ? $current_cat['cat_desc'] : '';

					if ($this->is_category_language_key($current_name) && $cat_name === $this->translate_category_text($current_name))
					{
						$cat_name = $current_name;
					}

					if ($this->is_category_language_key($current_desc) && $cat_desc === $this->translate_category_text($current_desc))
					{
						$cat_desc = $current_desc;
					}
				}
			}

			$cat_order = $this->request->variable('cat_order', 10);
			$cat_enabled = $this->request->variable('cat_enabled', 1);
			$cat_expiration_days = max(0, $this->request->variable('cat_expiration_days', 0));
			$cat_require_price = $this->request->variable('cat_require_price', 0);
			$cat_require_location = $this->request->variable('cat_require_location', 0);
			$cat_require_phone = $this->request->variable('cat_require_phone', 0);
			$cat_allow_price = $this->request->variable('cat_allow_price', 1);
			$cat_allow_images = $this->request->variable('cat_allow_images', 1);
			$cat_allowed_types = $this->sanitize_allowed_types($this->request->variable('cat_allowed_types', [0]));

			if ($cat_name === '')
			{
				\trigger_error($this->language->lang('MARKETPLACE_CAT_NAME_REQUIRED') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($cat_id)
			{
				$sql_ary = [
					'cat_name'             => $cat_name,
					'cat_desc'             => $cat_desc,
					'cat_order'            => $cat_order,
					'cat_enabled'          => $cat_enabled,
					'cat_expiration_days'  => $cat_expiration_days,
					'cat_require_price'    => $cat_require_price,
					'cat_require_location' => $cat_require_location,
					'cat_require_phone'    => $cat_require_phone,
					'cat_allow_price'      => $cat_allow_price,
					'cat_allow_images'     => $cat_allow_images,
					'cat_allowed_types'    => $cat_allowed_types,
				];
				$sql = 'UPDATE ' . $this->table_cats . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE cat_id = ' . (int) $cat_id;
				$this->db->sql_query($sql);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_CAT_EDITED');
				$msg = $this->language->lang('MARKETPLACE_CAT_UPDATED');
			}
			else
			{
				$sql_ary = [
					'cat_name'             => $cat_name,
					'cat_desc'             => $cat_desc,
					'cat_order'            => $cat_order,
					'cat_enabled'          => $cat_enabled,
					'cat_expiration_days'  => $cat_expiration_days,
					'cat_require_price'    => $cat_require_price,
					'cat_require_location' => $cat_require_location,
					'cat_require_phone'    => $cat_require_phone,
					'cat_allow_price'      => $cat_allow_price,
					'cat_allow_images'     => $cat_allow_images,
					'cat_allowed_types'    => $cat_allowed_types,
				];
				$sql = 'INSERT INTO ' . $this->table_cats . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
				$this->db->sql_query($sql);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_CAT_ADDED');
				$msg = $this->language->lang('MARKETPLACE_CAT_ADDED');
			}

			\trigger_error($msg . \adm_back_link($this->u_action));
		}

		$cat_data = [];
		if ($cat_id)
		{
			$sql = 'SELECT * FROM ' . $this->table_cats . ' WHERE cat_id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$cat_data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
		}

		$categories = [];
		$sql = 'SELECT * FROM ' . $this->table_cats . ' ORDER BY cat_order ASC, cat_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$cat_counts = [
				'total' => 0,
				'pending' => 0,
				'active' => 0,
				'sold' => 0,
				'expired' => 0,
				'hidden' => 0,
			];
			$sql2 = 'SELECT ad_status, COUNT(*) as cnt FROM ' . $this->table_ads . ' WHERE cat_id = ' . (int) $row['cat_id'] . ' GROUP BY ad_status';
			$res2 = $this->db->sql_query($sql2);
			while ($count_row = $this->db->sql_fetchrow($res2))
			{
				$status = (int) $count_row['ad_status'];
				$count = (int) $count_row['cnt'];
				$cat_counts['total'] += $count;
				if ($status === 0) { $cat_counts['pending'] = $count; }
				else if ($status === 1) { $cat_counts['active'] = $count; }
				else if ($status === 2) { $cat_counts['sold'] = $count; }
				else if ($status === 3) { $cat_counts['expired'] = $count; }
				else if ($status === 4) { $cat_counts['hidden'] = $count; }
			}
			$this->db->sql_freeresult($res2);

			$row['CAT_NAME_RAW'] = isset($row['cat_name']) ? $row['cat_name'] : '';
			$row['CAT_DESC_RAW'] = isset($row['cat_desc']) ? $row['cat_desc'] : '';
			$row['CAT_NAME_DISPLAY'] = $this->translate_category_text($row['CAT_NAME_RAW']);
			$row['CAT_DESC_DISPLAY'] = $this->translate_category_text($row['CAT_DESC_RAW']);
			$row['ADS_COUNT'] = $cat_counts['total'];
			$row['ADS_ACTIVE_COUNT'] = $cat_counts['active'];
			$row['ADS_PENDING_COUNT'] = $cat_counts['pending'];
			$row['ADS_SOLD_COUNT'] = $cat_counts['sold'];
			$row['ADS_EXPIRED_COUNT'] = $cat_counts['expired'];
			$row['ADS_HIDDEN_COUNT'] = $cat_counts['hidden'];
			$row['S_CATEGORY_EMPTY'] = ($cat_counts['total'] === 0);
			$row['CAT_ALLOWED_TYPES_DISPLAY'] = $this->format_allowed_types(isset($row['cat_allowed_types']) ? $row['cat_allowed_types'] : '1,2,3,4,5,6');
			$categories[] = $row;
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'U_ACTION'   => $this->u_action,
			'U_BACK'     => $this->u_action,

			'CAT_ID'     => $cat_id,
			'CAT_NAME'   => isset($cat_data['cat_name']) ? $this->translate_category_text($cat_data['cat_name']) : '',
			'CAT_DESC'   => isset($cat_data['cat_desc']) ? $this->translate_category_text($cat_data['cat_desc']) : '',
			'CAT_ORDER'  => $cat_data['cat_order'] ?? 10,
			'CAT_ENABLED'=> isset($cat_data['cat_enabled']) ? (bool) $cat_data['cat_enabled'] : true,
			'CAT_EXPIRATION_DAYS' => isset($cat_data['cat_expiration_days']) ? (int) $cat_data['cat_expiration_days'] : 0,
			'CAT_REQUIRE_PRICE' => !empty($cat_data['cat_require_price']),
			'CAT_REQUIRE_LOCATION' => !empty($cat_data['cat_require_location']),
			'CAT_REQUIRE_PHONE' => !empty($cat_data['cat_require_phone']),
			'CAT_ALLOW_PRICE' => !isset($cat_data['cat_allow_price']) || !empty($cat_data['cat_allow_price']),
			'CAT_ALLOW_IMAGES' => !isset($cat_data['cat_allow_images']) || !empty($cat_data['cat_allow_images']),
			'CAT_ALLOWED_TYPES' => isset($cat_data['cat_allowed_types']) ? $cat_data['cat_allowed_types'] : '1,2,3,4,5,6',
			'CAT_TYPE_OPTIONS' => $this->get_category_type_options(isset($cat_data['cat_allowed_types']) ? $cat_data['cat_allowed_types'] : '1,2,3,4,5,6'),

			'S_EDIT'     => (bool) $cat_id,
			'S_ADD'      => ($action === 'add'),

			'categories' => $categories,
		]);
	}

	/**
	 * Manage / moderate ads.
	 */
	public function manage_ads()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');
		\add_form_key('mundophpbb_marketplace_acp_ads');

		$action = $this->request->variable('action', '');
		$ad_id  = $this->request->variable('ad_id', 0);
		$purchase_id = $this->request->variable('purchase_id', 0);

		if ($purchase_id && $action)
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_ads'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->handle_purchase_action($action, $purchase_id);
		}

		if ($ad_id && $action)
		{
			$is_confirmed = \confirm_box(true);
			if (!$is_confirmed && (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_ads')))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->handle_ad_action($action, $ad_id);
		}

		$start = $this->request->variable('start', 0);
		$limit = 25;

		$filter_status = $this->request->variable('status', -1);

		$sql_where = '';
		if ($filter_status >= 0)
		{
			$sql_where = 'WHERE a.ad_status = ' . (int) $filter_status;
		}

		$sql = 'SELECT a.*, u.username, u.user_colour, c.cat_name
				FROM ' . $this->table_ads . ' a
				LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = a.user_id)
				LEFT JOIN ' . $this->table_cats . ' c ON (c.cat_id = a.cat_id)
				' . $sql_where . '
				ORDER BY a.ad_created DESC';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$ads = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['STATUS_LANG'] = $this->get_status_lang($row['ad_status']);
			$row['AD_TYPE_LANG'] = $this->get_ad_type_lang(isset($row['ad_type']) ? (int) $row['ad_type'] : 1);
			$row['AD_CONDITION_LANG'] = $this->get_ad_condition_lang(isset($row['ad_condition']) ? (int) $row['ad_condition'] : 0);
			$row['AD_PRICE_DISPLAY'] = $this->format_acp_price($row);
			$row['ad_quantity'] = isset($row['ad_quantity']) ? max(0, (int) $row['ad_quantity']) : 1;
			$row['AD_QUANTITY_LANG'] = $this->format_quantity($row['ad_quantity']);
			$row['cat_name'] = $this->translate_category_text(isset($row['cat_name']) ? $row['cat_name'] : '');
			$row['AD_CREATED_DISPLAY'] = !empty($row['ad_created']) ? $this->user->format_date((int) $row['ad_created']) : '';
			$row['AD_EXPIRES_DISPLAY'] = !empty($row['ad_expires']) ? $this->user->format_date((int) $row['ad_expires']) : '';
			$row['AD_SOLD_AT_DISPLAY'] = !empty($row['ad_sold_at']) ? $this->user->format_date((int) $row['ad_sold_at']) : '';
			$row['AD_EXPIRED_AT_DISPLAY'] = !empty($row['ad_expired_at']) ? $this->user->format_date((int) $row['ad_expired_at']) : '';
			$row['AD_FEATURED_UNTIL_DISPLAY'] = !empty($row['ad_featured_until']) ? $this->user->format_date((int) $row['ad_featured_until']) : '';
			$row['AD_BOOSTED_UNTIL_DISPLAY'] = !empty($row['ad_boosted_until']) ? $this->user->format_date((int) $row['ad_boosted_until']) : '';
			$row['AD_LAST_BUMPED_DISPLAY'] = !empty($row['ad_last_bumped']) ? $this->user->format_date((int) $row['ad_last_bumped']) : '';
			$row['AD_BUMPED_AT_DISPLAY'] = $row['AD_LAST_BUMPED_DISPLAY'];
			$row['S_IS_FEATURED'] = (!isset($this->config['marketplace_allow_featured']) || !empty($this->config['marketplace_allow_featured'])) && !empty($row['ad_featured_until']) && (int) $row['ad_featured_until'] >= time();
			$row['S_IS_BOOSTED'] = (!isset($this->config['marketplace_allow_boosted']) || !empty($this->config['marketplace_allow_boosted'])) && !empty($row['ad_boosted_until']) && (int) $row['ad_boosted_until'] >= time();
			$row['OPEN_REPORTS'] = $this->count_open_reports((int) $row['ad_id']);
			$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$ads[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(*) as total FROM ' . $this->table_ads . ' a' . ($sql_where ? ' ' . $sql_where : '');
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$pagination_url = $this->u_action . '&amp;status=' . $filter_status;
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total, $limit, $start);

		$pending_promotions = $this->get_pending_promotions();
		$pending_purchases = $this->get_pending_purchases();

		$this->template->assign_vars([
			'U_ACTION'     => $this->u_action,
			'ads'          => $ads,
			'S_FILTER'     => $filter_status,
			'TOTAL_ADS'    => $total,
			'PENDING_PROMOTIONS' => $pending_promotions,
			'S_HAS_PENDING_PROMOTIONS' => !empty($pending_promotions),
			'PENDING_PURCHASES' => $pending_purchases,
			'S_HAS_PENDING_PURCHASES' => !empty($pending_purchases),
			'FEATURED_DAYS_DEFAULT' => isset($this->config['marketplace_featured_days']) ? (int) $this->config['marketplace_featured_days'] : 14,
			'BOOSTED_DAYS_DEFAULT' => isset($this->config['marketplace_boosted_days']) ? (int) $this->config['marketplace_boosted_days'] : 7,
		]);
	}


	private function get_pending_promotions()
	{
		$sql = 'SELECT p.*, a.ad_title, a.ad_status, u.username, u.user_colour
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			WHERE p.promotion_status IN (0, 3)
			ORDER BY p.promotion_requested ASC';
		$result = $this->db->sql_query_limit($sql, 25);

		$promotions = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PROMOTION_TYPE_LANG'] = $this->get_promotion_type_lang($row['promotion_type']);
			$row['PROMOTION_STATUS_LANG'] = $this->get_promotion_status_lang((int) $row['promotion_status']);
			$row['PROMOTION_REQUESTED_DISPLAY'] = !empty($row['promotion_requested']) ? $this->user->format_date((int) $row['promotion_requested']) : '';
			$row['PROMOTION_PRICE_DISPLAY'] = $this->format_package_price((int) $row['promotion_amount_cents'], $row['promotion_currency']);
			$row['PROMOTION_PACKAGE_TITLE'] = !empty($row['promotion_note']) ? $row['promotion_note'] : '';
			$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper($row['payment_provider']) : '-';
			$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? $row['payment_reference'] : '-';
			$row['U_VIEW'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$promotions[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $promotions;
	}


	private function get_pending_purchases()
	{
		$sql = 'SELECT p.*, a.ad_title, a.ad_status, buyer.username AS buyer_username, seller.username AS seller_username
			FROM ' . $this->table_purchases . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' buyer ON buyer.user_id = p.buyer_user_id
			LEFT JOIN ' . USERS_TABLE . ' seller ON seller.user_id = p.seller_user_id
			WHERE p.purchase_status IN (0, 3)
			ORDER BY p.purchase_created ASC';
		$result = $this->db->sql_query_limit($sql, 25);

		$purchases = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PURCHASE_STATUS_LANG'] = $this->get_purchase_status_lang((int) $row['purchase_status']);
			$row['PURCHASE_CREATED_DISPLAY'] = !empty($row['purchase_created']) ? $this->user->format_date((int) $row['purchase_created']) : '';
			$row['PURCHASE_PRICE_DISPLAY'] = $this->format_package_price((int) $row['purchase_amount_cents'], $row['purchase_currency']);
			$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper($row['payment_provider']) : '-';
			$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? $row['payment_reference'] : '-';
			$row['U_VIEW'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$purchases[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $purchases;
	}

	private function handle_purchase_action($action, $purchase_id)
	{
		$purchase = $this->get_purchase($purchase_id);
		if (!$purchase || !in_array((int) $purchase['purchase_status'], [0, 3], true))
		{
			\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_FOUND') . \adm_back_link($this->u_action), E_USER_WARNING);
		}

		$ad = $this->get_ad((int) $purchase['ad_id']);
		if (!$ad)
		{
			\trigger_error($this->language->lang('MARKETPLACE_AD_NOT_FOUND') . \adm_back_link($this->u_action), E_USER_WARNING);
		}

		switch ($action)
		{
			case 'approve_purchase':
				$now = time();
				$quantity = isset($ad['ad_quantity']) ? max(0, (int) $ad['ad_quantity']) : 1;
				$quantity = max(0, $quantity - 1);
				$sql_ary = [
					'ad_quantity' => $quantity,
					'ad_updated' => $now,
				];
				if ($quantity <= 0)
				{
					$sql_ary['ad_status'] = 2;
					$sql_ary['ad_sold_at'] = $now;
				}
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . (int) $ad['ad_id']);
				$this->update_purchase_status($purchase_id, 1);
				$this->add_notification((int) $purchase['buyer_user_id'], (int) $purchase['ad_id'], 'purchase_approved', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_APPROVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_APPROVED_MESSAGE', $ad['ad_title']));
				$this->add_notification((int) $purchase['seller_user_id'], (int) $purchase['ad_id'], 'purchase_approved_seller', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_SELLER_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_SELLER_MESSAGE', $ad['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_APPROVED') . \adm_back_link($this->u_action));
			break;

			case 'reject_purchase':
				$this->update_purchase_status($purchase_id, 2);
				$this->add_notification((int) $purchase['buyer_user_id'], (int) $purchase['ad_id'], 'purchase_rejected', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_REJECTED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_REJECTED_MESSAGE', $ad['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_REJECTED') . \adm_back_link($this->u_action));
			break;

			default:
				\trigger_error($this->language->lang('MARKETPLACE_ACTION_NOT_ALLOWED') . \adm_back_link($this->u_action), E_USER_WARNING);
			break;
		}
	}

	private function get_purchase($purchase_id)
	{
		$sql = 'SELECT * FROM ' . $this->table_purchases . ' WHERE purchase_id = ' . (int) $purchase_id;
		$result = $this->db->sql_query($sql);
		$purchase = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $purchase;
	}

	private function update_purchase_status($purchase_id, $status)
	{
		$sql_ary = [
			'purchase_status' => (int) $status,
			'purchase_decided' => time(),
			'purchase_decided_by' => (int) $this->user->data['user_id'],
		];
		$this->db->sql_query('UPDATE ' . $this->table_purchases . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE purchase_id = ' . (int) $purchase_id);
	}

	private function get_purchase_status_lang($status)
	{
		switch ((int) $status)
		{
			case 0:
				return $this->language->lang('MARKETPLACE_PURCHASE_STATUS_PENDING');
			case 1:
				return $this->language->lang('MARKETPLACE_PURCHASE_STATUS_APPROVED');
			case 2:
				return $this->language->lang('MARKETPLACE_PURCHASE_STATUS_REJECTED');
			case 3:
				return $this->language->lang('MARKETPLACE_PURCHASE_STATUS_AWAITING_PAYMENT');
		}
		return $this->language->lang('MARKETPLACE_PURCHASE_STATUS_PENDING');
	}

	private function handle_promotion_action($action, $promotion_id)
	{
		$promotion = $this->get_promotion($promotion_id);
		if (!$promotion || !in_array((int) $promotion['promotion_status'], [0, 3], true))
		{
			\trigger_error($this->language->lang('MARKETPLACE_PROMOTION_NOT_FOUND') . \adm_back_link($this->u_action), E_USER_WARNING);
		}

		$ad = $this->get_ad((int) $promotion['ad_id']);
		if (!$ad)
		{
			\trigger_error($this->language->lang('MARKETPLACE_AD_NOT_FOUND') . \adm_back_link($this->u_action), E_USER_WARNING);
		}

		$now = time();
		$days = max(1, (int) $promotion['promotion_days']);

		switch ($action)
		{
			case 'approve_promotion':
				if ($promotion['promotion_type'] === 'featured')
				{
					$sql_ary = [
						'ad_featured_until' => $now + ($days * 86400),
						'ad_featured_by' => (int) $this->user->data['user_id'],
						'ad_updated' => $now,
					];
					$sql_ary = $this->filter_existing_ad_columns($sql_ary);
					$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . (int) $ad['ad_id']);
				}
				else if ($promotion['promotion_type'] === 'boosted')
				{
					$sql_ary = [
						'ad_boosted_until' => $now + ($days * 86400),
						'ad_boosted_by' => (int) $this->user->data['user_id'],
						'ad_updated' => $now,
					];
					$sql_ary = $this->filter_existing_ad_columns($sql_ary);
					$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . (int) $ad['ad_id']);
				}
				else
				{
					\trigger_error($this->language->lang('MARKETPLACE_ACTION_NOT_ALLOWED') . \adm_back_link($this->u_action), E_USER_WARNING);
				}

				$this->update_promotion_status($promotion_id, 1);
				$this->add_notification((int) $promotion['user_id'], (int) $promotion['ad_id'], 'promotion_approved', $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_APPROVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_APPROVED_MESSAGE', $ad['ad_title'], $this->get_promotion_type_lang($promotion['promotion_type'])));
				\trigger_error($this->language->lang('MARKETPLACE_PROMOTION_APPROVED') . \adm_back_link($this->u_action));
			break;

			case 'reject_promotion':
				$this->update_promotion_status($promotion_id, 2);
				$this->add_notification((int) $promotion['user_id'], (int) $promotion['ad_id'], 'promotion_rejected', $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_REJECTED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_REJECTED_MESSAGE', $ad['ad_title'], $this->get_promotion_type_lang($promotion['promotion_type'])));
				\trigger_error($this->language->lang('MARKETPLACE_PROMOTION_REJECTED') . \adm_back_link($this->u_action));
			break;

			default:
				\trigger_error($this->language->lang('MARKETPLACE_ACTION_NOT_ALLOWED') . \adm_back_link($this->u_action), E_USER_WARNING);
			break;
		}
	}

	private function get_promotion($promotion_id)
	{
		$sql = 'SELECT * FROM ' . $this->table_promotions . ' WHERE promotion_id = ' . (int) $promotion_id;
		$result = $this->db->sql_query($sql);
		$promotion = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $promotion;
	}

	private function update_promotion_status($promotion_id, $status)
	{
		$sql_ary = [
			'promotion_status' => (int) $status,
			'promotion_decided' => time(),
			'promotion_decided_by' => (int) $this->user->data['user_id'],
		];
		$this->db->sql_query('UPDATE ' . $this->table_promotions . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE promotion_id = ' . (int) $promotion_id);
	}

	private function get_promotion_status_lang($status)
	{
		switch ((int) $status)
		{
			case 0:
				return $this->language->lang('MARKETPLACE_PROMOTION_STATUS_PENDING');
			case 1:
				return $this->language->lang('MARKETPLACE_PROMOTION_STATUS_APPROVED');
			case 2:
				return $this->language->lang('MARKETPLACE_PROMOTION_STATUS_REJECTED');
			case 3:
				return $this->language->lang('MARKETPLACE_PROMOTION_STATUS_AWAITING_PAYMENT');
		}
		return $this->language->lang('MARKETPLACE_PROMOTION_STATUS_PENDING');
	}

	private function get_promotion_type_lang($type)
	{
		switch ((string) $type)
		{
			case 'featured':
				return $this->language->lang('MARKETPLACE_FEATURED');
			case 'boosted':
				return $this->language->lang('MARKETPLACE_BOOSTED');
			case 'renewal':
				return $this->language->lang('MARKETPLACE_RENEW_AD');
		}

		return (string) $type;
	}

	private function handle_ad_action($action, $ad_id)
	{
		$ad_id = (int) $ad_id;
		$now = time();
		$ad = $this->get_ad($ad_id);
		if (!$ad)
		{
			\trigger_error($this->language->lang('MARKETPLACE_AD_NOT_FOUND') . \adm_back_link($this->u_action), E_USER_WARNING);
		}

		switch ($action)
		{
			case 'approve':
				$sql_ary = [
					'ad_status'       => 1,
					'ad_expires'      => $this->calculate_expiration_time($now, (int) $ad['cat_id']),
					'ad_updated'      => $now,
					'ad_expired_at'   => 0,
					'ad_approved_at'  => $now,
					'ad_approved_by'  => (int) $this->user->data['user_id'],
					'ad_hidden_at'    => 0,
					'ad_hidden_by'    => 0,
					'ad_hidden_reason'=> '',
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_AD_APPROVED', false, ['ad_id' => $ad_id]);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'approved', $this->language->lang('MARKETPLACE_NOTIFICATION_APPROVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_APPROVED_MESSAGE', $ad['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_AD_APPROVED') . \adm_back_link($this->u_action));
			break;

			case 'reject':
			case 'hide':
				$reason = trim($this->request->variable('hidden_reason_' . $ad_id, '', true));
				if ($reason === '')
				{
					$reason = trim($this->request->variable('hidden_reason', '', true));
				}
				$sql_ary = [
					'ad_status'        => 4,
					'ad_updated'       => $now,
					'ad_hidden_at'     => $now,
					'ad_hidden_by'     => (int) $this->user->data['user_id'],
					'ad_hidden_reason' => $reason,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_AD_REJECTED', false, ['ad_id' => $ad_id]);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'hidden', $this->language->lang('MARKETPLACE_NOTIFICATION_HIDDEN_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_HIDDEN_MESSAGE', $ad['ad_title'], $reason !== '' ? $reason : $this->language->lang('MARKETPLACE_NO_REASON_GIVEN')));
				\trigger_error($this->language->lang('MARKETPLACE_AD_REJECTED') . \adm_back_link($this->u_action));
			break;

			case 'delete':
				if (\confirm_box(true))
				{
					$this->delete_ad_images($ad_id);
					$this->db->sql_query('DELETE FROM ' . $this->table_reports . ' WHERE ad_id = ' . $ad_id);
					$this->db->sql_query('DELETE FROM ' . $this->table_notifications . ' WHERE ad_id = ' . $ad_id);
					$this->db->sql_query('DELETE FROM ' . $this->table_ads . ' WHERE ad_id = ' . $ad_id);
					$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_AD_DELETED', false, ['ad_id' => $ad_id]);
					\trigger_error($this->language->lang('MARKETPLACE_AD_DELETED') . \adm_back_link($this->u_action));
				}
				else
				{
					\confirm_box(false, $this->language->lang('CONFIRM_DELETE_AD'), \build_hidden_fields([
						'i'      => $this->request->variable('i', ''),
						'mode'   => 'ads',
						'action' => 'delete',
						'ad_id'  => $ad_id,
					]));
				}
			break;

			case 'mark_sold':
				$sql_ary = [
					'ad_status'  => 2,
					'ad_sold_at' => $now,
					'ad_quantity'=> 0,
					'ad_updated' => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'sold', $this->language->lang('MARKETPLACE_NOTIFICATION_SOLD_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_SOLD_MESSAGE', $ad['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_AD_MARKED_SOLD') . \adm_back_link($this->u_action));
			break;

			case 'stock_increase':
			case 'stock_decrease':
			case 'stock_out':
				$quantity = isset($ad['ad_quantity']) ? max(0, (int) $ad['ad_quantity']) : 1;
				if ($action === 'stock_increase')
				{
					$quantity++;
				}
				else if ($action === 'stock_decrease')
				{
					$quantity = max(0, $quantity - 1);
				}
				else
				{
					$quantity = 0;
				}
				$sql_ary = ['ad_quantity' => $quantity, 'ad_updated' => $now];
				if ($quantity <= 0)
				{
					$sql_ary['ad_status'] = 2;
					$sql_ary['ad_sold_at'] = $now;
				}
				else if ((int) $ad['ad_status'] === 2)
				{
					$sql_ary['ad_status'] = 1;
					$sql_ary['ad_sold_at'] = 0;
				}
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
			break;

			case 'bump':
				$sql_ary = [
					'ad_last_bumped' => $now,
					'ad_updated'     => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				\trigger_error($this->language->lang('MARKETPLACE_AD_BUMPED') . \adm_back_link($this->u_action));
			break;

			case 'feature':
				if (isset($this->config['marketplace_allow_featured']) && empty($this->config['marketplace_allow_featured']))
				{
					\trigger_error($this->language->lang('MARKETPLACE_FEATURED_DISABLED'));
				}

				$days = max(1, $this->request->variable('featured_days', (int) $this->config['marketplace_featured_days']));
				$sql_ary = [
					'ad_featured_until' => $now + ($days * 86400),
					'ad_featured_by'    => (int) $this->user->data['user_id'],
					'ad_updated'        => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'featured', $this->language->lang('MARKETPLACE_NOTIFICATION_FEATURED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_FEATURED_MESSAGE', $ad['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_AD_FEATURED') . \adm_back_link($this->u_action));
			break;

			case 'boost':
				if (isset($this->config['marketplace_allow_boosted']) && empty($this->config['marketplace_allow_boosted']))
				{
					\trigger_error($this->language->lang('MARKETPLACE_BOOSTED_DISABLED'));
				}

				$days = max(1, $this->request->variable('boosted_days', (int) $this->config['marketplace_boosted_days']));
				$sql_ary = [
					'ad_boosted_until' => $now + ($days * 86400),
					'ad_boosted_by'    => (int) $this->user->data['user_id'],
					'ad_updated'       => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'boosted', $this->language->lang('MARKETPLACE_NOTIFICATION_BOOSTED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_BOOSTED_MESSAGE', $ad['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_AD_BOOSTED') . \adm_back_link($this->u_action));
			break;

			case 'unboost':
				$sql_ary = [
					'ad_boosted_until' => 0,
					'ad_boosted_by'    => 0,
					'ad_updated'       => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'unboosted', $this->language->lang('MARKETPLACE_NOTIFICATION_UNBOOSTED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_UNBOOSTED_MESSAGE', $ad['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_AD_UNBOOSTED') . \adm_back_link($this->u_action));
			break;

			case 'unfeature':
				$sql_ary = [
					'ad_featured_until' => 0,
					'ad_featured_by'    => 0,
					'ad_updated'        => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'unfeatured', $this->language->lang('MARKETPLACE_NOTIFICATION_UNFEATURED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_UNFEATURED_MESSAGE', $ad['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_AD_UNFEATURED') . \adm_back_link($this->u_action));
			break;
		}
	}


	public function display_dashboard()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');

		$stats = [
			'TOTAL_ADS'      => $this->count_ads(),
			'ACTIVE_ADS'     => $this->count_ads('ad_status = 1'),
			'PENDING_ADS'    => $this->count_ads('ad_status = 0'),
			'SOLD_ADS'       => $this->count_ads('ad_status = 2'),
			'EXPIRED_ADS'    => $this->count_ads('ad_status = 3'),
			'HIDDEN_ADS'     => $this->count_ads('ad_status = 4'),
			'FEATURED_ADS'   => $this->column_exists($this->table_ads, 'ad_featured_until') ? $this->count_ads('ad_featured_until >= ' . (int) time()) : 0,
			'BOOSTED_ADS'    => $this->column_exists($this->table_ads, 'ad_boosted_until') ? $this->count_ads('ad_boosted_until >= ' . (int) time()) : 0,
			'OPEN_REPORTS'   => $this->count_reports('report_status = 0'),
			'TOTAL_REPORTS'  => $this->count_reports(),
			'TOTAL_IMAGES'   => $this->count_images(),
			'DISK_USAGE'     => $this->format_bytes($this->get_marketplace_disk_usage()),
		];

		$recent_reports = [];
		$sql = 'SELECT r.*, a.ad_title, u.username, u.user_colour
			FROM ' . $this->table_reports . ' r
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = r.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = r.reporter_id
			ORDER BY r.report_created DESC';
		$result = $this->db->sql_query_limit($sql, 10);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['REPORT_CREATED_DISPLAY'] = !empty($row['report_created']) ? $this->user->format_date((int) $row['report_created']) : '';
			$row['REPORT_STATUS_LANG'] = ((int) $row['report_status'] === 0) ? $this->language->lang('MARKETPLACE_REPORT_OPEN') : $this->language->lang('MARKETPLACE_REPORT_CLOSED');
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$recent_reports[] = $row;
		}
		$this->db->sql_freeresult($result);

		$recent_pending_ads = [];
		$sql = 'SELECT a.*, u.username, u.user_colour, c.cat_name
			FROM ' . $this->table_ads . ' a
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
			LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
			WHERE a.ad_status = 0
			ORDER BY a.ad_created DESC';
		$result = $this->db->sql_query_limit($sql, 8);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['AD_CREATED_DISPLAY'] = !empty($row['ad_created']) ? $this->user->format_date((int) $row['ad_created']) : '';
			$row['AD_PRICE_DISPLAY'] = $this->format_acp_price($row);
			$row['cat_name'] = $this->translate_category_text(isset($row['cat_name']) ? $row['cat_name'] : '');
			$row['U_AD'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$recent_pending_ads[] = $row;
		}
		$this->db->sql_freeresult($result);

		$base_dashboard_url = $this->u_action;
		$u_ads = str_replace('mode=dashboard', 'mode=ads', $base_dashboard_url);
		$u_reports = str_replace('mode=dashboard', 'mode=reports', $base_dashboard_url);
		$u_categories = str_replace('mode=dashboard', 'mode=categories', $base_dashboard_url);
		$u_settings = str_replace('mode=dashboard', 'mode=settings', $base_dashboard_url);

		$this->template->assign_vars(array_merge($stats, [
			'U_ACTION'           => $this->u_action,
			'U_ACP_ADS'          => $u_ads,
			'U_ACP_PENDING_ADS'  => $u_ads . '&amp;status=0',
			'U_ACP_ACTIVE_ADS'   => $u_ads . '&amp;status=1',
			'U_ACP_HIDDEN_ADS'   => $u_ads . '&amp;status=4',
			'U_ACP_REPORTS'      => $u_reports,
			'U_ACP_OPEN_REPORTS' => $u_reports . '&amp;status=0',
			'U_ACP_CATEGORIES'   => $u_categories,
			'U_ACP_SETTINGS'     => $u_settings,
			'RECENT_REPORTS'     => $recent_reports,
			'RECENT_PENDING_ADS' => $recent_pending_ads,
		]));
	}

	public function manage_reports()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');
		\add_form_key('mundophpbb_marketplace_acp_reports');

		$action = $this->request->variable('action', '');
		$report_id = $this->request->variable('report_id', 0);
		if ($action !== '' && $report_id)
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_reports'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}
			$this->handle_report_action($action, $report_id);
		}

		$start = $this->request->variable('start', 0);
		$limit = 25;
		$filter_status = $this->request->variable('status', -1);
		$where = ($filter_status >= 0) ? 'WHERE r.report_status = ' . (int) $filter_status : '';

		$sql = 'SELECT r.*, a.ad_title, a.user_id AS ad_user_id, u.username, u.user_colour
			FROM ' . $this->table_reports . ' r
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = r.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = r.reporter_id
			' . $where . '
			ORDER BY r.report_created DESC';
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$reports = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['REPORT_CREATED_DISPLAY'] = !empty($row['report_created']) ? $this->user->format_date((int) $row['report_created']) : '';
			$row['REPORT_TIME_DISPLAY'] = $row['REPORT_CREATED_DISPLAY'];
			$row['REPORT_CLOSED_DISPLAY'] = !empty($row['report_closed']) ? $this->user->format_date((int) $row['report_closed']) : '';
			$row['REPORT_CLOSED_AT_DISPLAY'] = $row['REPORT_CLOSED_DISPLAY'];
			$row['REPORT_REASON_LANG'] = $row['report_reason'];
			$row['report_text'] = isset($row['report_note']) ? $row['report_note'] : '';
			$row['reporter_username'] = isset($row['username']) ? $row['username'] : '';
			$row['REPORT_STATUS_LANG'] = ((int) $row['report_status'] === 0) ? $this->language->lang('MARKETPLACE_REPORT_OPEN') : $this->language->lang('MARKETPLACE_REPORT_CLOSED');
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$reports[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_reports . ' r ' . $where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$pagination_url = $this->u_action . '&amp;status=' . $filter_status;
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total, $limit, $start);

		$this->template->assign_vars([
			'U_ACTION'   => $this->u_action,
			'REPORTS'    => $reports,
			'reports'    => $reports,
			'S_FILTER'   => $filter_status,
			'TOTAL_REPORTS' => $total,
		]);
	}

	private function handle_report_action($action, $report_id)
	{
		$report_id = (int) $report_id;
		$report = $this->get_report($report_id);
		if (!$report)
		{
			\trigger_error($this->language->lang('MARKETPLACE_REPORT_NOT_FOUND') . \adm_back_link($this->u_action), E_USER_WARNING);
		}

		$now = time();
		switch ($action)
		{
			case 'resolve':
				$note = $this->request->variable('report_note_' . $report_id, '', true);
				$sql_ary = [
					'report_status'    => 1,
					'report_closed'    => $now,
					'report_closed_by' => (int) $this->user->data['user_id'],
					'report_note'      => $note,
				];
				$this->db->sql_query('UPDATE ' . $this->table_reports . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE report_id = ' . $report_id);
				$this->add_notification((int) $report['reporter_id'], (int) $report['ad_id'], 'report_closed', $this->language->lang('MARKETPLACE_NOTIFICATION_REPORT_CLOSED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_REPORT_CLOSED_MESSAGE', $report['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_REPORT_RESOLVED') . \adm_back_link($this->u_action));
			break;

			case 'reopen':
				$sql_ary = [
					'report_status'    => 0,
					'report_closed'    => 0,
					'report_closed_by' => 0,
				];
				$this->db->sql_query('UPDATE ' . $this->table_reports . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE report_id = ' . $report_id);
				\trigger_error($this->language->lang('MARKETPLACE_REPORT_REOPENED') . \adm_back_link($this->u_action));
			break;

			case 'delete':
				$this->db->sql_query('DELETE FROM ' . $this->table_reports . ' WHERE report_id = ' . $report_id);
				\trigger_error($this->language->lang('MARKETPLACE_REPORT_DELETED') . \adm_back_link($this->u_action));
			break;
		}
	}

	private function count_ads($where = '')
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_ads . ($where !== '' ? ' WHERE ' . $where : '');
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		return $total;
	}

	private function count_reports($where = '')
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_reports . ($where !== '' ? ' WHERE ' . $where : '');
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		return $total;
	}

	private function count_images()
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_images;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		return $total;
	}

	private function count_open_reports($ad_id)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_reports . ' WHERE ad_id = ' . (int) $ad_id . ' AND report_status = 0';
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		return $total;
	}

	private function get_marketplace_disk_usage()
	{
		$total = 0;
		if (!is_dir($this->upload_path))
		{
			return 0;
		}
		$files = scandir($this->upload_path);
		if (!$files)
		{
			return 0;
		}
		foreach ($files as $file)
		{
			$path = $this->upload_path . $file;
			if (is_file($path))
			{
				$total += (int) filesize($path);
			}
		}
		return $total;
	}

	private function format_bytes($bytes)
	{
		$bytes = (int) $bytes;
		if ($bytes >= 1073741824)
		{
			return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
		}
		if ($bytes >= 1048576)
		{
			return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
		}
		if ($bytes >= 1024)
		{
			return number_format($bytes / 1024, 2, ',', '.') . ' KB';
		}
		return $bytes . ' B';
	}

	private function get_ad($ad_id)
	{
		$sql = 'SELECT * FROM ' . $this->table_ads . ' WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		$ad = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $ad;
	}

	private function get_category($cat_id)
	{
		$sql = 'SELECT * FROM ' . $this->table_cats . ' WHERE cat_id = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql);
		$category = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $category;
	}

	private function get_report($report_id)
	{
		$sql = 'SELECT r.*, a.ad_title
			FROM ' . $this->table_reports . ' r
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = r.ad_id
			WHERE r.report_id = ' . (int) $report_id;
		$result = $this->db->sql_query($sql);
		$report = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $report;
	}

	private function add_notification($user_id, $ad_id, $type, $title, $message)
	{
		$user_id = (int) $user_id;
		if ($user_id <= 0 || $user_id === ANONYMOUS)
		{
			return;
		}

		$sql_ary = [
			'user_id'              => $user_id,
			'ad_id'                => (int) $ad_id,
			'notification_type'    => substr((string) $type, 0, 50),
			'notification_title'   => substr((string) $title, 0, 255),
			'notification_message' => (string) $message,
			'notification_read'    => 0,
			'notification_time'    => time(),
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_notifications . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}

	private function calculate_expiration_time($base_time = null, $cat_id = 0)
	{
		$days = 0;
		if ((int) $cat_id > 0)
		{
			$category = $this->get_category((int) $cat_id);
			if ($category && isset($category['cat_expiration_days']) && (int) $category['cat_expiration_days'] > 0)
			{
				$days = (int) $category['cat_expiration_days'];
			}
		}

		if ($days <= 0)
		{
			$days = (int) $this->config['marketplace_ad_expiration_days'];
		}

		if ($days <= 0)
		{
			return 0;
		}

		$base_time = $base_time ?: time();
		return (int) $base_time + ($days * 86400);
	}

	private function delete_ad_images($ad_id)
	{
		$sql = 'SELECT image_filename FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$filename = (string) $row['image_filename'];
			if ($this->is_safe_image_filename($filename))
			{
				$path = $this->upload_path . $filename;
				if (file_exists($path))
				{
					@unlink($path);
				}
			}
		}
		$this->db->sql_freeresult($result);
		$this->db->sql_query('DELETE FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id);
	}

	private function is_safe_image_filename($filename)
	{
		return $filename !== '' && basename($filename) === $filename && (bool) preg_match('/^[A-Za-z0-9_.-]+\.(jpe?g|png|gif|webp)$/i', $filename);
	}

	private function format_quantity($quantity)
	{
		$quantity = max(0, (int) $quantity);
		if ($quantity === 0)
		{
			return $this->language->lang('MARKETPLACE_STOCK_OUT');
		}
		return $this->language->lang($quantity === 1 ? 'MARKETPLACE_STOCK_ONE' : 'MARKETPLACE_STOCK_MANY', $quantity);
	}

	private function get_status_lang($status)
	{
		$map = [
			0 => 'MARKETPLACE_STATUS_PENDING',
			1 => 'MARKETPLACE_STATUS_ACTIVE',
			2 => 'MARKETPLACE_STATUS_SOLD',
			3 => 'MARKETPLACE_STATUS_EXPIRED',
			4 => 'MARKETPLACE_STATUS_HIDDEN',
		];
		return $this->language->lang($map[$status] ?? 'MARKETPLACE_STATUS_UNKNOWN');
	}



	private function sanitize_allowed_types($types)
	{
		if (!is_array($types))
		{
			$types = explode(',', (string) $types);
		}

		$allowed = [];
		foreach ($types as $type)
		{
			$type = (int) $type;
			if ($type >= 1 && $type <= 6)
			{
				$allowed[$type] = $type;
			}
		}

		if (empty($allowed))
		{
			$allowed = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6];
		}

		sort($allowed);
		return implode(',', $allowed);
	}


	private function translate_category_text($text)
	{
		$text = (string) $text;
		if ($this->is_category_language_key($text))
		{
			return $this->language->lang($text);
		}

		return $text;
	}

	private function is_category_language_key($text)
	{
		return strpos((string) $text, 'MARKETPLACE_CAT_') === 0;
	}

	private function get_category_type_options($selected)
	{
		$selected_ids = array_flip(array_map('intval', explode(',', (string) $selected)));
		$options = [];
		foreach ([1, 2, 3, 4, 5, 6] as $type)
		{
			$options[] = [
				'VALUE'   => $type,
				'LABEL'   => $this->get_ad_type_lang($type),
				'CHECKED' => isset($selected_ids[$type]),
			];
		}
		return $options;
	}

	private function format_allowed_types($selected)
	{
		$labels = [];
		foreach (array_map('intval', explode(',', (string) $selected)) as $type)
		{
			if ($type >= 1 && $type <= 6)
			{
				$labels[] = $this->get_ad_type_lang($type);
			}
		}
		return empty($labels) ? $this->language->lang('MARKETPLACE_ALL_TYPES') : implode(', ', $labels);
	}


	private function column_exists($table, $column)
	{
		$key = $table . '.' . $column;
		if (isset($this->column_exists_cache[$key]))
		{
			return $this->column_exists_cache[$key];
		}

		$sql = 'SHOW COLUMNS FROM ' . $table . " LIKE '" . $this->db->sql_escape($column) . "'";
		$result = $this->db->sql_query($sql);
		$exists = (bool) $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->column_exists_cache[$key] = $exists;
		return $exists;
	}

	private function filter_existing_ad_columns(array $sql_ary)
	{
		foreach (array_keys($sql_ary) as $column)
		{
			if (!$this->column_exists($this->table_ads, $column))
			{
				unset($sql_ary[$column]);
			}
		}

		return $sql_ary;
	}

	private function get_ad_type_lang($type)
	{
		$map = [
			1 => 'MARKETPLACE_TYPE_SELL',
			2 => 'MARKETPLACE_TYPE_BUY',
			3 => 'MARKETPLACE_TYPE_TRADE',
			4 => 'MARKETPLACE_TYPE_SERVICE',
			5 => 'MARKETPLACE_TYPE_RENT',
			6 => 'MARKETPLACE_TYPE_WANTED',
		];
		return $this->language->lang($map[(int) $type] ?? 'MARKETPLACE_TYPE_SELL');
	}

	private function get_ad_condition_lang($condition)
	{
		$map = [
			0 => 'MARKETPLACE_CONDITION_NA',
			1 => 'MARKETPLACE_CONDITION_NEW',
			2 => 'MARKETPLACE_CONDITION_USED',
			3 => 'MARKETPLACE_CONDITION_REFURBISHED',
		];
		return $this->language->lang($map[(int) $condition] ?? 'MARKETPLACE_CONDITION_NA');
	}


	private function format_acp_price($ad)
	{
		if (empty($this->config['marketplace_enable_price']))
		{
			return '';
		}

		$currency = !empty($ad['ad_currency']) ? $ad['ad_currency'] : (isset($this->config['marketplace_currency_default']) ? $this->config['marketplace_currency_default'] : '');
		$price_type = isset($ad['ad_price_type']) ? (int) $ad['ad_price_type'] : 2;
		$amount = isset($ad['ad_price_cents']) ? (int) $ad['ad_price_cents'] : $this->parse_acp_price_amount(isset($ad['ad_price']) ? $ad['ad_price'] : '');

		switch ($price_type)
		{
			case 1:
				return $amount > 0 ? trim($currency . ' ' . $this->format_acp_price_amount($amount)) : $this->language->lang('MARKETPLACE_PRICE_NEGOTIABLE');
			case 2:
				return $amount > 0 ? trim($currency . ' ' . $this->format_acp_price_amount($amount)) . ' (' . $this->language->lang('MARKETPLACE_PRICE_TYPE_NEGOTIABLE') . ')' : $this->language->lang('MARKETPLACE_PRICE_NEGOTIABLE');
			case 3:
				return $this->language->lang('MARKETPLACE_PRICE_TYPE_FREE');
			case 4:
				return $this->language->lang('MARKETPLACE_PRICE_TYPE_ON_REQUEST');
		}

		return $this->language->lang('MARKETPLACE_PRICE_NEGOTIABLE');
	}

	private function parse_acp_price_amount($price)
	{
		$price = trim((string) $price);
		if ($price === '' || $price === '0')
		{
			return 0;
		}

		$price = preg_replace('/[^0-9,.]/', '', $price);
		if ($price === '' || $price === ',' || $price === '.')
		{
			return 0;
		}

		$last_comma = strrpos($price, ',');
		$last_dot = strrpos($price, '.');
		if ($last_comma !== false && $last_dot !== false)
		{
			$decimal_separator = $last_comma > $last_dot ? ',' : '.';
		}
		else
		{
			$decimal_separator = $last_comma !== false ? ',' : '.';
		}

		$normalized = preg_replace('/[,.]/', '', $price);
		if ($decimal_separator && preg_match('/[,.][0-9]{1,2}$/', $price))
		{
			$parts = preg_split('/[,.](?=[0-9]{1,2}$)/', $price);
			$integer = preg_replace('/[^0-9]/', '', $parts[0]);
			$decimal = str_pad(preg_replace('/[^0-9]/', '', $parts[1]), 2, '0');
			return ((int) $integer * 100) + (int) substr($decimal, 0, 2);
		}

		return (int) $normalized * 100;
	}

	private function format_acp_price_amount($amount)
	{
		$amount = max(0, (int) $amount);
		return number_format($amount / 100, 2, ',', '.');
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
