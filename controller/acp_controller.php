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

	/** @var string */
	protected $table_payment_logs;

	/** @var string */
	protected $table_coupons;

	/** @var string */
	protected $table_promo_periods;

	/** @var string */
	protected $table_group_freebies;

	/** @var string */
	protected $table_forbidden_terms;

	/** @var string */
	protected $table_user_limits;

	/** @var string */
	protected $table_group_limits;

	/** @var string */
	protected $table_user_security;

	/** @var string */
	protected $table_ad_edit_history;

	/** @var string */
	protected $table_moderation_logs;

	/** @var string */
	protected $table_category_fields;

	/** @var string */
	protected $table_ad_field_values;


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
		$table_purchases,
		$table_payment_logs
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
		$this->table_payment_logs = $table_payment_logs;
		$this->table_coupons = preg_replace('/marketplace_ads$/', 'marketplace_coupons', $table_ads);
		$this->table_promo_periods = preg_replace('/marketplace_ads$/', 'marketplace_promo_periods', $table_ads);
		$this->table_group_freebies = preg_replace('/marketplace_ads$/', 'marketplace_group_freebies', $table_ads);
		$this->table_forbidden_terms = preg_replace('/marketplace_ads$/', 'marketplace_forbidden_terms', $table_ads);
		$this->table_user_limits = preg_replace('/marketplace_ads$/', 'marketplace_user_limits', $table_ads);
		$this->table_group_limits = preg_replace('/marketplace_ads$/', 'marketplace_group_limits', $table_ads);
		$this->table_user_security = preg_replace('/marketplace_ads$/', 'marketplace_user_security', $table_ads);
		$this->table_ad_edit_history = preg_replace('/marketplace_ads$/', 'marketplace_ad_edit_history', $table_ads);
		$this->table_moderation_logs = preg_replace('/marketplace_ads$/', 'marketplace_moderation_logs', $table_ads);
		$this->table_category_fields = preg_replace('/marketplace_ads$/', 'marketplace_category_fields', $table_ads);
		$this->table_ad_field_values = preg_replace('/marketplace_ads$/', 'marketplace_ad_field_values', $table_ads);
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
				$this->config->set('marketplace_gateway_paypal_enabled', $this->request->variable('marketplace_gateway_paypal_enabled', 1));
				$this->config->set('marketplace_gateway_stripe_enabled', $this->request->variable('marketplace_gateway_stripe_enabled', 0));
				$this->config->set('marketplace_gateway_stripe_public_key', $this->request->variable('marketplace_gateway_stripe_public_key', '', true));
				$this->config->set('marketplace_gateway_stripe_secret_key', $this->request->variable('marketplace_gateway_stripe_secret_key', '', true));
				$this->config->set('marketplace_gateway_pix_enabled', $this->request->variable('marketplace_gateway_pix_enabled', 0));
				$this->config->set('marketplace_gateway_pix_key_type', $this->request->variable('marketplace_gateway_pix_key_type', 'cpf'));
				$this->config->set('marketplace_gateway_pix_key', $this->request->variable('marketplace_gateway_pix_key', '', true));
				$this->config->set('marketplace_gateway_pix_receiver_name', $this->request->variable('marketplace_gateway_pix_receiver_name', '', true));
				$this->config->set('marketplace_gateway_pix_receiver_city', $this->request->variable('marketplace_gateway_pix_receiver_city', '', true));
				$this->config->set('marketplace_gateway_pix_instructions', $this->request->variable('marketplace_gateway_pix_instructions', '', true));
				$this->config->set('marketplace_gateway_pix_deadline_minutes', max(5, $this->request->variable('marketplace_gateway_pix_deadline_minutes', 1440)));
				$this->config->set('marketplace_gateway_mercadopago_enabled', $this->request->variable('marketplace_gateway_mercadopago_enabled', 0));
				$this->config->set('marketplace_gateway_mercadopago_public_key', $this->request->variable('marketplace_gateway_mercadopago_public_key', '', true));
				$this->config->set('marketplace_gateway_mercadopago_access_token', $this->request->variable('marketplace_gateway_mercadopago_access_token', '', true));

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
			'MARKETPLACE_GATEWAY_PAYPAL_ENABLED' => !isset($this->config['marketplace_gateway_paypal_enabled']) || !empty($this->config['marketplace_gateway_paypal_enabled']),
			'MARKETPLACE_GATEWAY_STRIPE_ENABLED' => !empty($this->config['marketplace_gateway_stripe_enabled']),
			'MARKETPLACE_GATEWAY_STRIPE_PUBLIC_KEY' => isset($this->config['marketplace_gateway_stripe_public_key']) ? (string) $this->config['marketplace_gateway_stripe_public_key'] : '',
			'MARKETPLACE_GATEWAY_STRIPE_SECRET_KEY' => isset($this->config['marketplace_gateway_stripe_secret_key']) ? (string) $this->config['marketplace_gateway_stripe_secret_key'] : '',
			'MARKETPLACE_GATEWAY_PIX_ENABLED' => !empty($this->config['marketplace_gateway_pix_enabled']),
			'MARKETPLACE_GATEWAY_PIX_KEY_TYPE' => isset($this->config['marketplace_gateway_pix_key_type']) ? (string) $this->config['marketplace_gateway_pix_key_type'] : 'cpf',
			'MARKETPLACE_GATEWAY_PIX_KEY' => isset($this->config['marketplace_gateway_pix_key']) ? (string) $this->config['marketplace_gateway_pix_key'] : '',
			'MARKETPLACE_GATEWAY_PIX_KEY_MASKED' => $this->mask_sensitive_gateway_value(isset($this->config['marketplace_gateway_pix_key']) ? (string) $this->config['marketplace_gateway_pix_key'] : '', isset($this->config['marketplace_gateway_pix_key_type']) ? (string) $this->config['marketplace_gateway_pix_key_type'] : 'cpf'),
			'MARKETPLACE_GATEWAY_PIX_RECEIVER_NAME' => isset($this->config['marketplace_gateway_pix_receiver_name']) ? (string) $this->config['marketplace_gateway_pix_receiver_name'] : '',
			'MARKETPLACE_GATEWAY_PIX_RECEIVER_CITY' => isset($this->config['marketplace_gateway_pix_receiver_city']) ? (string) $this->config['marketplace_gateway_pix_receiver_city'] : '',
			'MARKETPLACE_GATEWAY_PIX_INSTRUCTIONS' => isset($this->config['marketplace_gateway_pix_instructions']) ? (string) $this->config['marketplace_gateway_pix_instructions'] : '',
			'MARKETPLACE_GATEWAY_PIX_DEADLINE_MINUTES' => isset($this->config['marketplace_gateway_pix_deadline_minutes']) ? (int) $this->config['marketplace_gateway_pix_deadline_minutes'] : 1440,
			'MARKETPLACE_GATEWAY_MERCADOPAGO_ENABLED' => !empty($this->config['marketplace_gateway_mercadopago_enabled']),
			'MARKETPLACE_GATEWAY_MERCADOPAGO_PUBLIC_KEY' => isset($this->config['marketplace_gateway_mercadopago_public_key']) ? (string) $this->config['marketplace_gateway_mercadopago_public_key'] : '',
			'MARKETPLACE_GATEWAY_MERCADOPAGO_ACCESS_TOKEN' => isset($this->config['marketplace_gateway_mercadopago_access_token']) ? (string) $this->config['marketplace_gateway_mercadopago_access_token'] : '',
			'MARKETPLACE_PAYPAL_IPN_URL' => $this->helper->route('mundophpbb_marketplace_paypal_ipn', [], true),
		]);
	}


	private function mask_sensitive_gateway_value($value, $type = '')
	{
		$value = trim((string) $value);
		$type = strtolower((string) $type);
		if ($value === '')
		{
			return '';
		}

		if ($type === 'cpf')
		{
			$digits = preg_replace('/\D+/', '', $value);
			if (strlen($digits) === 11)
			{
				return '***.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-**';
			}
		}
		if ($type === 'cnpj')
		{
			$digits = preg_replace('/\D+/', '', $value);
			if (strlen($digits) === 14)
			{
				return '**.' . substr($digits, 2, 3) . '.' . substr($digits, 5, 3) . '/' . substr($digits, 8, 4) . '-**';
			}
		}
		if (filter_var($value, FILTER_VALIDATE_EMAIL))
		{
			list($local, $domain) = explode('@', $value, 2);
			return substr($local, 0, 2) . '***@' . $domain;
		}
		if (strlen($value) <= 8)
		{
			return substr($value, 0, 2) . '***';
		}
		return substr($value, 0, 4) . '***' . substr($value, -4);
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
				'package_boosts'       => max(0, $this->request->variable('package_boosts', 0)),
				'package_ad_limit'     => max(0, $this->request->variable('package_ad_limit', 0)),
				'package_amount_cents' => max(0, $this->request->variable('package_amount_cents', 0)),
				'package_currency'     => $this->normalise_package_currency($this->request->variable('package_currency', isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL')),
				'package_billing_cycle'=> $this->request->variable('package_billing_cycle', 'none'),
				'package_auto_renew'   => $this->request->variable('package_auto_renew', 0),
				'package_is_professional' => $this->request->variable('package_is_professional', 0),
				'package_enabled'      => $this->request->variable('package_enabled', 1),
				'package_order'        => max(0, $this->request->variable('package_order', 0)),
				'package_updated'      => $now,
			];

			if (!in_array($sql_ary['package_type'], ['featured', 'boosted', 'renewal', 'boost_bundle', 'ad_quota', 'seller_plan'], true))
			{
				$sql_ary['package_type'] = 'featured';
			}

			if (!in_array($sql_ary['package_billing_cycle'], ['none', 'monthly', 'annual'], true))
			{
				$sql_ary['package_billing_cycle'] = 'none';
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


		if ($this->request->is_set_post('submit_coupon'))
		{
			if (!\check_form_key('mundophpbb_marketplace_acp_packages'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$now = time();
			$coupon_id = $this->request->variable('coupon_id', 0);
			$coupon_ary = [
				'coupon_code' => strtoupper(preg_replace('/[^A-Z0-9_-]/i', '', $this->request->variable('coupon_code', '', true))),
				'coupon_desc' => $this->request->variable('coupon_desc', '', true),
				'discount_type' => $this->request->variable('discount_type', 'percent'),
				'discount_value' => max(0, $this->request->variable('discount_value', 0)),
				'coupon_currency' => $this->normalise_package_currency($this->request->variable('coupon_currency', isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL')),
				'coupon_starts' => $this->parse_date_field('coupon_starts'),
				'coupon_ends' => $this->parse_date_field('coupon_ends'),
				'coupon_usage_limit' => max(0, $this->request->variable('coupon_usage_limit', 0)),
				'coupon_enabled' => $this->request->variable('coupon_enabled', 1),
				'coupon_updated' => $now,
			];
			if (!in_array($coupon_ary['discount_type'], ['percent', 'fixed'], true))
			{
				$coupon_ary['discount_type'] = 'percent';
			}
			if ($coupon_ary['coupon_code'] === '')
			{
				\trigger_error($this->language->lang('MARKETPLACE_COUPON_CODE_REQUIRED') . \adm_back_link($this->u_action), E_USER_WARNING);
			}
			if ($coupon_id)
			{
				$this->db->sql_query('UPDATE ' . $this->table_coupons . ' SET ' . $this->db->sql_build_array('UPDATE', $coupon_ary) . ' WHERE coupon_id = ' . (int) $coupon_id);
			}
			else
			{
				$coupon_ary['coupon_used_count'] = 0;
				$coupon_ary['coupon_created'] = $now;
				$this->db->sql_query('INSERT INTO ' . $this->table_coupons . ' ' . $this->db->sql_build_array('INSERT', $coupon_ary));
			}
			\trigger_error($this->language->lang('MARKETPLACE_COUPON_SAVED') . \adm_back_link($this->u_action));
		}

		if ($this->request->is_set_post('submit_period'))
		{
			if (!\check_form_key('mundophpbb_marketplace_acp_packages'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$period_id = $this->request->variable('period_id', 0);
			$period_ary = [
				'period_title' => $this->request->variable('period_title', '', true),
				'period_package_type' => $this->request->variable('period_package_type', 'all'),
				'discount_type' => $this->request->variable('period_discount_type', 'percent'),
				'discount_value' => max(0, $this->request->variable('period_discount_value', 0)),
				'period_starts' => $this->parse_date_field('period_starts'),
				'period_ends' => $this->parse_date_field('period_ends'),
				'period_enabled' => $this->request->variable('period_enabled', 1),
			];
			if (!in_array($period_ary['period_package_type'], ['all', 'featured', 'boosted', 'renewal', 'boost_bundle', 'ad_quota', 'seller_plan'], true))
			{
				$period_ary['period_package_type'] = 'all';
			}
			if (!in_array($period_ary['discount_type'], ['percent', 'fixed'], true))
			{
				$period_ary['discount_type'] = 'percent';
			}
			if ((string) $period_ary['period_title'] === '')
			{
				\trigger_error($this->language->lang('MARKETPLACE_PERIOD_TITLE_REQUIRED') . \adm_back_link($this->u_action), E_USER_WARNING);
			}
			if ($period_id)
			{
				$this->db->sql_query('UPDATE ' . $this->table_promo_periods . ' SET ' . $this->db->sql_build_array('UPDATE', $period_ary) . ' WHERE period_id = ' . (int) $period_id);
			}
			else
			{
				$this->db->sql_query('INSERT INTO ' . $this->table_promo_periods . ' ' . $this->db->sql_build_array('INSERT', $period_ary));
			}
			\trigger_error($this->language->lang('MARKETPLACE_PERIOD_SAVED') . \adm_back_link($this->u_action));
		}

		if ($this->request->is_set_post('submit_free_group'))
		{
			if (!\check_form_key('mundophpbb_marketplace_acp_packages'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			$group_id = $this->request->variable('free_group_id', 0);
			if ($group_id)
			{
				$sql_ary = [
					'group_id' => $group_id,
					'free_featured' => $this->request->variable('free_featured', 0),
					'free_boosted' => $this->request->variable('free_boosted', 0),
					'free_seller_plan' => $this->request->variable('free_seller_plan', 0),
				];
				$sql = 'SELECT free_id FROM ' . $this->table_group_freebies . ' WHERE group_id = ' . (int) $group_id;
				$result = $this->db->sql_query_limit($sql, 1);
				$free_id = (int) $this->db->sql_fetchfield('free_id');
				$this->db->sql_freeresult($result);
				if ($free_id)
				{
					$this->db->sql_query('UPDATE ' . $this->table_group_freebies . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE free_id = ' . (int) $free_id);
				}
				else
				{
					$this->db->sql_query('INSERT INTO ' . $this->table_group_freebies . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
				}
			}
			\trigger_error($this->language->lang('MARKETPLACE_FREE_GROUP_SAVED') . \adm_back_link($this->u_action));
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
				'package_boosts' => 0,
				'package_ad_limit' => 0,
				'package_amount_cents' => 0,
				'package_currency' => isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL',
				'package_billing_cycle' => 'none',
				'package_auto_renew' => 0,
				'package_is_professional' => 0,
				'package_enabled' => 1,
				'package_order' => 0,
			],
			'PACKAGE_CURRENCY_OPTIONS' => $this->build_currency_select_options($edit_package && !empty($edit_package['package_currency']) ? (string) $edit_package['package_currency'] : (isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL')),
			'S_EDIT_PACKAGE' => !empty($edit_package),
			'COUPONS' => $this->get_coupons(),
			'PROMO_PERIODS' => $this->get_promo_periods(),
			'GROUP_FREEBIES' => $this->get_group_freebies(),
			'GROUP_OPTIONS' => $this->get_package_group_options(),
		]);
	}


	private function parse_date_field($field)
	{
		$value = trim($this->request->variable($field, ''));
		if ($value === '')
		{
			return 0;
		}
		$time = strtotime($value . ' 00:00:00');
		return $time ? (int) $time : 0;
	}

	private function format_date_for_acp($time)
	{
		return ((int) $time > 0) ? $this->user->format_date((int) $time, 'd/m/Y') : '-';
	}

	private function get_coupons()
	{
		$sql = 'SELECT * FROM ' . $this->table_coupons . ' ORDER BY coupon_enabled DESC, coupon_id DESC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['DISCOUNT_DISPLAY'] = $this->format_discount_display($row['discount_type'], (int) $row['discount_value'], isset($row['coupon_currency']) ? $row['coupon_currency'] : '');
			$row['STARTS_DISPLAY'] = $this->format_date_for_acp($row['coupon_starts']);
			$row['ENDS_DISPLAY'] = $this->format_date_for_acp($row['coupon_ends']);
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_promo_periods()
	{
		$sql = 'SELECT * FROM ' . $this->table_promo_periods . ' ORDER BY period_enabled DESC, period_starts DESC, period_id DESC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PACKAGE_TYPE_LANG'] = $row['period_package_type'] === 'all' ? $this->language->lang('MARKETPLACE_ALL_TYPES') : $this->get_promotion_type_lang($row['period_package_type']);
			$row['DISCOUNT_DISPLAY'] = $this->format_discount_display($row['discount_type'], (int) $row['discount_value'], '');
			$row['STARTS_DISPLAY'] = $this->format_date_for_acp($row['period_starts']);
			$row['ENDS_DISPLAY'] = $this->format_date_for_acp($row['period_ends']);
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_group_freebies()
	{
		$sql = 'SELECT gf.*, g.group_name, g.group_type
			FROM ' . $this->table_group_freebies . ' gf
			LEFT JOIN ' . GROUPS_TABLE . ' g ON g.group_id = gf.group_id
			ORDER BY g.group_name ASC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['GROUP_NAME_DISPLAY'] = $this->format_group_name($row['group_name'], (int) $row['group_type']);
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_package_group_options()
	{
		$sql = 'SELECT group_id, group_name, group_type FROM ' . GROUPS_TABLE . ' ORDER BY group_type ASC, group_name ASC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = [
				'GROUP_ID' => (int) $row['group_id'],
				'GROUP_NAME' => $this->format_group_name($row['group_name'], (int) $row['group_type']),
			];
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function format_discount_display($type, $value, $currency = '')
	{
		if ($type === 'fixed')
		{
			return trim((string) $currency . ' ' . number_format(((int) $value) / 100, 2, ',', '.'));
		}
		return (int) $value . '%';
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

		// The add form must always create a fresh category.
		// Editing is only allowed when a valid cat_id is explicitly preserved in the form.
		if ($action === 'add')
		{
			$cat_id = 0;
		}

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
			$cat_require_approval = $this->request->variable('cat_require_approval', 0);
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
					'cat_require_approval' => $cat_require_approval,
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
					'cat_require_approval' => $cat_require_approval,
					'cat_allowed_types'    => $cat_allowed_types,
				];
				$sql = 'INSERT INTO ' . $this->table_cats . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
				$this->db->sql_query($sql);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_CAT_ADDED');
				$msg = $this->language->lang('MARKETPLACE_CAT_ADDED');
				$cat_id = (int) $this->db->sql_nextid();
			}

			$this->save_category_custom_fields((int) $cat_id, $this->request->variable('cat_custom_fields', '', true));

			\trigger_error($msg . \adm_back_link($this->u_action));
		}

		$cat_data = [];
		if ($cat_id)
		{
			$sql = 'SELECT * FROM ' . $this->table_cats . ' WHERE cat_id = ' . (int) $cat_id;
			$result = $this->db->sql_query($sql);
			$cat_data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$cat_data)
			{
				$cat_id = 0;
			}
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
			'CAT_REQUIRE_APPROVAL' => !empty($cat_data['cat_require_approval']),
			'CAT_ALLOWED_TYPES' => isset($cat_data['cat_allowed_types']) ? $cat_data['cat_allowed_types'] : '1,2,3,4,5,6',
			'CAT_TYPE_OPTIONS' => $this->get_category_type_options(isset($cat_data['cat_allowed_types']) ? $cat_data['cat_allowed_types'] : '1,2,3,4,5,6'),
			'CAT_CUSTOM_FIELDS' => $cat_id ? $this->format_category_custom_fields_for_textarea((int) $cat_id) : '',

			'S_EDIT'     => (bool) $cat_id,
			'S_ADD'      => ($action === 'add'),

			'categories' => $categories,
		]);
	}


	private function save_category_custom_fields($cat_id, $raw)
	{
		$cat_id = (int) $cat_id;
		if ($cat_id <= 0 || empty($this->table_category_fields))
		{
			return;
		}

		$this->db->sql_query('DELETE FROM ' . $this->table_category_fields . ' WHERE cat_id = ' . $cat_id);
		$lines = preg_split('/\r\n|\r|\n/', (string) $raw);
		$order = 0;
		foreach ($lines as $line)
		{
			$line = trim($line);
			if ($line === '') { continue; }
			$parts = array_map('trim', explode('|', $line));
			$label = isset($parts[0]) ? $parts[0] : '';
			if ($label === '') { continue; }
			$required = isset($parts[1]) && in_array(strtolower($parts[1]), ['1', 'sim', 'yes', 'required', 'obrigatorio', 'obrigatório'], true) ? 1 : 0;
			$type = isset($parts[2]) && in_array($parts[2], ['text', 'number', 'url'], true) ? $parts[2] : 'text';
			$sql_ary = [
				'cat_id' => $cat_id,
				'field_label' => $label,
				'field_type' => $type,
				'field_required' => $required,
				'field_order' => $order,
			];
			$this->db->sql_query('INSERT INTO ' . $this->table_category_fields . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
			$order += 10;
		}
	}

	private function format_category_custom_fields_for_textarea($cat_id)
	{
		$cat_id = (int) $cat_id;
		if ($cat_id <= 0 || empty($this->table_category_fields))
		{
			return '';
		}
		$sql = 'SELECT * FROM ' . $this->table_category_fields . ' WHERE cat_id = ' . $cat_id . ' ORDER BY field_order ASC, field_id ASC';
		$result = $this->db->sql_query($sql);
		$lines = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$lines[] = $row['field_label'] . '|' . (!empty($row['field_required']) ? 'required' : 'optional') . '|' . (isset($row['field_type']) ? $row['field_type'] : 'text');
		}
		$this->db->sql_freeresult($result);
		return implode("\n", $lines);
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

		if ($action === 'export_csv')
		{
			$this->export_ads_csv();
		}

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
		$filter_q = trim($this->request->variable('q', '', true));

		$where = [];
		if ($filter_status >= 0)
		{
			$where[] = 'a.ad_status = ' . (int) $filter_status;
		}
		if ($filter_q !== '')
		{
			$q = $this->db->sql_escape($filter_q);
			$where[] = "(a.ad_title LIKE '%$q%' OR a.ad_desc LIKE '%$q%' OR u.username LIKE '%$q%' OR c.cat_name LIKE '%$q%' OR CAST(a.ad_id AS CHAR) = '$q')";
		}
		$sql_where = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

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

		$pagination_url = $this->u_action . '&amp;status=' . $filter_status . ($filter_q !== '' ? '&amp;q=' . urlencode($filter_q) : '');
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total, $limit, $start);

		$pending_promotions = $this->get_pending_promotions();
		$pending_purchases = $this->get_pending_purchases();
		$payment_logs = $this->get_payment_logs();
		$promotion_subscribers = $this->get_promotion_subscribers();

		$this->template->assign_vars([
			'U_ACTION'     => $this->u_action,
			'ads'          => $ads,
			'S_FILTER'     => $filter_status,
			'FILTER_Q'     => $filter_q,
			'U_EXPORT_CSV' => $pagination_url . '&amp;action=export_csv',
			'TOTAL_ADS'    => $total,
			'PENDING_PROMOTIONS' => $pending_promotions,
			'S_HAS_PENDING_PROMOTIONS' => !empty($pending_promotions),
			'PENDING_PURCHASES' => $pending_purchases,
			'S_HAS_PENDING_PURCHASES' => !empty($pending_purchases),
			'PAYMENT_LOGS' => $payment_logs,
			'S_HAS_PAYMENT_LOGS' => !empty($payment_logs),
			'PROMOTION_SUBSCRIBERS' => $promotion_subscribers,
			'S_HAS_PROMOTION_SUBSCRIBERS' => !empty($promotion_subscribers),
			'FEATURED_DAYS_DEFAULT' => isset($this->config['marketplace_featured_days']) ? (int) $this->config['marketplace_featured_days'] : 14,
			'BOOSTED_DAYS_DEFAULT' => isset($this->config['marketplace_boosted_days']) ? (int) $this->config['marketplace_boosted_days'] : 7,
		]);
	}



	public function display_notifications()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');
		\add_form_key('mundophpbb_marketplace_acp_notifications');

		$action = $this->request->variable('action', '');
		$purchase_id = $this->request->variable('purchase_id', 0);
		$promotion_id = $this->request->variable('promotion_id', 0);
		$notification_id = $this->request->variable('notification_id', 0);

		if ($action && in_array($action, ['mark_notification_read', 'delete_notification', 'delete_old_notifications', 'approve_purchase', 'reject_purchase', 'approve_promotion', 'reject_promotion'], true))
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_notifications'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($action === 'mark_notification_read')
			{
				$this->mark_acp_notification_read($notification_id);
				\trigger_error($this->language->lang('MARKETPLACE_NOTIFICATION_MARKED_READ') . \adm_back_link($this->u_action));
			}

			if ($action === 'delete_notification')
			{
				$this->delete_acp_notification($notification_id);
				\trigger_error($this->language->lang('MARKETPLACE_NOTIFICATION_DELETED') . \adm_back_link($this->u_action));
			}

			if ($action === 'delete_old_notifications')
			{
				$days = max(1, $this->request->variable('days', 90));
				$this->delete_old_acp_notifications($days);
				\trigger_error($this->language->lang('MARKETPLACE_OLD_NOTIFICATIONS_DELETED') . \adm_back_link($this->u_action));
			}

			if ($purchase_id)
			{
				$this->handle_purchase_action($action, $purchase_id);
			}

			if ($promotion_id)
			{
				$this->handle_promotion_action($action, $promotion_id);
			}
		}

		$filter_type = $this->request->variable('notification_type', '');
		$filter_read = $this->request->variable('notification_read', '');
		$filter_q = trim($this->request->variable('q', '', true));
		if ($this->request->variable('action', '') === 'export_csv')
		{
			$this->export_notifications_csv($filter_type, $filter_read, $filter_q);
		}
		$start = $this->request->variable('start', 0);
		$limit = max(10, (int) $this->config['marketplace_items_per_page']);

		$marketplace_notifications = $this->get_acp_notifications($filter_type, $filter_read, $limit, $start, $filter_q);
		$total_notifications = $this->count_acp_notifications($filter_type, $filter_read, $filter_q);
		$pagination_url = $this->u_action . '&amp;notification_type=' . urlencode($filter_type) . '&amp;notification_read=' . urlencode($filter_read) . ($filter_q !== '' ? '&amp;q=' . urlencode($filter_q) : '');
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total_notifications, $limit, $start);
		$notification_types = $this->get_acp_notification_types();
		$pending_promotions = $this->get_pending_promotions();
		$pending_purchases = $this->get_pending_purchases();
		$payment_logs = $this->get_payment_logs();
		$promotion_subscribers = $this->get_promotion_subscribers();

		$this->template->assign_vars([
			'U_ACTION' => $this->u_action,
			'MARKETPLACE_NOTIFICATIONS' => $marketplace_notifications,
			'S_HAS_MARKETPLACE_NOTIFICATIONS' => !empty($marketplace_notifications),
			'NOTIFICATION_TYPES' => $notification_types,
			'S_FILTER_NOTIFICATION_TYPE' => $filter_type,
			'S_FILTER_NOTIFICATION_READ' => $filter_read,
			'FILTER_Q' => $filter_q,
			'TOTAL_NOTIFICATIONS' => $total_notifications,
			'U_EXPORT_CSV' => $pagination_url . '&amp;action=export_csv',
			'PENDING_PROMOTIONS' => $pending_promotions,
			'S_HAS_PENDING_PROMOTIONS' => !empty($pending_promotions),
			'PENDING_PURCHASES' => $pending_purchases,
			'S_HAS_PENDING_PURCHASES' => !empty($pending_purchases),
			'PAYMENT_LOGS' => $payment_logs,
			'S_HAS_PAYMENT_LOGS' => !empty($payment_logs),
			'PROMOTION_SUBSCRIBERS' => $promotion_subscribers,
			'S_HAS_PROMOTION_SUBSCRIBERS' => !empty($promotion_subscribers),
		]);
	}

	private function get_acp_notifications($filter_type = '', $filter_read = '', $limit = 100, $start = 0, $filter_q = '')
	{
		$where = [];
		if ($filter_type !== '')
		{
			$where[] = "n.notification_type = '" . $this->db->sql_escape($filter_type) . "'";
		}
		if ($filter_read !== '' && in_array($filter_read, ['0', '1'], true))
		{
			$where[] = 'n.notification_read = ' . (int) $filter_read;
		}
		if ($filter_q !== '')
		{
			$q = $this->db->sql_escape($filter_q);
			$where[] = "(n.notification_title LIKE '%$q%' OR n.notification_message LIKE '%$q%' OR a.ad_title LIKE '%$q%' OR u.username LIKE '%$q%')";
		}

		$sql_where = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';
		$sql = 'SELECT n.*, a.ad_title, u.username, u.user_colour
			FROM ' . $this->table_notifications . ' n
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = n.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = n.user_id' .
			$sql_where . '
			ORDER BY n.notification_time DESC';
		$result = $this->db->sql_query_limit($sql, (int) $limit, (int) $start);
		$notifications = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['NOTIFICATION_TIME_DISPLAY'] = !empty($row['notification_time']) ? $this->user->format_date((int) $row['notification_time']) : '';
			$row['NOTIFICATION_TYPE_LANG'] = $this->get_notification_type_lang($row['notification_type']);
			$row['USERNAME_DISPLAY'] = !empty($row['username']) ? $row['username'] : $this->language->lang('MARKETPLACE_SYSTEM_NOTIFICATION');
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$notifications[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $notifications;
	}


	private function count_acp_notifications($filter_type = '', $filter_read = '', $filter_q = '')
	{
		$where = [];
		if ($filter_type !== '')
		{
			$where[] = "n.notification_type = '" . $this->db->sql_escape($filter_type) . "'";
		}
		if ($filter_read !== '' && in_array($filter_read, ['0', '1'], true))
		{
			$where[] = 'n.notification_read = ' . (int) $filter_read;
		}
		if ($filter_q !== '')
		{
			$q = $this->db->sql_escape($filter_q);
			$where[] = "(n.notification_title LIKE '%$q%' OR n.notification_message LIKE '%$q%' OR a.ad_title LIKE '%$q%' OR u.username LIKE '%$q%')";
		}
		$sql_where = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';
		$sql = 'SELECT COUNT(*) AS total
			FROM ' . $this->table_notifications . ' n
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = n.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = n.user_id' . $sql_where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		return $total;
	}

	private function export_notifications_csv($filter_type = '', $filter_read = '', $filter_q = '')
	{
		$rows = [];
		foreach ($this->get_acp_notifications($filter_type, $filter_read, 100000, 0, $filter_q) as $row)
		{
			$rows[] = [(int) $row['notification_id'], $row['notification_type'], $row['USERNAME_DISPLAY'], $row['ad_title'], $row['notification_title'], $row['notification_message'], (int) $row['notification_read'], !empty($row['notification_time']) ? date('Y-m-d H:i:s', (int) $row['notification_time']) : ''];
		}
		$this->export_csv_response('marketplace-notifications.csv', ['id', 'type', 'user', 'ad', 'title', 'message', 'read', 'created'], $rows);
	}

	private function get_acp_notification_types()
	{
		$sql = 'SELECT DISTINCT notification_type
			FROM ' . $this->table_notifications . "
			WHERE notification_type <> ''
			ORDER BY notification_type ASC";
		$result = $this->db->sql_query($sql);
		$types = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$types[] = [
				'VALUE' => $row['notification_type'],
				'LABEL' => $this->get_notification_type_lang($row['notification_type']),
			];
		}
		$this->db->sql_freeresult($result);

		return $types;
	}

	private function mark_acp_notification_read($notification_id)
	{
		if ($notification_id <= 0)
		{
			return;
		}
		$this->db->sql_query('UPDATE ' . $this->table_notifications . ' SET notification_read = 1 WHERE notification_id = ' . (int) $notification_id);
	}

	private function delete_acp_notification($notification_id)
	{
		if ($notification_id <= 0)
		{
			return;
		}
		$this->db->sql_query('DELETE FROM ' . $this->table_notifications . ' WHERE notification_id = ' . (int) $notification_id);
	}

	private function delete_old_acp_notifications($days)
	{
		$cutoff = time() - (max(1, (int) $days) * 86400);
		$this->db->sql_query('DELETE FROM ' . $this->table_notifications . ' WHERE notification_time > 0 AND notification_time < ' . (int) $cutoff);
	}

	private function get_notification_type_lang($type)
	{
		$key = 'MARKETPLACE_NOTIFICATION_TYPE_' . strtoupper(preg_replace('/[^A-Z0-9_]/i', '_', (string) $type));
		$label = $this->language->lang($key);
		return ($label === $key) ? (string) $type : $label;
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
			$row['PAYMENT_LAST_STATUS_DISPLAY'] = $this->get_latest_payment_log_status((int) $row['promotion_id']);
			$row['U_VIEW'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$promotions[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $promotions;
	}


	private function get_promotion_subscribers()
	{
		$sql = 'SELECT p.*, a.ad_title, a.ad_status, a.ad_featured_until, a.ad_boosted_until, u.username, u.user_colour
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			WHERE p.promotion_status IN (0, 1, 3)
			ORDER BY p.promotion_requested DESC, p.promotion_id DESC';
		$result = $this->db->sql_query_limit($sql, 50);

		$subscribers = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PROMOTION_TYPE_LANG'] = $this->get_promotion_type_lang($row['promotion_type']);
			$row['PROMOTION_STATUS_LANG'] = $this->get_promotion_status_lang((int) $row['promotion_status']);
			$row['PROMOTION_REQUESTED_DISPLAY'] = !empty($row['promotion_requested']) ? $this->user->format_date((int) $row['promotion_requested']) : '';
			$row['PROMOTION_PRICE_DISPLAY'] = $this->format_package_price((int) $row['promotion_amount_cents'], $row['promotion_currency']);
			$row['PROMOTION_PACKAGE_TITLE'] = !empty($row['promotion_note']) ? $row['promotion_note'] : '-';
			$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper($row['payment_provider']) : '-';
			$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? $row['payment_reference'] : '-';
			$row['PAYMENT_LAST_STATUS_DISPLAY'] = $this->get_latest_payment_log_status((int) $row['promotion_id']);
			$row['U_VIEW'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$until = ($row['promotion_type'] === 'featured') ? (int) $row['ad_featured_until'] : (int) $row['ad_boosted_until'];
			$row['PROMOTION_UNTIL_DISPLAY'] = $until > 0 ? $this->user->format_date($until) : '-';
			$row['S_PROMOTION_ACTIVE'] = ((int) $row['promotion_status'] === 1 && $until >= time());
			$subscribers[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $subscribers;
	}


	private function get_latest_payment_log_status($promotion_id)
	{
		$sql = 'SELECT payment_validation_status, payment_transaction_id, payment_created
			FROM ' . $this->table_payment_logs . '
			WHERE promotion_id = ' . (int) $promotion_id . '
			ORDER BY payment_created DESC, payment_log_id DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return '';
		}

		$parts = [$this->get_payment_validation_status_lang($row['payment_validation_status'])];
		if (!empty($row['payment_transaction_id']))
		{
			$parts[] = 'TXN: ' . $row['payment_transaction_id'];
		}
		if (!empty($row['payment_created']))
		{
			$parts[] = $this->user->format_date((int) $row['payment_created']);
		}

		return implode(' | ', $parts);
	}

	private function get_payment_logs()
	{
		$sql = 'SELECT l.*, p.promotion_type, p.promotion_status, a.ad_title, u.username
			FROM ' . $this->table_payment_logs . ' l
			LEFT JOIN ' . $this->table_promotions . ' p ON p.promotion_id = l.promotion_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			ORDER BY l.payment_created DESC, l.payment_log_id DESC';
		$result = $this->db->sql_query_limit($sql, 25);

		$logs = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PAYMENT_CREATED_DISPLAY'] = !empty($row['payment_created']) ? $this->user->format_date((int) $row['payment_created']) : '';
			$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper($row['payment_provider']) : '-';
			$row['PAYMENT_AMOUNT_DISPLAY'] = $this->format_package_price((int) $row['payment_amount_cents'], $row['payment_currency']);
			$row['PAYMENT_VERIFICATION_STATUS_LANG'] = $this->get_payment_verification_status_lang($row['payment_verification_status']);
			$row['PAYMENT_VALIDATION_STATUS_LANG'] = $this->get_payment_validation_status_lang($row['payment_validation_status']);
			$row['PROMOTION_TYPE_LANG'] = !empty($row['promotion_type']) ? $this->get_promotion_type_lang($row['promotion_type']) : '-';
			$row['PROMOTION_STATUS_LANG'] = isset($row['promotion_status']) ? $this->get_promotion_status_lang((int) $row['promotion_status']) : '-';
			$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? $row['payment_reference'] : '-';
			$row['PAYMENT_TRANSACTION_DISPLAY'] = !empty($row['payment_transaction_id']) ? $row['payment_transaction_id'] : '-';
			$row['PAYMENT_RECEIVER_DISPLAY'] = !empty($row['payment_receiver']) ? $row['payment_receiver'] : '-';
			$logs[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $logs;
	}

	private function get_payment_verification_status_lang($status)
	{
		$status = strtolower(trim((string) $status));

		switch ($status)
		{
			case 'verified':
				return $this->language->lang('MARKETPLACE_PAYMENT_VERIFIED');
			case 'invalid':
				return $this->language->lang('MARKETPLACE_PAYMENT_INVALID');
			default:
				return $status !== '' ? strtoupper($status) : '-';
		}
	}

	private function get_payment_validation_status_lang($status)
	{
		$status = strtoupper(trim((string) $status));

		switch ($status)
		{
			case 'OK':
				return $this->language->lang('MARKETPLACE_PAYMENT_APPROVED_AUTOMATICALLY');
			case 'PAYMENT_MISMATCH':
				return $this->language->lang('MARKETPLACE_PAYMENT_MISMATCH');
			case 'PROMOTION_NOT_FOUND':
				return $this->language->lang('MARKETPLACE_PAYMENT_PROMOTION_NOT_FOUND');
			case 'ALREADY_APPROVED':
				return $this->language->lang('MARKETPLACE_PAYMENT_ALREADY_APPROVED');
			case 'AD_NOT_ACTIVE':
				return $this->language->lang('MARKETPLACE_PAYMENT_AD_NOT_ACTIVE');
			case 'IGNORED_STATUS':
				return $this->language->lang('MARKETPLACE_PAYMENT_IGNORED_STATUS');
			case 'PAYPAL_NOT_VERIFIED':
				return $this->language->lang('MARKETPLACE_PAYMENT_PAYPAL_NOT_VERIFIED');
			case 'MISSING_REFERENCE':
				return $this->language->lang('MARKETPLACE_PAYMENT_MISSING_REFERENCE');
			case 'PROMOTION_NOT_AWAITING_PAYMENT':
				return $this->language->lang('MARKETPLACE_PAYMENT_NOT_AWAITING');
			case 'EMPTY':
				return $this->language->lang('MARKETPLACE_PAYMENT_EMPTY');
			case 'PENDING_MANUAL_CONFIRMATION':
				return $this->language->lang('MARKETPLACE_PAYMENT_PENDING_MANUAL_CONFIRMATION');
			default:
				return $status !== '' ? $status : '-';
		}
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
			$row['PAYMENT_LAST_STATUS_DISPLAY'] = $this->get_latest_payment_log_status((int) $row['promotion_id']);
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
			case 4:
				return $this->language->lang('MARKETPLACE_PURCHASE_STATUS_CANCELLED');
			case 5:
				return $this->language->lang('MARKETPLACE_PURCHASE_STATUS_COMPLETED');
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
			case 'boost_bundle':
				return $this->language->lang('MARKETPLACE_PACKAGE_TYPE_BOOST_BUNDLE');
			case 'ad_quota':
				return $this->language->lang('MARKETPLACE_PACKAGE_TYPE_AD_QUOTA');
			case 'seller_plan':
				return $this->language->lang('MARKETPLACE_PACKAGE_TYPE_SELLER_PLAN');
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
				$this->add_moderation_log($ad_id, (int) $ad['user_id'], 'ad_approved', '');
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
					'ad_refusal_reason' => $reason,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MARKETPLACE_AD_REJECTED', false, ['ad_id' => $ad_id]);
				$this->add_moderation_log($ad_id, (int) $ad['user_id'], 'ad_rejected', $reason);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'hidden', $this->language->lang('MARKETPLACE_NOTIFICATION_HIDDEN_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_HIDDEN_MESSAGE', $ad['ad_title'], $reason !== '' ? $reason : $this->language->lang('MARKETPLACE_NO_REASON_GIVEN')));
				\trigger_error($this->language->lang('MARKETPLACE_AD_REJECTED') . \adm_back_link($this->u_action));
			break;

			case 'delete':
				if (\confirm_box(true))
				{
					$this->add_moderation_log($ad_id, (int) $ad['user_id'], 'ad_removed', '');
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
				$this->add_moderation_log($ad_id, (int) $ad['user_id'], 'ad_marked_sold', '');
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


	public function display_security()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');
		\add_form_key('mundophpbb_marketplace_acp_security');

		$action = $this->request->variable('action', '');
		if ($this->request->is_set_post('submit_action') || $this->request->is_set_post('submit'))
		{
			if (!\check_form_key('mundophpbb_marketplace_acp_security'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}

			switch ($action)
			{
				case 'add_term':
					$term = trim($this->request->variable('term_text', '', true));
					if ($term === '')
					{
						\trigger_error($this->language->lang('MARKETPLACE_FORBIDDEN_TERM_REQUIRED') . \adm_back_link($this->u_action), E_USER_WARNING);
					}
					$sql_ary = ['term_text' => $term, 'term_enabled' => 1, 'term_created' => time()];
					$this->db->sql_query('INSERT INTO ' . $this->table_forbidden_terms . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
					$this->add_moderation_log(0, 0, 'forbidden_term_added', $term);
				break;

				case 'toggle_term':
					$term_id = $this->request->variable('term_id', 0);
					$sql = 'SELECT term_enabled FROM ' . $this->table_forbidden_terms . ' WHERE term_id = ' . (int) $term_id;
					$result = $this->db->sql_query($sql);
					$enabled = (int) $this->db->sql_fetchfield('term_enabled');
					$this->db->sql_freeresult($result);
					$this->db->sql_query('UPDATE ' . $this->table_forbidden_terms . ' SET term_enabled = ' . ($enabled ? 0 : 1) . ' WHERE term_id = ' . (int) $term_id);
					$this->add_moderation_log(0, 0, 'forbidden_term_toggled', 'term_id=' . $term_id);
				break;

				case 'delete_term':
					$term_id = $this->request->variable('term_id', 0);
					$this->db->sql_query('DELETE FROM ' . $this->table_forbidden_terms . ' WHERE term_id = ' . (int) $term_id);
					$this->add_moderation_log(0, 0, 'forbidden_term_deleted', 'term_id=' . $term_id);
				break;

				case 'save_user_limit':
					$username = trim($this->request->variable('username', '', true));
					$user_id = $this->get_user_id_by_username($username);
					if (!$user_id)
					{
						\trigger_error($this->language->lang('NO_USER') . \adm_back_link($this->u_action), E_USER_WARNING);
					}
					$max_ads = max(0, $this->request->variable('max_ads', 0));
					$this->upsert_user_limit($user_id, $max_ads);
					$this->add_moderation_log(0, $user_id, 'user_limit_saved', (string) $max_ads);
				break;

				case 'delete_user_limit':
					$user_id = $this->request->variable('user_id', 0);
					$this->db->sql_query('DELETE FROM ' . $this->table_user_limits . ' WHERE user_id = ' . (int) $user_id);
					$this->add_moderation_log(0, $user_id, 'user_limit_deleted', '');
				break;

				case 'save_group_limit':
					$group_id = $this->request->variable('group_id', 0);
					$max_ads = max(0, $this->request->variable('max_ads', 0));
					if ($group_id <= 0)
					{
						\trigger_error($this->language->lang('MARKETPLACE_GROUP_REQUIRED') . \adm_back_link($this->u_action), E_USER_WARNING);
					}
					$this->upsert_group_limit($group_id, $max_ads);
					$this->add_moderation_log(0, 0, 'group_limit_saved', 'group_id=' . $group_id . '; max=' . $max_ads);
				break;

				case 'delete_group_limit':
					$group_id = $this->request->variable('group_id', 0);
					$this->db->sql_query('DELETE FROM ' . $this->table_group_limits . ' WHERE group_id = ' . (int) $group_id);
					$this->add_moderation_log(0, 0, 'group_limit_deleted', 'group_id=' . $group_id);
				break;

				case 'save_user_security':
					$username = trim($this->request->variable('security_username', '', true));
					$user_id = $this->get_user_id_by_username($username);
					if (!$user_id)
					{
						\trigger_error($this->language->lang('NO_USER') . \adm_back_link($this->u_action), E_USER_WARNING);
					}
					$sql_ary = [
						'user_id' => $user_id,
						'seller_suspended' => $this->request->variable('seller_suspended', 0),
						'publish_blocked' => $this->request->variable('publish_blocked', 0),
						'verified_seller' => $this->request->variable('verified_seller', 0),
						'security_note' => trim($this->request->variable('security_note', '', true)),
						'updated_at' => time(),
						'updated_by' => (int) $this->user->data['user_id'],
					];
					$this->db->sql_query('DELETE FROM ' . $this->table_user_security . ' WHERE user_id = ' . (int) $user_id);
					$this->db->sql_query('INSERT INTO ' . $this->table_user_security . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
					$this->add_moderation_log(0, $user_id, 'user_security_saved', $sql_ary['security_note']);
				break;

				case 'clear_user_security':
					$user_id = $this->request->variable('user_id', 0);
					$this->db->sql_query('DELETE FROM ' . $this->table_user_security . ' WHERE user_id = ' . (int) $user_id);
					$this->add_moderation_log(0, $user_id, 'user_security_cleared', '');
				break;
			}

			\trigger_error($this->language->lang('MARKETPLACE_SECURITY_UPDATED') . \adm_back_link($this->u_action));
		}

		$this->template->assign_vars([
			'U_ACTION' => $this->u_action,
			'FORBIDDEN_TERMS' => $this->get_forbidden_terms(),
			'USER_LIMITS' => $this->get_user_limits(),
			'GROUP_LIMITS' => $this->get_group_limits(),
			'GROUP_OPTIONS' => $this->get_package_group_options(),
			'USER_SECURITY' => $this->get_user_security_rows(),
			'SUSPICIOUS_ADS' => $this->get_suspicious_ads(),
			'MODERATION_LOGS' => $this->get_moderation_logs(),
		]);
	}


	public function display_financial_reports()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');

		$filters = $this->get_financial_report_filters();
		if ($this->request->variable('action', '') === 'export_csv')
		{
			$this->export_financial_report_csv($filters);
		}

		$payment_logs = $this->get_financial_payment_logs($filters, 50);
		$all_payment_logs = $this->get_financial_payment_logs($filters, 0);
		$summary = $this->build_financial_summary($all_payment_logs);

		$base_url = $this->build_financial_report_url($filters);
		$this->template->assign_vars(array_merge($summary, [
			'U_ACTION' => $this->u_action,
			'U_EXPORT_CSV' => $base_url . '&amp;action=export_csv',
			'FILTER_START_DATE' => $filters['start_date'],
			'FILTER_END_DATE' => $filters['end_date'],
			'FILTER_USER_ID' => $filters['user_id'] > 0 ? $filters['user_id'] : '',
			'FILTER_PROMOTION_TYPE' => $filters['promotion_type'],
			'PROMOTION_TYPE_OPTIONS' => $this->get_financial_promotion_type_options($filters['promotion_type']),
			'FINANCIAL_PAYMENT_LOGS' => $payment_logs,
		]));
	}

	private function get_financial_report_filters()
	{
		$promotion_type = $this->request->variable('promotion_type', '');
		$allowed_types = ['featured', 'boosted', 'renewal'];
		if (!in_array($promotion_type, $allowed_types, true))
		{
			$promotion_type = '';
		}

		return [
			'start_date' => $this->normalise_financial_date($this->request->variable('start_date', '')),
			'end_date' => $this->normalise_financial_date($this->request->variable('end_date', '')),
			'user_id' => max(0, $this->request->variable('user_id', 0)),
			'promotion_type' => $promotion_type,
		];
	}

	private function normalise_financial_date($date)
	{
		$date = trim((string) $date);
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
		{
			return '';
		}

		$parts = array_map('intval', explode('-', $date));
		return checkdate($parts[1], $parts[2], $parts[0]) ? $date : '';
	}

	private function get_financial_where($filters)
	{
		$where = ['1 = 1'];
		if (!empty($filters['start_date']))
		{
			$where[] = 'l.payment_created >= ' . (int) strtotime($filters['start_date'] . ' 00:00:00');
		}
		if (!empty($filters['end_date']))
		{
			$where[] = 'l.payment_created <= ' . (int) strtotime($filters['end_date'] . ' 23:59:59');
		}
		if (!empty($filters['user_id']))
		{
			$where[] = 'p.user_id = ' . (int) $filters['user_id'];
		}
		if (!empty($filters['promotion_type']))
		{
			$where[] = "p.promotion_type = '" . $this->db->sql_escape($filters['promotion_type']) . "'";
		}

		return implode(' AND ', $where);
	}

	private function get_financial_payment_logs($filters, $limit = 50)
	{
		$sql = 'SELECT l.*, p.promotion_type, p.promotion_status, p.user_id, a.ad_title, u.username, u.user_colour
			FROM ' . $this->table_payment_logs . ' l
			LEFT JOIN ' . $this->table_promotions . ' p ON p.promotion_id = l.promotion_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			WHERE ' . $this->get_financial_where($filters) . '
			ORDER BY l.payment_created DESC, l.payment_log_id DESC';

		$result = $limit > 0 ? $this->db->sql_query_limit($sql, (int) $limit) : $this->db->sql_query($sql);
		$logs = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PAYMENT_CREATED_DISPLAY'] = !empty($row['payment_created']) ? $this->user->format_date((int) $row['payment_created']) : '';
			$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper($row['payment_provider']) : '-';
			$row['PAYMENT_AMOUNT_DISPLAY'] = $this->format_package_price((int) $row['payment_amount_cents'], $row['payment_currency']);
			$row['PAYMENT_VERIFICATION_STATUS_LANG'] = $this->get_payment_verification_status_lang($row['payment_verification_status']);
			$row['PAYMENT_VALIDATION_STATUS_LANG'] = $this->get_payment_validation_status_lang($row['payment_validation_status']);
			$row['PROMOTION_TYPE_LANG'] = !empty($row['promotion_type']) ? $this->get_promotion_type_lang($row['promotion_type']) : '-';
			$row['PROMOTION_STATUS_LANG'] = isset($row['promotion_status']) ? $this->get_promotion_status_lang((int) $row['promotion_status']) : '-';
			$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? $row['payment_reference'] : '-';
			$row['PAYMENT_TRANSACTION_DISPLAY'] = !empty($row['payment_transaction_id']) ? $row['payment_transaction_id'] : '-';
			$logs[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $logs;
	}

	private function build_financial_summary($logs)
	{
		$totals = [];
		$featured_totals = [];
		$boosted_totals = [];
		$by_type = [];
		$by_month = [];
		$confirmed = 0;
		$invalid = 0;
		$pending = 0;

		foreach ($logs as $row)
		{
			$is_confirmed = $this->is_financial_payment_confirmed($row);
			$is_invalid = $this->is_financial_payment_invalid($row);
			$is_pending = $this->is_financial_payment_pending($row);

			if ($is_confirmed)
			{
				$confirmed++;
				$currency = !empty($row['payment_currency']) ? $row['payment_currency'] : 'BRL';
				$amount = (int) $row['payment_amount_cents'];
				$type = !empty($row['promotion_type']) ? $row['promotion_type'] : '';
				$month = !empty($row['payment_created']) ? date('Y-m', (int) $row['payment_created']) : $this->language->lang('MARKETPLACE_UNKNOWN');

				$this->add_financial_amount($totals, $currency, $amount);
				$this->add_financial_amount($by_type[$type], $currency, $amount);
				$this->add_financial_amount($by_month[$month], $currency, $amount);
				if ($type === 'featured')
				{
					$this->add_financial_amount($featured_totals, $currency, $amount);
				}
				if ($type === 'boosted')
				{
					$this->add_financial_amount($boosted_totals, $currency, $amount);
				}
			}
			else if ($is_invalid)
			{
				$invalid++;
			}
			else if ($is_pending)
			{
				$pending++;
			}
		}

		ksort($by_month);
		$monthly_rows = [];
		foreach ($by_month as $month => $amounts)
		{
			$monthly_rows[] = [
				'MONTH' => $month,
				'REVENUE_DISPLAY' => $this->format_financial_amounts($amounts),
			];
		}

		$type_rows = [];
		foreach (['featured', 'boosted', 'renewal'] as $type)
		{
			$type_rows[] = [
				'TYPE' => $type,
				'TYPE_LANG' => $this->get_promotion_type_lang($type),
				'REVENUE_DISPLAY' => isset($by_type[$type]) ? $this->format_financial_amounts($by_type[$type]) : $this->format_financial_amounts([]),
			];
		}

		return [
			'FINANCIAL_TOTAL_REVENUE' => $this->format_financial_amounts($totals),
			'FINANCIAL_FEATURED_REVENUE' => $this->format_financial_amounts($featured_totals),
			'FINANCIAL_BOOSTED_REVENUE' => $this->format_financial_amounts($boosted_totals),
			'FINANCIAL_CONFIRMED_TRANSACTIONS' => $confirmed,
			'FINANCIAL_INVALID_TRANSACTIONS' => $invalid,
			'FINANCIAL_PENDING_TRANSACTIONS' => $pending,
			'FINANCIAL_MONTHLY_REVENUE' => $monthly_rows,
			'FINANCIAL_TYPE_REVENUE' => $type_rows,
		];
	}

	private function add_financial_amount(&$bucket, $currency, $amount_cents)
	{
		if (!is_array($bucket))
		{
			$bucket = [];
		}
		if (!isset($bucket[$currency]))
		{
			$bucket[$currency] = 0;
		}
		$bucket[$currency] += (int) $amount_cents;
	}

	private function format_financial_amounts($amounts)
	{
		if (empty($amounts))
		{
			$currency = isset($this->config['marketplace_paypal_currency']) && $this->config['marketplace_paypal_currency'] !== '' ? $this->config['marketplace_paypal_currency'] : 'BRL';
			return trim((string) $currency . ' ' . number_format(0, 2, ',', '.'));
		}

		$parts = [];
		ksort($amounts);
		foreach ($amounts as $currency => $amount_cents)
		{
			$parts[] = trim((string) $currency . ' ' . number_format(((int) $amount_cents) / 100, 2, ',', '.'));
		}
		return implode(' / ', $parts);
	}

	private function is_financial_payment_confirmed($row)
	{
		return strtoupper(trim((string) $row['payment_validation_status'])) === 'OK';
	}

	private function is_financial_payment_invalid($row)
	{
		$verification = strtolower(trim((string) $row['payment_verification_status']));
		$status = strtolower(trim((string) $row['payment_status']));
		$validation = strtoupper(trim((string) $row['payment_validation_status']));
		$bad_statuses = ['denied', 'failed', 'refunded', 'reversed', 'voided', 'expired'];

		return $verification === 'invalid' || in_array($status, $bad_statuses, true) || ($validation !== '' && !in_array($validation, ['OK', 'ALREADY_APPROVED', 'IGNORED_STATUS'], true));
	}

	private function is_financial_payment_pending($row)
	{
		$status = strtolower(trim((string) $row['payment_status']));
		return in_array($status, ['pending', 'in-progress', 'processed'], true) || (isset($row['promotion_status']) && (int) $row['promotion_status'] === 3 && !$this->is_financial_payment_confirmed($row) && !$this->is_financial_payment_invalid($row));
	}

	private function get_financial_promotion_type_options($selected)
	{
		$options = [[
			'VALUE' => '',
			'LABEL' => $this->language->lang('MARKETPLACE_ALL_PROMOTION_TYPES'),
			'S_SELECTED' => $selected === '',
		]];
		foreach (['featured', 'boosted', 'renewal'] as $type)
		{
			$options[] = [
				'VALUE' => $type,
				'LABEL' => $this->get_promotion_type_lang($type),
				'S_SELECTED' => $selected === $type,
			];
		}
		return $options;
	}

	private function build_financial_report_url($filters)
	{
		$url = $this->u_action;
		foreach (['start_date', 'end_date', 'user_id', 'promotion_type'] as $key)
		{
			if ($filters[$key] !== '' && $filters[$key] !== 0)
			{
				$url .= '&amp;' . $key . '=' . urlencode((string) $filters[$key]);
			}
		}
		return $url;
	}

	private function export_financial_report_csv($filters)
	{
		$logs = $this->get_financial_payment_logs($filters, 0);
		$filename = 'marketplace_financial_report_' . date('Y-m-d_H-i-s') . '.csv';

		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Pragma: no-cache');
		header('Expires: 0');

		$output = fopen('php://output', 'w');
		fputcsv($output, [
			'payment_log_id',
			'payment_created',
			'user_id',
			'username',
			'ad_title',
			'promotion_type',
			'promotion_status',
			'payment_provider',
			'payment_reference',
			'payment_transaction_id',
			'payment_status',
			'payment_verification_status',
			'payment_validation_status',
			'payment_amount',
			'payment_currency',
			'payment_receiver',
		]);

		foreach ($logs as $row)
		{
			fputcsv($output, [
				(int) $row['payment_log_id'],
				!empty($row['payment_created']) ? date('Y-m-d H:i:s', (int) $row['payment_created']) : '',
				isset($row['user_id']) ? (int) $row['user_id'] : 0,
				isset($row['username']) ? $row['username'] : '',
				isset($row['ad_title']) ? $row['ad_title'] : '',
				isset($row['promotion_type']) ? $row['promotion_type'] : '',
				isset($row['promotion_status']) ? (int) $row['promotion_status'] : '',
				isset($row['payment_provider']) ? $row['payment_provider'] : '',
				isset($row['payment_reference']) ? $row['payment_reference'] : '',
				isset($row['payment_transaction_id']) ? $row['payment_transaction_id'] : '',
				isset($row['payment_status']) ? $row['payment_status'] : '',
				isset($row['payment_verification_status']) ? $row['payment_verification_status'] : '',
				isset($row['payment_validation_status']) ? $row['payment_validation_status'] : '',
				number_format(((int) $row['payment_amount_cents']) / 100, 2, '.', ''),
				isset($row['payment_currency']) ? $row['payment_currency'] : '',
				isset($row['payment_receiver']) ? $row['payment_receiver'] : '',
			]);
		}
		fclose($output);
		\garbage_collection();
		\exit_handler();
	}


	/**
	 * Display payment/IPN administration with filters, pagination, search and CSV export.
	 */
	public function display_payments()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');
		\add_form_key('mundophpbb_marketplace_acp_payments');

		$filters = $this->get_payment_admin_filters();
		$action = $this->request->variable('action', '');
		if ($action === 'export_csv')
		{
			$this->export_payments_csv($filters);
		}
		if ($action === 'revalidate_payment')
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_payments'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}
			$this->revalidate_payment_log($this->request->variable('payment_log_id', 0));
		}

		$start = $this->request->variable('start', 0);
		$limit = max(10, (int) $this->config['marketplace_items_per_page']);
		$where = $this->get_payment_admin_where($filters);

		$sql = 'SELECT l.*, p.promotion_type, p.promotion_status, p.user_id, a.ad_title, u.username, u.user_colour
			FROM ' . $this->table_payment_logs . ' l
			LEFT JOIN ' . $this->table_promotions . ' p ON p.promotion_id = l.promotion_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			WHERE ' . $where . '
			ORDER BY l.payment_created DESC, l.payment_log_id DESC';
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$payments = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$payments[] = $this->prepare_payment_log_row($row);
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(*) AS total
			FROM ' . $this->table_payment_logs . ' l
			LEFT JOIN ' . $this->table_promotions . ' p ON p.promotion_id = l.promotion_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			WHERE ' . $where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$pagination_url = $this->build_admin_filter_url($this->u_action, $filters);
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total, $limit, $start);

		$this->template->assign_vars([
			'U_ACTION' => $this->u_action,
			'U_EXPORT_CSV' => $pagination_url . '&amp;action=export_csv',
			'PAYMENTS' => $payments,
			'TOTAL_PAYMENTS' => $total,
			'FILTER_Q' => $filters['q'],
			'FILTER_STATUS' => $filters['status'],
			'FILTER_PROVIDER' => $filters['provider'],
			'FILTER_TRANSACTION' => $filters['transaction'],
			'FILTER_REFERENCE' => $filters['reference'],
			'FILTER_START_DATE' => $filters['start_date'],
			'FILTER_END_DATE' => $filters['end_date'],
			'FILTER_USER_ID' => $filters['user_id'] > 0 ? $filters['user_id'] : '',
		]);
	}

	/**
	 * Display promotion administration with filters, pagination, search and CSV export.
	 */
	public function display_promotions()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');
		\add_form_key('mundophpbb_marketplace_acp_promotions');

		$action = $this->request->variable('action', '');
		$promotion_id = $this->request->variable('promotion_id', 0);
		if ($action === 'export_csv')
		{
			$this->export_promotions_csv($this->get_promotion_admin_filters());
		}
		if ($promotion_id && in_array($action, ['approve_promotion', 'reject_promotion'], true))
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_acp_promotions'))
			{
				\trigger_error($this->language->lang('FORM_INVALID') . \adm_back_link($this->u_action), E_USER_WARNING);
			}
			$this->handle_promotion_action($action, $promotion_id);
		}

		$filters = $this->get_promotion_admin_filters();
		$start = $this->request->variable('start', 0);
		$limit = max(10, (int) $this->config['marketplace_items_per_page']);
		$where = $this->get_promotion_admin_where($filters);

		$sql = 'SELECT p.*, a.ad_title, u.username, u.user_colour, pp.package_title
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			LEFT JOIN ' . $this->table_promotion_packages . ' pp ON pp.package_id = p.package_id
			WHERE ' . $where . '
			ORDER BY p.promotion_requested DESC, p.promotion_id DESC';
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$promotions = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PROMOTION_TYPE_LANG'] = $this->get_promotion_type_lang($row['promotion_type']);
			$row['PROMOTION_STATUS_LANG'] = $this->get_promotion_status_lang((int) $row['promotion_status']);
			$row['PROMOTION_REQUESTED_DISPLAY'] = !empty($row['promotion_requested']) ? $this->user->format_date((int) $row['promotion_requested']) : '';
			$row['PROMOTION_DECIDED_DISPLAY'] = !empty($row['promotion_decided']) ? $this->user->format_date((int) $row['promotion_decided']) : '';
			$row['PROMOTION_AMOUNT_DISPLAY'] = $this->format_package_price((int) $row['promotion_amount_cents'], !empty($row['promotion_currency']) ? $row['promotion_currency'] : (isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL'));
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$promotions[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(*) AS total
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			LEFT JOIN ' . $this->table_promotion_packages . ' pp ON pp.package_id = p.package_id
			WHERE ' . $where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$pagination_url = $this->build_admin_filter_url($this->u_action, $filters);
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total, $limit, $start);

		$this->template->assign_vars([
			'U_ACTION' => $this->u_action,
			'U_EXPORT_CSV' => $pagination_url . '&amp;action=export_csv',
			'PROMOTIONS' => $promotions,
			'TOTAL_PROMOTIONS' => $total,
			'FILTER_Q' => $filters['q'],
			'FILTER_STATUS' => $filters['status'],
			'FILTER_PROMOTION_TYPE' => $filters['promotion_type'],
			'FILTER_USER_ID' => $filters['user_id'] > 0 ? $filters['user_id'] : '',
			'PROMOTION_TYPE_OPTIONS' => $this->get_financial_promotion_type_options($filters['promotion_type']),
		]);
	}

	/**
	 * Display Marketplace administrative logs with filters, pagination, search and CSV export.
	 */
	public function display_admin_logs()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');

		$filters = [
			'q' => trim($this->request->variable('q', '', true)),
			'action_type' => trim($this->request->variable('action_type', '', true)),
			'user_id' => max(0, $this->request->variable('user_id', 0)),
		];
		if ($this->request->variable('action', '') === 'export_csv')
		{
			$this->export_admin_logs_csv($filters);
		}

		$start = $this->request->variable('start', 0);
		$limit = max(10, (int) $this->config['marketplace_items_per_page']);
		$where = $this->get_admin_logs_where($filters);

		$sql = 'SELECT l.*, a.ad_title, au.username AS admin_username, tu.username AS target_username
			FROM ' . $this->table_moderation_logs . ' l
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = l.ad_id
			LEFT JOIN ' . USERS_TABLE . ' au ON au.user_id = l.admin_user_id
			LEFT JOIN ' . USERS_TABLE . ' tu ON tu.user_id = l.target_user_id
			WHERE ' . $where . '
			ORDER BY l.log_time DESC, l.log_id DESC';
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$logs = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['LOG_TIME_DISPLAY'] = !empty($row['log_time']) ? $this->user->format_date((int) $row['log_time']) : '';
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$logs[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(*) AS total
			FROM ' . $this->table_moderation_logs . ' l
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = l.ad_id
			LEFT JOIN ' . USERS_TABLE . ' au ON au.user_id = l.admin_user_id
			LEFT JOIN ' . USERS_TABLE . ' tu ON tu.user_id = l.target_user_id
			WHERE ' . $where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$pagination_url = $this->build_admin_filter_url($this->u_action, $filters);
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total, $limit, $start);

		$this->template->assign_vars([
			'U_ACTION' => $this->u_action,
			'U_EXPORT_CSV' => $pagination_url . '&amp;action=export_csv',
			'ADMIN_LOGS' => $logs,
			'TOTAL_ADMIN_LOGS' => $total,
			'FILTER_Q' => $filters['q'],
			'FILTER_ACTION_TYPE' => $filters['action_type'],
			'FILTER_USER_ID' => $filters['user_id'] > 0 ? $filters['user_id'] : '',
		]);
	}

	private function get_payment_admin_filters()
	{
		$status = $this->request->variable('status', '');
		if (!in_array($status, ['', 'confirmed', 'invalid', 'pending'], true))
		{
			$status = '';
		}

		$provider = strtolower(trim($this->request->variable('provider', '', true)));
		if (!preg_match('/^[a-z0-9_-]{0,50}$/', $provider))
		{
			$provider = '';
		}

		return [
			'q' => trim($this->request->variable('q', '', true)),
			'status' => $status,
			'provider' => $provider,
			'transaction' => trim($this->request->variable('transaction', '', true)),
			'reference' => trim($this->request->variable('reference', '', true)),
			'start_date' => $this->normalise_financial_date($this->request->variable('start_date', '')),
			'end_date' => $this->normalise_financial_date($this->request->variable('end_date', '')),
			'user_id' => max(0, $this->request->variable('user_id', 0)),
		];
	}

	private function get_payment_admin_where($filters)
	{
		$where = ['1 = 1'];
		if ($filters['q'] !== '')
		{
			$q = $this->db->sql_escape($filters['q']);
			$where[] = "(l.payment_reference LIKE '%$q%' OR l.payment_transaction_id LIKE '%$q%' OR l.payment_receiver LIKE '%$q%' OR a.ad_title LIKE '%$q%' OR u.username LIKE '%$q%' OR l.payment_status LIKE '%$q%' OR l.payment_validation_status LIKE '%$q%')";
		}
		if (!empty($filters['provider']))
		{
			$where[] = "l.payment_provider = '" . $this->db->sql_escape($filters['provider']) . "'";
		}
		if (!empty($filters['transaction']))
		{
			$where[] = "l.payment_transaction_id LIKE '%" . $this->db->sql_escape($filters['transaction']) . "%'";
		}
		if (!empty($filters['reference']))
		{
			$where[] = "l.payment_reference LIKE '%" . $this->db->sql_escape($filters['reference']) . "%'";
		}
		if (!empty($filters['start_date']))
		{
			$where[] = 'l.payment_created >= ' . (int) strtotime($filters['start_date'] . ' 00:00:00');
		}
		if (!empty($filters['end_date']))
		{
			$where[] = 'l.payment_created <= ' . (int) strtotime($filters['end_date'] . ' 23:59:59');
		}
		if (!empty($filters['user_id']))
		{
			$where[] = 'p.user_id = ' . (int) $filters['user_id'];
		}
		if ($filters['status'] === 'confirmed')
		{
			$where[] = "l.payment_validation_status IN ('OK', 'ok', 'confirmed')";
		}
		else if ($filters['status'] === 'invalid')
		{
			$where[] = "(l.payment_validation_status <> '' AND l.payment_validation_status NOT IN ('OK', 'ok', 'confirmed') AND l.payment_validation_status NOT LIKE '%PENDING%')";
		}
		else if ($filters['status'] === 'pending')
		{
			$where[] = "(l.payment_validation_status = '' OR l.payment_validation_status LIKE '%PENDING%' OR l.payment_status LIKE '%Pending%')";
		}
		return implode(' AND ', $where);
	}

	private function get_promotion_admin_filters()
	{
		$promotion_type = $this->request->variable('promotion_type', '');
		if (!in_array($promotion_type, ['', 'featured', 'boosted', 'renewal', 'boost_bundle', 'ad_quota', 'seller_plan'], true))
		{
			$promotion_type = '';
		}
		$status = $this->request->variable('status', -1);
		return [
			'q' => trim($this->request->variable('q', '', true)),
			'status' => ($status >= 0) ? (int) $status : -1,
			'promotion_type' => $promotion_type,
			'user_id' => max(0, $this->request->variable('user_id', 0)),
		];
	}

	private function get_promotion_admin_where($filters)
	{
		$where = ['1 = 1'];
		if ($filters['q'] !== '')
		{
			$q = $this->db->sql_escape($filters['q']);
			$where[] = "(p.payment_reference LIKE '%$q%' OR a.ad_title LIKE '%$q%' OR u.username LIKE '%$q%' OR pp.package_title LIKE '%$q%')";
		}
		if ((int) $filters['status'] >= 0)
		{
			$where[] = 'p.promotion_status = ' . (int) $filters['status'];
		}
		if ($filters['promotion_type'] !== '')
		{
			$where[] = "p.promotion_type = '" . $this->db->sql_escape($filters['promotion_type']) . "'";
		}
		if (!empty($filters['user_id']))
		{
			$where[] = 'p.user_id = ' . (int) $filters['user_id'];
		}
		return implode(' AND ', $where);
	}

	private function get_admin_logs_where($filters)
	{
		$where = ['1 = 1'];
		if ($filters['q'] !== '')
		{
			$q = $this->db->sql_escape($filters['q']);
			$where[] = "(l.log_action LIKE '%$q%' OR l.log_note LIKE '%$q%' OR a.ad_title LIKE '%$q%' OR au.username LIKE '%$q%' OR tu.username LIKE '%$q%')";
		}
		if ($filters['action_type'] !== '')
		{
			$where[] = "l.log_action = '" . $this->db->sql_escape($filters['action_type']) . "'";
		}
		if (!empty($filters['user_id']))
		{
			$where[] = '(l.admin_user_id = ' . (int) $filters['user_id'] . ' OR l.target_user_id = ' . (int) $filters['user_id'] . ')';
		}
		return implode(' AND ', $where);
	}

	private function prepare_payment_log_row($row)
	{
		$row['PAYMENT_CREATED_DISPLAY'] = !empty($row['payment_created']) ? $this->user->format_date((int) $row['payment_created']) : '';
		$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper($row['payment_provider']) : '-';
		$row['PAYMENT_AMOUNT_DISPLAY'] = $this->format_package_price((int) $row['payment_amount_cents'], !empty($row['payment_currency']) ? $row['payment_currency'] : (isset($this->config['marketplace_paypal_currency']) ? (string) $this->config['marketplace_paypal_currency'] : 'BRL'));
		$row['PAYMENT_VERIFICATION_STATUS_LANG'] = $this->get_payment_verification_status_lang(isset($row['payment_verification_status']) ? $row['payment_verification_status'] : '');
		$row['PAYMENT_VALIDATION_STATUS_LANG'] = $this->get_payment_validation_status_lang(isset($row['payment_validation_status']) ? $row['payment_validation_status'] : '');
		$row['PROMOTION_TYPE_LANG'] = !empty($row['promotion_type']) ? $this->get_promotion_type_lang($row['promotion_type']) : '-';
		$row['PROMOTION_STATUS_LANG'] = isset($row['promotion_status']) ? $this->get_promotion_status_lang((int) $row['promotion_status']) : '-';
		$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? $row['payment_reference'] : '-';
		$row['PAYMENT_TRANSACTION_DISPLAY'] = !empty($row['payment_transaction_id']) ? $row['payment_transaction_id'] : '-';
		$row['PAYMENT_RECEIVER_DISPLAY'] = !empty($row['payment_receiver']) ? $row['payment_receiver'] : '-';
		$provider = strtolower((string) $row['payment_provider']);
		$row['S_CAN_REVALIDATE'] = !empty($row['payment_log_id']) && in_array($provider, ['paypal', 'pix'], true);
		$row['PAYMENT_RECEIVER_DISPLAY'] = $provider === 'pix' ? $this->mask_sensitive_gateway_value($row['PAYMENT_RECEIVER_DISPLAY'], isset($this->config['marketplace_gateway_pix_key_type']) ? (string) $this->config['marketplace_gateway_pix_key_type'] : 'cpf') : $row['PAYMENT_RECEIVER_DISPLAY'];
		return $row;
	}

	private function build_admin_filter_url($base, array $filters)
	{
		$url = $base;
		foreach ($filters as $key => $value)
		{
			if ($value !== '' && $value !== -1 && !($value === 0 && !in_array($key, ['status'], true)))
			{
				$url .= '&amp;' . $key . '=' . urlencode((string) $value);
			}
		}
		return $url;
	}

	private function export_csv_response($filename, array $headers, array $rows)
	{
		if (ob_get_level())
		{
			@ob_end_clean();
		}
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		$out = fopen('php://output', 'w');
		fputcsv($out, $headers, ';');
		foreach ($rows as $row)
		{
			fputcsv($out, $row, ';');
		}
		fclose($out);
		exit;
	}

	private function export_payments_csv($filters)
	{
		$sql = 'SELECT l.*, p.promotion_type, p.promotion_status, p.user_id, a.ad_title, u.username
			FROM ' . $this->table_payment_logs . ' l
			LEFT JOIN ' . $this->table_promotions . ' p ON p.promotion_id = l.promotion_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			WHERE ' . $this->get_payment_admin_where($filters) . '
			ORDER BY l.payment_created DESC, l.payment_log_id DESC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = [(int) $row['payment_log_id'], $row['payment_provider'], $row['username'], $row['ad_title'], $row['promotion_type'], $row['payment_status'], $row['payment_verification_status'], $row['payment_validation_status'], $row['payment_reference'], $row['payment_transaction_id'], $row['payment_receiver'], $row['payment_currency'], (int) $row['payment_amount_cents'], !empty($row['payment_created']) ? date('Y-m-d H:i:s', (int) $row['payment_created']) : ''];
		}
		$this->db->sql_freeresult($result);
		$this->export_csv_response('marketplace-payments.csv', ['id', 'provider', 'user', 'ad', 'promotion_type', 'payment_status', 'verification_status', 'validation_status', 'reference', 'transaction', 'receiver', 'currency', 'amount_cents', 'created'], $rows);
	}

	private function revalidate_payment_log($payment_log_id)
	{
		$payment_log_id = (int) $payment_log_id;
		if ($payment_log_id <= 0)
		{
			\trigger_error($this->language->lang('MARKETPLACE_PAYMENT_LOG_NOT_FOUND') . \adm_back_link($this->u_action), E_USER_WARNING);
		}

		$sql = 'SELECT l.*, p.promotion_id, p.promotion_type, p.promotion_status, p.promotion_days, p.promotion_amount_cents, p.promotion_currency, p.user_id, p.ad_id, a.ad_title, a.ad_status
			FROM ' . $this->table_payment_logs . ' l
			LEFT JOIN ' . $this->table_promotions . ' p ON p.promotion_id = l.promotion_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE l.payment_log_id = ' . $payment_log_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			\trigger_error($this->language->lang('MARKETPLACE_PAYMENT_LOG_NOT_FOUND') . \adm_back_link($this->u_action), E_USER_WARNING);
		}
		$provider = strtolower((string) $row['payment_provider']);
		if (!in_array($provider, ['paypal', 'pix'], true))
		{
			\trigger_error($this->language->lang('MARKETPLACE_PAYMENT_GATEWAY_FUTURE_ONLY') . \adm_back_link($this->u_action), E_USER_WARNING);
		}

		$ipn_data = json_decode((string) $row['payment_raw'], true);
		if (!is_array($ipn_data))
		{
			$ipn_data = [];
		}

		$validation_status = ($provider === 'pix') ? $this->validate_pix_payment_log_manually($row) : $this->validate_paypal_payment_log_locally($row, $ipn_data);
		$this->db->sql_query('UPDATE ' . $this->table_payment_logs . ' SET ' . $this->db->sql_build_array('UPDATE', [
			'payment_verification_status' => 'manual',
			'payment_validation_status' => $validation_status,
		]) . ' WHERE payment_log_id = ' . $payment_log_id);

		if ($validation_status === 'OK' && (int) $row['promotion_id'] > 0 && (int) $row['promotion_status'] === 3)
		{
			$this->apply_paid_promotion_from_acp($row);
			$this->update_promotion_status((int) $row['promotion_id'], 1);
			$this->add_notification((int) $row['user_id'], (int) $row['ad_id'], 'payment_confirmed', $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_CONFIRMED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_CONFIRMED_MESSAGE', $row['ad_title']));
		}

		\trigger_error($this->language->lang('MARKETPLACE_PAYMENT_REVALIDATED', $validation_status) . \adm_back_link($this->u_action));
	}


	private function validate_pix_payment_log_manually(array $row)
	{
		if (empty($row['promotion_id']))
		{
			return 'PROMOTION_NOT_FOUND';
		}
		if ((int) $row['promotion_status'] === 1)
		{
			return 'ALREADY_APPROVED';
		}
		if ((int) $row['promotion_status'] !== 3)
		{
			return 'PROMOTION_NOT_AWAITING_PAYMENT';
		}
		if ((int) $row['ad_status'] !== 1)
		{
			return 'AD_NOT_ACTIVE';
		}
		if ((int) $row['payment_amount_cents'] !== (int) $row['promotion_amount_cents'])
		{
			return 'PAYMENT_MISMATCH';
		}
		return 'OK';
	}

	private function validate_paypal_payment_log_locally(array $row, array $ipn_data)
	{
		$payment_status = strtolower(trim((string) (isset($ipn_data['payment_status']) ? $ipn_data['payment_status'] : $row['payment_status'])));
		if ($payment_status !== 'completed')
		{
			return 'IGNORED_STATUS';
		}

		if (empty($row['promotion_id']))
		{
			return 'PROMOTION_NOT_FOUND';
		}
		if ((int) $row['promotion_status'] === 1)
		{
			return 'ALREADY_APPROVED';
		}
		if ((int) $row['promotion_status'] !== 3)
		{
			return 'PROMOTION_NOT_AWAITING_PAYMENT';
		}
		if ((int) $row['ad_status'] !== 1)
		{
			return 'AD_NOT_ACTIVE';
		}

		$receiver_email = isset($ipn_data['receiver_email']) ? $this->sanitize_payment_email($ipn_data['receiver_email']) : $this->sanitize_payment_email(isset($row['payment_receiver']) ? $row['payment_receiver'] : '');
		$business = isset($ipn_data['business']) ? $this->sanitize_payment_email($ipn_data['business']) : '';
		$expected_business = $this->get_acp_paypal_business_account();
		if ($expected_business === '' || ($receiver_email !== $expected_business && $business !== $expected_business))
		{
			return 'PAYMENT_MISMATCH';
		}

		$gross = isset($ipn_data['mc_gross']) ? (float) str_replace(',', '.', (string) $ipn_data['mc_gross']) : ((int) $row['payment_amount_cents'] / 100);
		$expected_gross = ((int) $row['promotion_amount_cents']) / 100;
		if (abs($gross - $expected_gross) > 0.009)
		{
			return 'PAYMENT_MISMATCH';
		}

		$currency = isset($ipn_data['mc_currency']) ? strtoupper(preg_replace('/[^A-Z]/', '', (string) $ipn_data['mc_currency'])) : strtoupper(preg_replace('/[^A-Z]/', '', (string) $row['payment_currency']));
		$expected_currency = !empty($row['promotion_currency']) ? strtoupper(preg_replace('/[^A-Z]/', '', (string) $row['promotion_currency'])) : (isset($this->config['marketplace_paypal_currency']) ? strtoupper(preg_replace('/[^A-Z]/', '', (string) $this->config['marketplace_paypal_currency'])) : 'BRL');
		if ($currency === '' || $currency !== $expected_currency)
		{
			return 'PAYMENT_MISMATCH';
		}

		return 'OK';
	}

	private function apply_paid_promotion_from_acp(array $promotion)
	{
		$now = time();
		$days = max(1, (int) $promotion['promotion_days']);
		$sql_ary = [];
		if ($promotion['promotion_type'] === 'featured')
		{
			$sql_ary = ['ad_featured_until' => $now + ($days * 86400), 'ad_featured_by' => (int) $this->user->data['user_id'], 'ad_updated' => $now];
		}
		else if ($promotion['promotion_type'] === 'boosted')
		{
			$sql_ary = ['ad_boosted_until' => $now + ($days * 86400), 'ad_boosted_by' => (int) $this->user->data['user_id'], 'ad_updated' => $now];
		}
		if (!empty($sql_ary))
		{
			$sql_ary = $this->filter_existing_ad_columns($sql_ary);
			$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . (int) $promotion['ad_id']);
		}
	}

	private function get_acp_paypal_business_account()
	{
		if (!empty($this->config['marketplace_paypal_sandbox']))
		{
			return $this->sanitize_payment_email(isset($this->config['marketplace_paypal_sandbox_business']) ? (string) $this->config['marketplace_paypal_sandbox_business'] : '');
		}
		return $this->sanitize_payment_email(isset($this->config['marketplace_paypal_business']) ? (string) $this->config['marketplace_paypal_business'] : '');
	}

	private function sanitize_payment_email($email)
	{
		$email = strtolower(trim((string) $email));
		return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
	}

	private function export_promotions_csv($filters)
	{
		$sql = 'SELECT p.*, a.ad_title, u.username, pp.package_title
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			LEFT JOIN ' . $this->table_promotion_packages . ' pp ON pp.package_id = p.package_id
			WHERE ' . $this->get_promotion_admin_where($filters) . '
			ORDER BY p.promotion_requested DESC, p.promotion_id DESC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = [(int) $row['promotion_id'], $row['username'], $row['ad_title'], $row['package_title'], $row['promotion_type'], (int) $row['promotion_status'], $row['payment_reference'], $row['promotion_currency'], (int) $row['promotion_amount_cents'], !empty($row['promotion_requested']) ? date('Y-m-d H:i:s', (int) $row['promotion_requested']) : ''];
		}
		$this->db->sql_freeresult($result);
		$this->export_csv_response('marketplace-promotions.csv', ['id', 'user', 'ad', 'package', 'type', 'status', 'reference', 'currency', 'amount_cents', 'requested'], $rows);
	}

	private function export_admin_logs_csv($filters)
	{
		$sql = 'SELECT l.*, a.ad_title, au.username AS admin_username, tu.username AS target_username
			FROM ' . $this->table_moderation_logs . ' l
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = l.ad_id
			LEFT JOIN ' . USERS_TABLE . ' au ON au.user_id = l.admin_user_id
			LEFT JOIN ' . USERS_TABLE . ' tu ON tu.user_id = l.target_user_id
			WHERE ' . $this->get_admin_logs_where($filters) . '
			ORDER BY l.log_time DESC, l.log_id DESC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = [(int) $row['log_id'], $row['log_action'], $row['admin_username'], $row['target_username'], $row['ad_title'], $row['log_note'], !empty($row['log_time']) ? date('Y-m-d H:i:s', (int) $row['log_time']) : ''];
		}
		$this->db->sql_freeresult($result);
		$this->export_csv_response('marketplace-admin-logs.csv', ['id', 'action', 'admin', 'target_user', 'ad', 'note', 'created'], $rows);
	}


	private function export_ads_csv()
	{
		$filter_status = $this->request->variable('status', -1);
		$filter_q = trim($this->request->variable('q', '', true));
		$where = [];
		if ($filter_status >= 0)
		{
			$where[] = 'a.ad_status = ' . (int) $filter_status;
		}
		if ($filter_q !== '')
		{
			$q = $this->db->sql_escape($filter_q);
			$where[] = "(a.ad_title LIKE '%$q%' OR a.ad_desc LIKE '%$q%' OR u.username LIKE '%$q%' OR c.cat_name LIKE '%$q%' OR CAST(a.ad_id AS CHAR) = '$q')";
		}
		$sql_where = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
		$sql = 'SELECT a.*, u.username, c.cat_name
			FROM ' . $this->table_ads . ' a
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
			LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
			' . $sql_where . '
			ORDER BY a.ad_created DESC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = [(int) $row['ad_id'], $row['ad_title'], $row['username'], $this->translate_category_text($row['cat_name']), (int) $row['ad_status'], $row['ad_currency'], $row['ad_price'], !empty($row['ad_created']) ? date('Y-m-d H:i:s', (int) $row['ad_created']) : ''];
		}
		$this->db->sql_freeresult($result);
		$this->export_csv_response('marketplace-ads.csv', ['id', 'title', 'user', 'category', 'status', 'currency', 'price', 'created'], $rows);
	}

	private function export_reports_csv()
	{
		$filter_status = $this->request->variable('status', -1);
		$filter_q = trim($this->request->variable('q', '', true));
		$where_parts = [];
		if ($filter_status >= 0)
		{
			$where_parts[] = 'r.report_status = ' . (int) $filter_status;
		}
		if ($filter_q !== '')
		{
			$q = $this->db->sql_escape($filter_q);
			$where_parts[] = "(r.report_reason LIKE '%$q%' OR r.report_note LIKE '%$q%' OR a.ad_title LIKE '%$q%' OR u.username LIKE '%$q%' OR CAST(r.report_id AS CHAR) = '$q')";
		}
		$where = !empty($where_parts) ? 'WHERE ' . implode(' AND ', $where_parts) : '';
		$sql = 'SELECT r.*, a.ad_title, u.username
			FROM ' . $this->table_reports . ' r
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = r.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = r.reporter_id
			' . $where . '
			ORDER BY r.report_created DESC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = [(int) $row['report_id'], $row['report_type'], $row['ad_title'], $row['username'], (int) $row['report_status'], $row['report_reason'], $row['report_note'], !empty($row['report_created']) ? date('Y-m-d H:i:s', (int) $row['report_created']) : ''];
		}
		$this->db->sql_freeresult($result);
		$this->export_csv_response('marketplace-reports.csv', ['id', 'type', 'ad', 'reporter', 'status', 'reason', 'note', 'created'], $rows);
	}

	public function display_dashboard()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');

		$now = time();

		$stats = [
			'TOTAL_ADS'      => $this->count_ads(),
			'ACTIVE_ADS'     => $this->count_ads('ad_status = 1'),
			'PENDING_ADS'    => $this->count_ads('ad_status = 0'),
			'SOLD_ADS'       => $this->count_ads('ad_status = 2'),
			'EXPIRED_ADS'    => $this->count_ads('ad_status = 3'),
			'HIDDEN_ADS'     => $this->count_ads('ad_status = 4'),
			'PENDING_PROMOTIONS' => $this->count_promotions('promotion_status IN (0, 3)'),
			'ACTIVE_BOOSTED_ADS' => $this->column_exists($this->table_ads, 'ad_boosted_until') ? $this->count_ads('ad_boosted_until >= ' . (int) $now) : 0,
			'ACTIVE_FEATURED_ADS' => $this->column_exists($this->table_ads, 'ad_featured_until') ? $this->count_ads('ad_featured_until >= ' . (int) $now) : 0,
			'FEATURED_ADS'   => $this->column_exists($this->table_ads, 'ad_featured_until') ? $this->count_ads('ad_featured_until >= ' . (int) $now) : 0,
			'BOOSTED_ADS'    => $this->column_exists($this->table_ads, 'ad_boosted_until') ? $this->count_ads('ad_boosted_until >= ' . (int) $now) : 0,
			'CONFIRMED_PAYMENTS' => $this->count_payment_logs("payment_validation_status = 'OK'"),
			'INVALID_PAYMENTS' => $this->count_payment_logs("payment_verification_status = 'invalid' OR payment_status IN ('Denied', 'Failed', 'Refunded', 'Reversed', 'Voided', 'Expired')"),
			'IPN_ERROR_LOGS' => $this->count_payment_logs("payment_validation_status <> '' AND payment_validation_status NOT IN ('OK', 'ALREADY_APPROVED', 'IGNORED_STATUS')"),
			'OPEN_REPORTS'   => $this->count_reports('report_status = 0'),
			'TOTAL_REPORTS'  => $this->count_reports(),
			'TOTAL_IMAGES'   => $this->count_images(),
			'DISK_USAGE'     => $this->format_bytes($this->get_marketplace_disk_usage()),
		];

		$recent_ads = $this->get_recent_dashboard_ads(10);
		$recent_pending_ads = $this->get_recent_dashboard_ads(8, 'a.ad_status = 0');
		$recent_reports = $this->get_recent_dashboard_reports(10);
		$recent_promotions = $this->get_recent_dashboard_promotions(10);
		$recent_payment_logs = $this->get_recent_dashboard_payment_logs(10);
		$top_users = $this->get_dashboard_top_users(10);
		$top_categories = $this->get_dashboard_top_categories(10);

		$base_dashboard_url = $this->u_action;
		$u_ads = str_replace('mode=dashboard', 'mode=ads', $base_dashboard_url);
		$u_reports = str_replace('mode=dashboard', 'mode=reports', $base_dashboard_url);
		$u_categories = str_replace('mode=dashboard', 'mode=categories', $base_dashboard_url);
		$u_settings = str_replace('mode=dashboard', 'mode=settings', $base_dashboard_url);
		$u_notifications = str_replace('mode=dashboard', 'mode=notifications', $base_dashboard_url);
		$u_financial_reports = str_replace('mode=dashboard', 'mode=financial_reports', $base_dashboard_url);

		$this->template->assign_vars(array_merge($stats, [
			'U_ACTION'           => $this->u_action,
			'U_ACP_ADS'          => $u_ads,
			'U_ACP_PENDING_ADS'  => $u_ads . '&amp;status=0',
			'U_ACP_ACTIVE_ADS'   => $u_ads . '&amp;status=1',
			'U_ACP_EXPIRED_ADS'  => $u_ads . '&amp;status=3',
			'U_ACP_HIDDEN_ADS'   => $u_ads . '&amp;status=4',
			'U_ACP_REPORTS'      => $u_reports,
			'U_ACP_OPEN_REPORTS' => $u_reports . '&amp;status=0',
			'U_ACP_CATEGORIES'   => $u_categories,
			'U_ACP_SETTINGS'     => $u_settings,
			'U_ACP_NOTIFICATIONS' => $u_notifications,
			'U_ACP_FINANCIAL_REPORTS' => $u_financial_reports,
			'RECENT_ADS'         => $recent_ads,
			'RECENT_REPORTS'     => $recent_reports,
			'RECENT_PENDING_ADS' => $recent_pending_ads,
			'RECENT_PROMOTIONS'  => $recent_promotions,
			'RECENT_PAYMENT_LOGS' => $recent_payment_logs,
			'TOP_USERS'          => $top_users,
			'TOP_CATEGORIES'     => $top_categories,
		]));
	}

	private function get_recent_dashboard_reports($limit = 10)
	{
		$recent_reports = [];
		$sql = 'SELECT r.*, a.ad_title, u.username, u.user_colour
			FROM ' . $this->table_reports . ' r
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = r.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = r.reporter_id
			ORDER BY r.report_created DESC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['REPORT_CREATED_DISPLAY'] = !empty($row['report_created']) ? $this->user->format_date((int) $row['report_created']) : '';
			$row['REPORT_STATUS_LANG'] = ((int) $row['report_status'] === 0) ? $this->language->lang('MARKETPLACE_REPORT_OPEN') : $this->language->lang('MARKETPLACE_REPORT_CLOSED');
			$row['REPORT_TYPE_LANG'] = $this->get_report_type_lang(isset($row['report_type']) ? $row['report_type'] : 'ad');
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$recent_reports[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $recent_reports;
	}

	private function get_recent_dashboard_ads($limit = 10, $where = '')
	{
		$recent_ads = [];
		$sql = 'SELECT a.*, u.username, u.user_colour, c.cat_name
			FROM ' . $this->table_ads . ' a
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
			LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
			' . ($where !== '' ? 'WHERE ' . $where : '') . '
			ORDER BY a.ad_created DESC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['AD_CREATED_DISPLAY'] = !empty($row['ad_created']) ? $this->user->format_date((int) $row['ad_created']) : '';
			$row['AD_PRICE_DISPLAY'] = $this->format_acp_price($row);
			$row['AD_STATUS_LANG'] = $this->get_status_lang((int) $row['ad_status']);
			$row['cat_name'] = $this->translate_category_text(isset($row['cat_name']) ? $row['cat_name'] : '');
			$row['U_AD'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$recent_ads[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $recent_ads;
	}

	private function get_recent_dashboard_promotions($limit = 10)
	{
		$promotions = [];
		$sql = 'SELECT p.*, a.ad_title, a.ad_status, u.username, u.user_colour
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			ORDER BY p.promotion_requested DESC, p.promotion_id DESC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PROMOTION_TYPE_LANG'] = $this->get_promotion_type_lang($row['promotion_type']);
			$row['PROMOTION_STATUS_LANG'] = $this->get_promotion_status_lang((int) $row['promotion_status']);
			$row['PROMOTION_REQUESTED_DISPLAY'] = !empty($row['promotion_requested']) ? $this->user->format_date((int) $row['promotion_requested']) : '';
			$row['PROMOTION_PRICE_DISPLAY'] = $this->format_package_price((int) $row['promotion_amount_cents'], $row['promotion_currency']);
			$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper($row['payment_provider']) : '-';
			$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? $row['payment_reference'] : '-';
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$promotions[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $promotions;
	}

	private function get_recent_dashboard_payment_logs($limit = 10)
	{
		$logs = [];
		$sql = 'SELECT l.*, p.promotion_type, p.promotion_status, a.ad_title, u.username
			FROM ' . $this->table_payment_logs . ' l
			LEFT JOIN ' . $this->table_promotions . ' p ON p.promotion_id = l.promotion_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = p.user_id
			ORDER BY l.payment_created DESC, l.payment_log_id DESC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PAYMENT_CREATED_DISPLAY'] = !empty($row['payment_created']) ? $this->user->format_date((int) $row['payment_created']) : '';
			$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper($row['payment_provider']) : '-';
			$row['PAYMENT_AMOUNT_DISPLAY'] = $this->format_package_price((int) $row['payment_amount_cents'], $row['payment_currency']);
			$row['PAYMENT_VERIFICATION_STATUS_LANG'] = $this->get_payment_verification_status_lang($row['payment_verification_status']);
			$row['PAYMENT_VALIDATION_STATUS_LANG'] = $this->get_payment_validation_status_lang($row['payment_validation_status']);
			$row['PAYMENT_TRANSACTION_DISPLAY'] = !empty($row['payment_transaction_id']) ? $row['payment_transaction_id'] : '-';
			$logs[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $logs;
	}

	private function get_dashboard_top_users($limit = 10)
	{
		$users = [];
		$sql = 'SELECT a.user_id, COUNT(a.ad_id) AS total_ads, u.username, u.user_colour
			FROM ' . $this->table_ads . ' a
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
			GROUP BY a.user_id, u.username, u.user_colour
			ORDER BY total_ads DESC, u.username ASC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $users;
	}

	private function get_dashboard_top_categories($limit = 10)
	{
		$categories = [];
		$sql = 'SELECT c.cat_id, c.cat_name, COUNT(a.ad_id) AS total_ads
			FROM ' . $this->table_cats . ' c
			LEFT JOIN ' . $this->table_ads . ' a ON a.cat_id = c.cat_id
			GROUP BY c.cat_id, c.cat_name
			ORDER BY total_ads DESC, c.cat_order ASC, c.cat_name ASC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['cat_name'] = $this->translate_category_text(isset($row['cat_name']) ? $row['cat_name'] : '');
			$categories[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $categories;
	}

	public function manage_reports()
	{
		$this->language->add_lang(['acp', 'common'], 'mundophpbb/marketplace');
		\add_form_key('mundophpbb_marketplace_acp_reports');

		$action = $this->request->variable('action', '');
		$report_id = $this->request->variable('report_id', 0);
		if ($action === 'export_csv')
		{
			$this->export_reports_csv();
		}

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
		$filter_q = trim($this->request->variable('q', '', true));
		$where_parts = [];
		if ($filter_status >= 0)
		{
			$where_parts[] = 'r.report_status = ' . (int) $filter_status;
		}
		if ($filter_q !== '')
		{
			$q = $this->db->sql_escape($filter_q);
			$where_parts[] = "(r.report_reason LIKE '%$q%' OR r.report_note LIKE '%$q%' OR a.ad_title LIKE '%$q%' OR u.username LIKE '%$q%' OR CAST(r.report_id AS CHAR) = '$q')";
		}
		$where = !empty($where_parts) ? 'WHERE ' . implode(' AND ', $where_parts) : '';

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
			$row['REPORT_TYPE_LANG'] = $this->get_report_type_lang(isset($row['report_type']) ? $row['report_type'] : 'ad');
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$reports[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_reports . ' r ' . $where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$pagination_url = $this->u_action . '&amp;status=' . $filter_status . ($filter_q !== '' ? '&amp;q=' . urlencode($filter_q) : '');
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total, $limit, $start);

		$this->template->assign_vars([
			'U_ACTION'   => $this->u_action,
			'REPORTS'    => $reports,
			'reports'    => $reports,
			'S_FILTER'   => $filter_status,
			'FILTER_Q'   => $filter_q,
			'U_EXPORT_CSV' => $pagination_url . '&amp;action=export_csv',
			'TOTAL_REPORTS' => $total,
		]);
	}


	private function get_report_type_lang($type)
	{
		switch ((string) $type)
		{
			case 'seller':
				return $this->language->lang('MARKETPLACE_REPORT_TYPE_SELLER');
			case 'buyer':
				return $this->language->lang('MARKETPLACE_REPORT_TYPE_BUYER');
			case 'ad':
			default:
				return $this->language->lang('MARKETPLACE_REPORT_TYPE_AD');
		}
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

	private function count_promotions($where = '')
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_promotions . ($where !== '' ? ' WHERE ' . $where : '');
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		return $total;
	}

	private function count_payment_logs($where = '')
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_payment_logs . ($where !== '' ? ' WHERE ' . $where : '');
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



	private function get_user_id_by_username($username)
	{
		$username_clean = utf8_clean_string($username);
		if ($username_clean === '')
		{
			return 0;
		}
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean = '" . $this->db->sql_escape($username_clean) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$user_id = (int) $this->db->sql_fetchfield('user_id');
		$this->db->sql_freeresult($result);
		return $user_id;
	}

	private function upsert_user_limit($user_id, $max_ads)
	{
		$this->db->sql_query('DELETE FROM ' . $this->table_user_limits . ' WHERE user_id = ' . (int) $user_id);
		$this->db->sql_query('INSERT INTO ' . $this->table_user_limits . ' ' . $this->db->sql_build_array('INSERT', ['user_id' => (int) $user_id, 'max_ads' => (int) $max_ads]));
	}

	private function upsert_group_limit($group_id, $max_ads)
	{
		$this->db->sql_query('DELETE FROM ' . $this->table_group_limits . ' WHERE group_id = ' . (int) $group_id);
		$this->db->sql_query('INSERT INTO ' . $this->table_group_limits . ' ' . $this->db->sql_build_array('INSERT', ['group_id' => (int) $group_id, 'max_ads' => (int) $max_ads]));
	}

	private function get_forbidden_terms()
	{
		$rows = [];
		$sql = 'SELECT * FROM ' . $this->table_forbidden_terms . ' ORDER BY term_enabled DESC, term_text ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['TERM_CREATED_DISPLAY'] = !empty($row['term_created']) ? $this->user->format_date((int) $row['term_created']) : '';
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_user_limits()
	{
		$rows = [];
		$sql = 'SELECT l.*, u.username, u.user_colour FROM ' . $this->table_user_limits . ' l LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = l.user_id ORDER BY u.username_clean ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_group_limits()
	{
		$rows = [];
		$sql = 'SELECT l.*, g.group_name, g.group_type FROM ' . $this->table_group_limits . ' l LEFT JOIN ' . GROUPS_TABLE . ' g ON g.group_id = l.group_id ORDER BY g.group_name ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['group_name'] = $this->format_group_name($row['group_name'], isset($row['group_type']) ? (int) $row['group_type'] : 0);
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_group_options()
	{
		$options = [];
		$sql = 'SELECT group_id, group_name, group_type FROM ' . GROUPS_TABLE . ' ORDER BY group_name ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$options[] = [
				'GROUP_ID' => (int) $row['group_id'],
				'GROUP_NAME' => $this->format_group_name($row['group_name'], isset($row['group_type']) ? (int) $row['group_type'] : 0),
			];
		}
		$this->db->sql_freeresult($result);
		return $options;
	}

	private function format_group_name($group_name, $group_type = 0)
	{
		$group_name = (string) $group_name;

		if (defined('GROUP_SPECIAL') && (int) $group_type === GROUP_SPECIAL)
		{
			$translated = $this->language->lang('G_' . $group_name);
			return ($translated !== 'G_' . $group_name) ? $translated : $group_name;
		}

		return $group_name;
	}

	private function get_user_security_rows()
	{
		$rows = [];
		$sql = 'SELECT s.*, u.username, u.user_colour FROM ' . $this->table_user_security . ' s LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id ORDER BY s.updated_at DESC';
		$result = $this->db->sql_query_limit($sql, 50);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['UPDATED_DISPLAY'] = !empty($row['updated_at']) ? $this->user->format_date((int) $row['updated_at']) : '';
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_suspicious_ads()
	{
		$rows = [];
		$sql = 'SELECT a.*, u.username, u.user_colour, c.cat_name FROM ' . $this->table_ads . ' a LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id WHERE a.ad_suspicious = 1 ORDER BY a.ad_created DESC';
		$result = $this->db->sql_query_limit($sql, 25);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['STATUS_LANG'] = $this->get_status_lang((int) $row['ad_status']);
			$row['CAT_NAME_DISPLAY'] = $this->translate_category_text(isset($row['cat_name']) ? $row['cat_name'] : '');
			$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_moderation_logs()
	{
		$rows = [];
		$sql = 'SELECT l.*, a.ad_title, au.username AS admin_username, tu.username AS target_username
			FROM ' . $this->table_moderation_logs . ' l
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = l.ad_id
			LEFT JOIN ' . USERS_TABLE . ' au ON au.user_id = l.admin_user_id
			LEFT JOIN ' . USERS_TABLE . ' tu ON tu.user_id = l.target_user_id
			ORDER BY l.log_time DESC';
		$result = $this->db->sql_query_limit($sql, 50);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['LOG_TIME_DISPLAY'] = !empty($row['log_time']) ? $this->user->format_date((int) $row['log_time']) : '';
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function add_moderation_log($ad_id, $target_user_id, $action, $note = '')
	{
		if (!$this->db_tools_table_exists($this->table_moderation_logs))
		{
			return;
		}
		$sql_ary = [
			'ad_id' => (int) $ad_id,
			'target_user_id' => (int) $target_user_id,
			'admin_user_id' => (int) $this->user->data['user_id'],
			'log_action' => (string) $action,
			'log_note' => (string) $note,
			'log_time' => time(),
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_moderation_logs . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}

	private function db_tools_table_exists($table)
	{
		$sql = "SELECT 1 FROM " . $table;
		$result = $this->db->sql_query_limit($sql, 1, 0, 1);
		$this->db->sql_freeresult($result);
		return true;
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
