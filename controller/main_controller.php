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
 * Main controller for Marketplace front-end.
 */
class main_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

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
	protected $table_reviews;

	/** @var string */
	protected $table_follows;

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
	protected $table_favorites;

	/** @var string */
	protected $table_compare;

	/** @var string */
	protected $table_category_fields;

	/** @var string */
	protected $table_ad_field_values;
	protected $table_conversations;
	protected $table_messages;
	protected $table_message_blocks;

	/** @var string */
	protected $upload_path;

	/** @var array */
	protected $column_exists_cache = [];

	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\language\language $language,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request $request,
		\phpbb\pagination $pagination,
		\phpbb\auth\auth $auth,
		\phpbb\cache\driver\driver_interface $cache,
		$root_path,
		$php_ext,
		$table_ads,
		$table_cats,
		$table_images,
		$table_reports,
		$table_notifications,
		$table_promotions,
		$table_promotion_packages,
		$table_purchases,
		$table_follows,
		$table_payment_logs
	)
	{
		$this->config     = $config;
		$this->helper     = $helper;
		$this->template   = $template;
		$this->language   = $language;
		$this->user       = $user;
		$this->db         = $db;
		$this->request    = $request;
		$this->pagination = $pagination;
		$this->auth       = $auth;
		$this->cache      = $cache;
		$this->root_path  = $root_path;
		$this->php_ext    = $php_ext;
		$this->table_ads  = $table_ads;
		$this->table_cats = $table_cats;
		$this->table_images = $table_images;
		$this->table_reports = $table_reports;
		$this->table_notifications = $table_notifications;
		$this->table_promotions = $table_promotions;
		$this->table_promotion_packages = $table_promotion_packages;
		$this->table_purchases = $table_purchases;
		$this->table_reviews = preg_replace('/marketplace_purchases$/', 'marketplace_reviews', $table_purchases);
		$this->table_follows = $table_follows;
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
		$this->table_favorites = preg_replace('/marketplace_ads$/', 'marketplace_favorites', $table_ads);
		$this->table_compare = preg_replace('/marketplace_ads$/', 'marketplace_compare', $table_ads);
		$this->table_category_fields = preg_replace('/marketplace_ads$/', 'marketplace_category_fields', $table_ads);
		$this->table_ad_field_values = preg_replace('/marketplace_ads$/', 'marketplace_ad_field_values', $table_ads);
		$this->table_conversations = preg_replace('/marketplace_ads$/', 'marketplace_conversations', $table_ads);
		$this->table_messages = preg_replace('/marketplace_ads$/', 'marketplace_messages', $table_ads);
		$this->table_message_blocks = preg_replace('/marketplace_ads$/', 'marketplace_message_blocks', $table_ads);

		$this->upload_path = $this->root_path . 'files/marketplace/';
	}

	/**
	 * Marketplace index - list ads.
	 */
	public function index()
	{
		return $this->display_listing(0);
	}

	/**
	 * Ads in a specific category.
	 */
	public function category($cat_id)
	{
		return $this->display_listing((int) $cat_id);
	}


	/**
	 * Public seller page.
	 */
	public function seller($user_id)
	{
		$this->base_assigns();
		$this->language->add_lang('common', 'mundophpbb/marketplace');
		$this->ensure_marketplace_available();

		$user_id = (int) $user_id;
		if ($user_id <= 0 || $user_id === ANONYMOUS)
		{
			\trigger_error($this->language->lang('NO_USER'));
		}

		$sql = 'SELECT user_id, username, user_colour, user_regdate, user_type FROM ' . USERS_TABLE . ' WHERE user_id = ' . $user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$seller = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$seller)
		{
			\trigger_error($this->language->lang('NO_USER'));
		}

		$seller['U_PROFILE'] = \append_sid("{$this->root_path}memberlist.{$this->php_ext}", ['mode' => 'viewprofile', 'u' => $user_id]);
		$seller['MEMBER_SINCE'] = $this->user->format_date((int) $seller['user_regdate']);
		$reputation = $this->get_user_reputation_summary($user_id);

		$sql = 'SELECT a.*, u.username, u.user_colour, c.cat_name
			FROM ' . $this->table_ads . ' a
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
			LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
			WHERE a.user_id = ' . $user_id . ' AND ' . $this->public_ads_where('a') . '
			ORDER BY a.ad_last_bumped DESC, a.ad_created DESC';
		$result = $this->db->sql_query_limit($sql, 24);
		$ads = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$row['U_POSTER'] = $this->helper->route('mundophpbb_marketplace_seller', ['user_id' => (int) $row['user_id']]);
			$row['AD_PRICE_DISPLAY'] = $this->format_price($row);
			$row['MAIN_IMAGE'] = $this->get_main_image((int) $row['ad_id']);
			$this->prepare_ad_for_display($row);
			$ads[] = $row;
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'SELLER' => $seller,
			'SELLER_REPUTATION' => $reputation,
			'ADS' => $ads,
			'TOTAL_ADS' => count($ads),
		]);

		return $this->helper->render('@mundophpbb_marketplace/marketplace_seller.html', $seller['username']);
	}

	public function favorite($ad_id)
	{
		return $this->toggle_user_ad_collection((int) $ad_id, 'favorite');
	}

	public function compare_add($ad_id)
	{
		return $this->toggle_user_ad_collection((int) $ad_id, 'compare_add');
	}

	public function compare_remove($ad_id)
	{
		return $this->toggle_user_ad_collection((int) $ad_id, 'compare_remove');
	}

	public function compare_clear()
	{
		$this->language->add_lang('common', 'mundophpbb/marketplace');
		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			\login_box('', $this->language->lang('LOGIN_REQUIRED'));
		}
		if ($this->db_tools_table_exists($this->table_compare))
		{
			$this->db->sql_query('DELETE FROM ' . $this->table_compare . ' WHERE user_id = ' . (int) $this->user->data['user_id']);
		}
		$redirect = $this->helper->route('mundophpbb_marketplace_compare');
		\meta_refresh(1, $redirect);
		\trigger_error($this->language->lang('MARKETPLACE_COMPARE_CLEARED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
	}

	public function compare()
	{
		$this->base_assigns();
		$this->language->add_lang('common', 'mundophpbb/marketplace');
		$this->ensure_marketplace_available();
		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			\login_box('', $this->language->lang('LOGIN_REQUIRED'));
		}

		$ads = [];
		if ($this->db_tools_table_exists($this->table_compare))
		{
			$sql = 'SELECT a.*, u.username, u.user_colour, c.cat_name
				FROM ' . $this->table_compare . ' mc
				LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = mc.ad_id
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
				LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
				WHERE mc.user_id = ' . (int) $this->user->data['user_id'] . ' AND a.ad_id IS NOT NULL
				ORDER BY mc.compare_time DESC';
			$result = $this->db->sql_query_limit($sql, 4);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
				$row['U_REMOVE_COMPARE'] = $this->helper->route('mundophpbb_marketplace_compare_remove', ['ad_id' => (int) $row['ad_id']]);
				$row['AD_PRICE_DISPLAY'] = $this->format_price($row);
				$row['MAIN_IMAGE'] = $this->get_main_image((int) $row['ad_id']);
				$this->prepare_ad_for_display($row);
				$ads[] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		$this->template->assign_vars([
			'ADS' => $ads,
			'U_CLEAR_COMPARE' => $this->helper->route('mundophpbb_marketplace_compare_clear'),
		]);
		return $this->helper->render('@mundophpbb_marketplace/marketplace_compare.html', $this->language->lang('MARKETPLACE_COMPARE'));
	}

	/**
	 * Shared public listing with search, filters and sorting.
	 */
	private function display_listing($forced_cat_id = 0)
	{
		$this->base_assigns();
		$this->language->add_lang('common', 'mundophpbb/marketplace');
		$this->ensure_marketplace_available();

		$forced_cat_id = (int) $forced_cat_id;
		$current_cat = false;
		if ($forced_cat_id > 0)
		{
			$current_cat = $this->get_category($forced_cat_id);
			if (!$current_cat || !$current_cat['cat_enabled'])
			{
				\trigger_error($this->language->lang('MARKETPLACE_CAT_NOT_FOUND'));
			}
		}

		$start = $this->request->variable('start', 0);
		$per_page = max(5, (int) $this->config['marketplace_items_per_page']);
		$categories = $this->get_categories(true);
		$filters = $this->get_listing_filters($forced_cat_id);
		$where = $this->build_listing_where($filters);
		$order_by = $this->get_listing_order_by($filters['sort']);

		$sql = 'SELECT a.*, u.username, u.user_colour, c.cat_name
				FROM ' . $this->table_ads . ' a
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
				LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
				WHERE ' . $where . '
				ORDER BY ' . $order_by;

		$result = $this->db->sql_query_limit($sql, $per_page, $start);

		$ads = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $row['ad_id']]);
			$row['U_FAVORITE'] = $this->helper->route('mundophpbb_marketplace_favorite', ['ad_id' => (int) $row['ad_id']]);
			$row['U_COMPARE'] = $this->helper->route('mundophpbb_marketplace_compare_add', ['ad_id' => (int) $row['ad_id']]);
			$row['S_IS_FAVORITE'] = $this->is_favorite_ad((int) $row['ad_id']);
			$row['U_POSTER'] = $this->helper->route('mundophpbb_marketplace_seller', ['user_id' => (int) $row['user_id']]);
			$row['AD_PRICE_DISPLAY'] = $this->format_price($row);
			$row['MAIN_IMAGE'] = $this->get_main_image($row['ad_id']);
			$this->prepare_ad_for_display($row);
			$ads[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(*) as total FROM ' . $this->table_ads . ' a WHERE ' . $where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$base_url = ($forced_cat_id > 0) ? $this->helper->route('mundophpbb_marketplace_category', ['cat_id' => $forced_cat_id]) : $this->helper->route('mundophpbb_marketplace_index');
		$pagination_url = $this->append_url_params($base_url, $this->get_filter_url_params($filters, $forced_cat_id > 0));
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total, $per_page, $start);

		$this->template->assign_vars([
			'MARKETPLACE_TITLE' => $current_cat ? $current_cat['CAT_NAME_DISPLAY'] : $this->language->lang('MARKETPLACE'),
			'CURRENT_CAT'       => $current_cat,
			'ADS'               => $ads,
			'CATEGORIES'        => $categories,
			'TOTAL_ADS'         => $total,
			'S_CAN_POST'        => $this->auth->acl_get('u_marketplace_post'),
			'U_POST_NEW'        => $this->helper->route('mundophpbb_marketplace_post'),
			'U_FILTER_ACTION'   => $this->helper->route('mundophpbb_marketplace_index'),
			'U_FILTER_RESET'    => $base_url,
			'U_CLEAR_FILTERS'   => $base_url,
			'S_SHOW_PRICE'      => (bool) $this->config['marketplace_enable_price'],
			'S_SHOW_SOLD_ADS'   => (bool) $this->config['marketplace_show_sold_ads'],
			'S_SHOW_SOLD_FILTER'=> (bool) $this->config['marketplace_show_sold_ads'],
			'FILTER_Q'          => $filters['q'],
			'FILTER_CAT_ID'     => $filters['cat_id'],
			'FILTER_LOCATION'   => $filters['location'],
			'FILTER_CITY_REGION'=> $filters['location'],
			'FILTER_CITY'       => isset($filters['city']) ? $filters['city'] : '',
			'FILTER_REGION'     => isset($filters['region']) ? $filters['region'] : '',
			'FILTER_COUNTRY'    => isset($filters['country']) ? $filters['country'] : '',
			'LOCATION_OPTIONS'  => $this->get_marketplace_location_options(isset($filters['location']) ? $filters['location'] : '', false),
			'REGION_OPTIONS'    => $this->get_marketplace_region_options(isset($filters['region']) ? $filters['region'] : '', false),
			'FILTER_DISTANCE'   => isset($filters['distance']) ? (int) $filters['distance'] : 0,
			'FILTER_LAT'        => isset($filters['lat_raw']) ? $filters['lat_raw'] : '',
			'FILTER_LNG'        => isset($filters['lng_raw']) ? $filters['lng_raw'] : '',
			'FILTER_PRICE_MIN'  => $filters['price_min_raw'],
			'FILTER_PRICE_MAX'  => $filters['price_max_raw'],
			'FILTER_WITH_IMAGE' => $filters['with_image'],
			'FILTER_WITH_IMAGES'=> $filters['with_image'],
			'FILTER_STATUS'     => $filters['status'],
			'FILTER_SORT'       => $filters['sort'],
			'AD_TYPE_OPTIONS'   => $this->get_ad_type_options($filters['ad_type'], true),
			'AD_CONDITION_OPTIONS' => $this->get_ad_condition_options($filters['ad_condition'], true),
			'STATUS_OPTIONS'    => $this->get_public_status_options($filters['status']),
			'SORT_OPTIONS'      => $this->get_sort_options($filters['sort']),
			'U_COMPARE_PAGE'    => $this->helper->route('mundophpbb_marketplace_compare'),
		]);

		$page_title = $current_cat ? $current_cat['CAT_NAME_DISPLAY'] : $this->language->lang('MARKETPLACE');
		return $this->helper->render('@mundophpbb_marketplace/marketplace_index.html', $page_title);
	}

	/**
	 * View single ad
	 */
	public function view($ad_id)
	{
		$this->base_assigns();
		$this->language->add_lang('common', 'mundophpbb/marketplace');
		$this->ensure_marketplace_available();
		\add_form_key('mundophpbb_marketplace_action');

		$ad_id = (int) $ad_id;

		$sql = 'SELECT a.*, u.username, u.user_colour, u.user_id as poster_id, u.user_regdate, u.user_type, c.cat_name, c.cat_id
				FROM ' . $this->table_ads . ' a
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
				LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
				WHERE a.ad_id = ' . $ad_id;
		$result = $this->db->sql_query($sql);
		$ad = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$ad)
		{
			\trigger_error($this->language->lang('MARKETPLACE_AD_NOT_FOUND'));
		}

		$this->expire_ad_if_needed($ad);

		$is_owner = ((int) $ad['user_id'] === (int) $this->user->data['user_id']) && (int) $this->user->data['user_id'] !== ANONYMOUS;
		$is_mod = $this->auth->acl_get('m_marketplace_edit') || $this->auth->acl_get('m_marketplace_approve') || $this->auth->acl_get('m_marketplace_delete') || $this->auth->acl_get('m_marketplace_feature') || $this->auth->acl_get('m_marketplace_reports');

		// Permission: public visible ads, own ads, or marketplace moderators.
		$can_view = $this->is_publicly_visible_ad($ad) || $is_owner || $is_mod;

		if (!$can_view)
		{
			\trigger_error($this->language->lang('MARKETPLACE_AD_NOT_FOUND'));
		}

		$action = $this->request->variable('action', '');
		if ($action !== '')
		{
			$this->handle_quick_action($action, $ad);
		}

		// Increment views only for active ads and not for the owner.
		if ((int) $ad['ad_status'] === 1 && !$is_owner)
		{
			$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ad_views = ad_views + 1 WHERE ad_id = ' . $ad_id);
			$ad['ad_views']++;
		}

		$images = $this->get_ad_images($ad_id);
		$more_ads_from_user = $this->get_more_ads_from_user((int) $ad['user_id'], $ad_id, 6);
		$seller_reputation = $this->get_user_reputation_summary((int) $ad['user_id']);
		$ad_reviews = $this->get_ad_reputation_history($ad_id);

		$ad['AD_PRICE_DISPLAY'] = $this->format_price($ad);
		$ad['cat_name'] = $this->translate_category_text(isset($ad['cat_name']) ? $ad['cat_name'] : '');
		$ad['cat_desc'] = $this->translate_category_text(isset($ad['cat_desc']) ? $ad['cat_desc'] : '');
		$ad['U_CATEGORY'] = $this->helper->route('mundophpbb_marketplace_category', ['cat_id' => (int) $ad['cat_id']]);
		$ad['AD_DESC_HTML'] = $this->render_description($ad['ad_desc']);
		$ad['LOCATION_DISPLAY'] = $this->format_ad_location($ad);
		$ad['MAP_QUERY'] = $this->build_map_query($ad);
		$ad['U_MAP'] = $ad['MAP_QUERY'] !== '' ? 'https://www.openstreetmap.org/search?query=' . rawurlencode($ad['MAP_QUERY']) : '';
		$ad['U_MAP_EMBED'] = (!empty($ad['ad_latitude']) && !empty($ad['ad_longitude'])) ? $this->build_osm_embed_url($ad['ad_latitude'], $ad['ad_longitude']) : '';
		$ad_custom_fields = $this->get_ad_custom_fields_for_display($ad_id);
		$ad['U_POSTER'] = $this->helper->route('mundophpbb_marketplace_seller', ['user_id' => (int) $ad['user_id']]);
		$ad['U_PM'] = ((int) $this->user->data['user_id'] !== ANONYMOUS && (int) $ad['ad_status'] === 1 && !$is_owner) ? \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => 'pm', 'mode' => 'compose', 'u' => $ad['user_id'], 'subject' => $this->language->lang('MARKETPLACE_PM_SUBJECT', $ad['ad_title'])]) : '';
		$ad['U_PM_TRACKED'] = $ad['U_PM'] ? $this->helper->route('mundophpbb_marketplace_contact', ['ad_id' => $ad_id, 'method' => 'pm']) : '';
		$ad['U_MESSAGE'] = ((int) $this->user->data['user_id'] !== ANONYMOUS && (int) $ad['ad_status'] === 1 && !$is_owner) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $ad_id]) : '';
		$ad['U_FAVORITE'] = $this->helper->route('mundophpbb_marketplace_favorite', ['ad_id' => $ad_id]);
		$ad['U_COMPARE'] = $this->helper->route('mundophpbb_marketplace_compare_add', ['ad_id' => $ad_id]);
		$ad['U_WHATSAPP'] = $this->build_whatsapp_url(isset($ad['ad_phone']) ? $ad['ad_phone'] : '');
		$ad['U_WHATSAPP_TRACKED'] = $ad['U_WHATSAPP'] ? $this->helper->route('mundophpbb_marketplace_contact', ['ad_id' => $ad_id, 'method' => 'whatsapp']) : '';
		$ad['U_EDIT'] = $this->can_edit_ad($ad) ? $this->helper->route('mundophpbb_marketplace_edit', ['ad_id' => $ad_id]) : '';
		$ad['U_DELETE'] = $this->can_delete_ad($ad) ? $this->helper->route('mundophpbb_marketplace_delete', ['ad_id' => $ad_id]) : '';
		$ad['U_ACTION'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $ad_id]);
		$this->prepare_ad_for_display($ad);

		$this->template->assign_vars([
			'AD'               => $ad,
			'IMAGES'           => $images,
			'MORE_ADS_FROM_USER' => $more_ads_from_user,
			'S_HAS_MORE_ADS_FROM_USER' => !empty($more_ads_from_user),
			'SELLER_REPUTATION' => $seller_reputation,
			'AD_REVIEWS' => $ad_reviews,
			'S_HAS_AD_REVIEWS' => !empty($ad_reviews),
			'AD_CUSTOM_FIELDS' => $ad_custom_fields,
			'S_HAS_CUSTOM_FIELDS' => !empty($ad_custom_fields),
			'S_CAN_CONTACT'    => ((int) $this->user->data['user_id'] !== ANONYMOUS && (int) $ad['ad_status'] === 1 && !$is_owner && !$this->marketplace_contact_is_blocked((int) $ad['user_id'], (int) $this->user->data['user_id'], (int) $ad['ad_id'])),
			'S_CAN_FOLLOW_SELLER' => $this->can_follow_seller($ad),
			'S_IS_FAVORITE_AD' => $this->is_favorite_ad($ad_id),
			'S_IS_FOLLOWING_SELLER' => $this->is_following_seller((int) $ad['user_id']),
			'S_CAN_BUY'        => $this->can_buy_ad($ad),
			'S_CAN_BUY_WITH_PAYPAL' => $this->can_buy_ad_with_paypal($ad),
			'S_CAN_BUY_WITH_PIX' => $this->can_buy_ad_with_pix($ad),
			'S_HAS_PENDING_PURCHASE' => $this->has_pending_purchase((int) $ad['ad_id'], (int) $this->user->data['user_id']),
			'S_OWN_AD'         => $is_owner,
			'S_IS_MOD'         => $is_mod,
			'S_CAN_APPROVE'    => $this->can_approve_ad($ad),
			'S_CAN_MARK_SOLD'  => $this->can_mark_sold($ad),
			'S_CAN_MANAGE_STOCK' => $this->can_manage_stock($ad),
			'S_CAN_RENEW'      => $this->can_renew_ad($ad),
			'S_CAN_PAUSE'      => $this->can_pause_ad($ad),
			'S_CAN_BUMP'       => $this->can_bump_ad($ad),
			'S_CAN_FEATURE'    => $this->can_feature_ad($ad),
			'S_CAN_UNFEATURE'  => $this->can_unfeature_ad($ad),
			'S_CAN_BOOST'      => $this->can_boost_ad($ad),
			'S_CAN_UNBOOST'    => $this->can_unboost_ad($ad),
			'S_CAN_REQUEST_FEATURED' => $this->can_request_promotion($ad, 'featured'),
			'S_CAN_REQUEST_BOOSTED'  => $this->can_request_promotion($ad, 'boosted'),
			'FEATURED_PACKAGES'      => $this->get_available_promotion_packages('featured'),
			'BOOSTED_PACKAGES'       => $this->get_available_promotion_packages('boosted'),
			'RENEWAL_PACKAGES'       => $this->get_available_promotion_packages('renewal'),
			'S_CAN_REPORT'     => $this->can_report_ad($ad),
			'S_SHOW_PRICE'     => (bool) $this->config['marketplace_enable_price'],
			'FEATURED_DAYS_DEFAULT' => isset($this->config['marketplace_featured_days']) ? (int) $this->config['marketplace_featured_days'] : 14,
			'BOOSTED_DAYS_DEFAULT' => isset($this->config['marketplace_boosted_days']) ? (int) $this->config['marketplace_boosted_days'] : 7,
		]);

		return $this->helper->render('@mundophpbb_marketplace/marketplace_view.html', $ad['ad_title']);
	}


	private function has_reviews_table()
	{
		return !empty($this->table_reviews) && isset($this->config['marketplace_version']) && version_compare((string) $this->config['marketplace_version'], '1.4.20', '>=');
	}

	private function get_user_reputation_summary($user_id)
	{
		$user_id = (int) $user_id;
		$summary = [
			'AVERAGE' => '0.0',
			'TOTAL_REVIEWS' => 0,
			'COMPLETED_SALES' => 0,
			'MEMBER_FOR' => '',
			'S_SELLER_TRUSTED' => false,
			'S_SELLER_VERIFIED' => false,
			'S_GOOD_REPUTATION' => false,
			'AVERAGE_STARS' => '☆☆☆☆☆',
		];

		$sql = 'SELECT user_regdate, user_type FROM ' . USERS_TABLE . ' WHERE user_id = ' . $user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if ($user_row)
		{
			$days = max(0, (int) floor((time() - (int) $user_row['user_regdate']) / 86400));
			$summary['MEMBER_FOR'] = $this->language->lang('MARKETPLACE_SELLER_MEMBER_FOR_DAYS', $days);
			$summary['S_SELLER_VERIFIED'] = $this->is_marketplace_verified_seller($user_id) || in_array((int) $user_row['user_type'], [0, 3], true);
		}

		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_purchases . '
			WHERE seller_user_id = ' . $user_id . ' AND purchase_status = 5';
		$result = $this->db->sql_query($sql);
		$summary['COMPLETED_SALES'] = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		if ($this->has_reviews_table())
		{
			$sql = 'SELECT COUNT(*) AS total, AVG(review_score) AS avg_score
				FROM ' . $this->table_reviews . '
				WHERE reviewed_user_id = ' . $user_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			$summary['TOTAL_REVIEWS'] = (int) $row['total'];
			$summary['AVERAGE'] = number_format((float) $row['avg_score'], 1, ',', '.');
		}

		$avg = (float) str_replace(',', '.', $summary['AVERAGE']);
		$summary['AVERAGE_STARS'] = str_repeat('★', max(0, min(5, (int) round($avg)))) . str_repeat('☆', max(0, 5 - max(0, min(5, (int) round($avg)))));
		$summary['S_GOOD_REPUTATION'] = ($summary['TOTAL_REVIEWS'] >= 3 && $avg >= 4.0);
		$summary['S_SELLER_TRUSTED'] = ($summary['COMPLETED_SALES'] >= 3 && $summary['TOTAL_REVIEWS'] >= 3 && $avg >= 4.5);

		return $summary;
	}

	private function get_ad_reputation_history($ad_id)
	{
		if (!$this->has_reviews_table())
		{
			return [];
		}

		$sql = 'SELECT r.*, u.username, u.user_colour
			FROM ' . $this->table_reviews . ' r
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = r.reviewer_user_id
			WHERE r.ad_id = ' . (int) $ad_id . '
			ORDER BY r.review_time DESC, r.review_id DESC';
		$result = $this->db->sql_query_limit($sql, 5);
		$reviews = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['REVIEW_TIME_DISPLAY'] = !empty($row['review_time']) ? $this->user->format_date((int) $row['review_time']) : '';
			$row['REVIEW_SCORE_STARS'] = str_repeat('★', max(0, (int) $row['review_score'])) . str_repeat('☆', max(0, 5 - (int) $row['review_score']));
			$row['REVIEWER_ROLE_LANG'] = $this->language->lang($row['reviewer_role'] === 'seller' ? 'MARKETPLACE_REVIEW_ROLE_SELLER' : 'MARKETPLACE_REVIEW_ROLE_BUYER');
			$reviews[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $reviews;
	}


	public function contact($ad_id, $method)
	{
		$this->language->add_lang('common', 'mundophpbb/marketplace');
		$ad_id = (int) $ad_id;
		$method = (string) $method;
		$sql = 'SELECT ad_id, user_id, ad_title, ad_status, ad_phone, ad_contact_method FROM ' . $this->table_ads . ' WHERE ad_id = ' . $ad_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$ad = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$ad || (int) $ad['ad_status'] !== 1 || (int) $ad['user_id'] === (int) $this->user->data['user_id'])
		{
			\trigger_error($this->language->lang('MARKETPLACE_CONTACT_UNAVAILABLE'));
		}
		if ($this->column_exists($this->table_ads, 'ad_contact_count'))
		{
			$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ad_contact_count = ad_contact_count + 1 WHERE ad_id = ' . $ad_id);
		}
		if ($method === 'whatsapp')
		{
			$url = $this->build_whatsapp_url(isset($ad['ad_phone']) ? $ad['ad_phone'] : '');
		}
		else
		{
			$url = ((int) $this->user->data['user_id'] !== ANONYMOUS) ? \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => 'pm', 'mode' => 'compose', 'u' => (int) $ad['user_id'], 'subject' => $this->language->lang('MARKETPLACE_PM_SUBJECT', $ad['ad_title'])]) : $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $ad_id]);
		}
		return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
	}

	/**
	 * Serve an uploaded marketplace image through phpBB routing.
	 */

	/**
	 * PayPal IPN endpoint.
	 *
	 * PayPal posts Instant Payment Notification data to this route. The payload is
	 * verified with PayPal before any local promotion is approved.
	 */

	public function payment($context, $id)
	{
		$this->base_assigns();
		$this->language->add_lang('common', 'mundophpbb/marketplace');
		$this->ensure_marketplace_available();

		$context = strtolower((string) $context);
		$id = (int) $id;
		if ((int) $this->user->data['user_id'] === ANONYMOUS || $id <= 0 || !in_array($context, ['purchase', 'promotion'], true))
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		$payment = ($context === 'purchase') ? $this->get_purchase_payment_context($id) : $this->get_promotion_payment_context($id);
		if (!$payment)
		{
			\trigger_error($this->language->lang('MARKETPLACE_PAYMENT_NOT_FOUND'));
		}

		if ($payment['payment_provider'] === 'pix')
		{
			$pix_key_type = (string) ($this->config['marketplace_gateway_pix_key_type'] ?? 'cpf');
			$pix_key = (string) ($this->config['marketplace_gateway_pix_key'] ?? '');
			$receiver_name = (string) ($this->config['marketplace_gateway_pix_receiver_name'] ?? '');
			$receiver_city = (string) ($this->config['marketplace_gateway_pix_receiver_city'] ?? '');
			$amount_cents = $context === 'purchase' ? (int) ($payment['purchase_amount_cents'] ?? 0) : (int) ($payment['promotion_amount_cents'] ?? 0);
			$payment_reference = (string) ($payment['payment_reference'] ?? '');
			$pix_payload = $this->build_pix_emv_payload($pix_key, $receiver_name, $receiver_city, $amount_cents, $payment_reference);

			$payment['PIX_KEY_TYPE_LANG'] = $this->get_pix_key_type_lang($pix_key_type);
			$payment['PIX_KEY_MASKED'] = $this->mask_pix_key($pix_key, $pix_key_type);
			$payment['PIX_KEY_RAW'] = $pix_key;
			$payment['PIX_RECEIVER_NAME'] = $receiver_name;
			$payment['PIX_RECEIVER_CITY'] = $receiver_city;
			$payment['PIX_INSTRUCTIONS'] = (string) ($this->config['marketplace_gateway_pix_instructions'] ?? '');
			$payment['PIX_BR_CODE'] = $pix_payload;
			$payment['S_HAS_PIX_QR'] = $pix_payload !== '';
			$deadline = isset($this->config['marketplace_gateway_pix_deadline_minutes']) ? (int) $this->config['marketplace_gateway_pix_deadline_minutes'] : 1440;
			$payment['PIX_DEADLINE_MINUTES'] = max(5, $deadline);
			$payment['S_IS_PIX'] = true;
		}

		$this->template->assign_vars([
			'PAYMENT' => $payment,
			'U_BACK_TO_AD' => $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $payment['ad_id']]),
		]);

		return $this->helper->render('@mundophpbb_marketplace/marketplace_payment.html', $this->language->lang('MARKETPLACE_PAYMENT_INSTRUCTIONS'));
	}

	private function get_purchase_payment_context($purchase_id)
	{
		$sql = 'SELECT p.*, a.ad_title
			FROM ' . $this->table_purchases . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE p.purchase_id = ' . (int) $purchase_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$row || (int) $this->user->data['user_id'] !== (int) $row['buyer_user_id'])
		{
			return null;
		}
		return $this->prepare_payment_context_row($row, 'purchase');
	}

	private function get_promotion_payment_context($promotion_id)
	{
		$sql = 'SELECT p.*, a.ad_title
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE p.promotion_id = ' . (int) $promotion_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$row || (int) $row['user_id'] !== (int) $this->user->data['user_id'])
		{
			return null;
		}
		return $this->prepare_payment_context_row($row, 'promotion');
	}

	private function prepare_payment_context_row(array $row, $context)
	{
		$amount = ($context === 'purchase') ? (int) $row['purchase_amount_cents'] : (int) $row['promotion_amount_cents'];
		$currency = ($context === 'purchase') ? (string) $row['purchase_currency'] : (string) $row['promotion_currency'];
		$row['PAYMENT_CONTEXT'] = $context;
		$row['PAYMENT_CONTEXT_LANG'] = $context === 'purchase' ? $this->language->lang('MARKETPLACE_PURCHASE') : $this->language->lang('MARKETPLACE_PROMOTION');
		$row['PAYMENT_AMOUNT_DISPLAY'] = $this->format_package_price($amount, $currency ?: ($this->config['marketplace_paypal_currency'] ?? 'BRL'));
		$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? (string) $row['payment_reference'] : '-';
		$row['PAYMENT_PROVIDER_LANG'] = strtoupper((string) $row['payment_provider']);
		return $row;
	}

	private function pix_emv_field($id, $value)
	{
		$value = (string) $value;
		if ($value === '')
		{
			return '';
		}

		return sprintf('%02d%02d%s', (int) $id, strlen($value), $value);
	}

	private function normalize_pix_text($text, $max_length)
	{
		$text = trim((string) $text);
		if (function_exists('iconv'))
		{
			$converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
			if ($converted !== false)
			{
				$text = $converted;
			}
		}
		$text = strtoupper($text);
		$text = preg_replace('/[^A-Z0-9 .\-]/', '', $text);
		$text = preg_replace('/\s+/', ' ', $text);
		$text = trim($text);

		return substr($text, 0, (int) $max_length);
	}

	private function normalize_pix_txid($reference)
	{
		$txid = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $reference));
		if ($txid === '')
		{
			$txid = 'MARKETPLACE';
		}

		return substr($txid, 0, 25);
	}

	private function pix_crc16($payload)
	{
		$crc = 0xFFFF;
		$payload = (string) $payload;

		for ($i = 0, $len = strlen($payload); $i < $len; $i++)
		{
			$crc ^= ord($payload[$i]) << 8;
			for ($bit = 0; $bit < 8; $bit++)
			{
				if (($crc & 0x8000) !== 0)
				{
					$crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
				}
				else
				{
					$crc = ($crc << 1) & 0xFFFF;
				}
			}
		}

		return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
	}

	private function build_pix_emv_payload($pix_key, $receiver_name, $receiver_city, $amount_cents, $reference)
	{
		$pix_key = trim((string) $pix_key);
		if ($pix_key === '')
		{
			return '';
		}

		$receiver_name = $this->normalize_pix_text($receiver_name ?: 'MARKETPLACE', 25);
		$receiver_city = $this->normalize_pix_text($receiver_city ?: 'BRASIL', 15);
		$txid = $this->normalize_pix_txid($reference);
		$merchant_account = $this->pix_emv_field(0, 'br.gov.bcb.pix') . $this->pix_emv_field(1, $pix_key);
		$payload = '';
		$payload .= $this->pix_emv_field(0, '01');
		$payload .= $this->pix_emv_field(26, $merchant_account);
		$payload .= $this->pix_emv_field(52, '0000');
		$payload .= $this->pix_emv_field(53, '986');
		if ((int) $amount_cents > 0)
		{
			$payload .= $this->pix_emv_field(54, number_format(((int) $amount_cents) / 100, 2, '.', ''));
		}
		$payload .= $this->pix_emv_field(58, 'BR');
		$payload .= $this->pix_emv_field(59, $receiver_name);
		$payload .= $this->pix_emv_field(60, $receiver_city);
		$payload .= $this->pix_emv_field(62, $this->pix_emv_field(5, $txid));
		$payload_without_crc = $payload . '6304';

		return $payload_without_crc . $this->pix_crc16($payload_without_crc);
	}

	public function paypal_ipn()
	{
		if (empty($this->config['marketplace_paypal_enabled']))
		{
			return new \Symfony\Component\HttpFoundation\Response('DISABLED', 200, ['Content-Type' => 'text/plain']);
		}

		$raw_post = file_get_contents('php://input');
		if ($raw_post === false || trim($raw_post) === '')
		{
			$this->log_payment_ipn([], 'invalid', 'EMPTY', 0, 'paypal');
			return new \Symfony\Component\HttpFoundation\Response('EMPTY', 400, ['Content-Type' => 'text/plain']);
		}

		$ipn_data = [];
		parse_str($raw_post, $ipn_data);

		if (!$this->verify_paypal_ipn($raw_post))
		{
			$this->log_payment_ipn($ipn_data, 'invalid', 'PAYPAL_NOT_VERIFIED', 0, 'paypal');
			return new \Symfony\Component\HttpFoundation\Response('INVALID', 200, ['Content-Type' => 'text/plain']);
		}

		$result = $this->process_paypal_ipn($ipn_data);

		return new \Symfony\Component\HttpFoundation\Response($result, 200, ['Content-Type' => 'text/plain']);
	}

	public function image($image_id)
	{
		if (!$this->can_view_marketplace())
		{
			return $this->image_not_found_response();
		}

		$image_id = (int) $image_id;
		if ($image_id <= 0)
		{
			return $this->image_not_found_response();
		}

		$sql = 'SELECT i.*, a.ad_status, a.user_id, a.ad_expires, a.ad_sold_at
				FROM ' . $this->table_images . ' i
				LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = i.ad_id
				WHERE i.image_id = ' . $image_id;
		$result = $this->db->sql_query($sql);
		$image = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$image || empty($image['image_filename']))
		{
			return $this->image_not_found_response();
		}

		$is_publicly_visible = $this->is_publicly_visible_ad($image);
		$can_view = $is_publicly_visible ||
			((int) $this->user->data['user_id'] !== ANONYMOUS && (int) $image['user_id'] === (int) $this->user->data['user_id']) ||
			$this->auth->acl_get('m_marketplace_edit') ||
			$this->auth->acl_get('m_marketplace_approve') ||
			$this->auth->acl_get('m_marketplace_delete');

		if (!$can_view)
		{
			return $this->image_not_found_response();
		}

		$filename = (string) $image['image_filename'];
		if (!$this->is_safe_image_filename($filename))
		{
			return $this->image_not_found_response();
		}

		$path = $this->upload_path . $filename;
		if (!is_file($path) || !is_readable($path))
		{
			return $this->image_placeholder_response();
		}

		$mime_type = $this->detect_image_mime($path);
		if ($mime_type === '')
		{
			return $this->image_placeholder_response();
		}

		$content = @file_get_contents($path);
		if ($content === false)
		{
			return $this->image_placeholder_response();
		}

		$response = new \Symfony\Component\HttpFoundation\Response($content, 200);
		$response->headers->set('Content-Type', $mime_type);
		$response->headers->set('X-Content-Type-Options', 'nosniff');
		$response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
		$response->headers->set('Content-Length', (string) strlen($content));
		$response->headers->set('Cache-Control', $is_publicly_visible ? 'public, max-age=604800' : 'private, no-cache');

		return $response;
	}

	/**
	 * Post new ad
	 */
	public function post()
	{
		return $this->post_or_edit(0);
	}

	/**
	 * Edit existing ad
	 */
	public function edit($ad_id)
	{
		return $this->post_or_edit((int) $ad_id);
	}

	/**
	 * Shared logic for post and edit
	 */
	private function post_or_edit($ad_id = 0)
	{
		$this->base_assigns();
		$this->language->add_lang('common', 'mundophpbb/marketplace');

		if (!$this->config['marketplace_enabled'])
		{
			\trigger_error($this->language->lang('MARKETPLACE_DISABLED'));
		}

		$is_edit = ($ad_id > 0);
		$ad = [];

		if (!$is_edit && !$this->auth->acl_get('u_marketplace_post'))
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		if (!$is_edit && $this->is_user_publish_blocked((int) $this->user->data['user_id']))
		{
			\trigger_error($this->language->lang('MARKETPLACE_USER_BLOCKED_FROM_POSTING'));
		}

		if ($is_edit)
		{
			$sql = 'SELECT * FROM ' . $this->table_ads . ' WHERE ad_id = ' . $ad_id;
			$result = $this->db->sql_query($sql);
			$ad = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$ad)
			{
				\trigger_error($this->language->lang('MARKETPLACE_AD_NOT_FOUND'));
			}

			if (!$this->can_edit_ad($ad))
			{
				\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
			}
		}

		\add_form_key('mundophpbb_marketplace_post');

		$submit = $this->request->is_set_post('submit');
		$errors = [];
		$image_gallery_updated = (bool) $this->request->variable('gallery_updated', 0);
		$ad_saved = (bool) $this->request->variable('ad_saved', 0);

		if ($submit)
		{
			if (!\check_form_key('mundophpbb_marketplace_post'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			$ad_title   = $this->request->variable('ad_title', '', true);
			$cat_id     = $this->request->variable('cat_id', 0);
			$ad_desc    = $this->request->variable('ad_desc', '', true);
			$ad_price   = $this->request->variable('ad_price', '', true);
			$ad_location= $this->request->variable('ad_location', '', true);
			$ad_city = $this->request->variable('ad_city', '', true);
			$ad_region = $this->request->variable('ad_region', '', true);
			$ad_country = $this->request->variable('ad_country', '', true);
			$ad_postal_code = $this->request->variable('ad_postal_code', '', true);
			$ad_location_approx = (int) $this->request->variable('ad_location_approx', 0);
			$ad_latitude = $this->sanitize_coordinate($this->request->variable('ad_latitude', '', true), 90);
			$ad_longitude = $this->sanitize_coordinate($this->request->variable('ad_longitude', '', true), 180);
			$ad_conservation = $this->request->variable('ad_conservation', '', true);
			$ad_delivery_options = $this->sanitize_delivery_options($this->request->variable('ad_delivery_options', [0]));
			if ($ad_location === '')
			{
				$ad_location = $this->compose_location($ad_city, $ad_region, $ad_country);
			}
			$ad_phone   = $this->request->variable('ad_phone', '', true);
			$ad_paypal_email_raw = $this->request->variable('ad_paypal_email', '', true);
			$ad_paypal_email = $this->sanitize_paypal_email($ad_paypal_email_raw);
			$contact_method = $this->request->variable('contact_method', 1);
			$ad_type = $this->sanitize_ad_type($this->request->variable('ad_type', 1));
			$ad_condition = $this->sanitize_ad_condition($this->request->variable('ad_condition', 0));
			$ad_quantity = $this->sanitize_quantity($this->request->variable('ad_quantity', 1));
			$ad_price_type = $this->sanitize_price_type($this->request->variable('ad_price_type', 2));
			$ad_price_cents = in_array($ad_price_type, [3, 4], true) ? 0 : $this->parse_price_amount($ad_price);
			$selected_category = $cat_id > 0 ? $this->get_category($cat_id) : false;
			$matched_forbidden_terms = $this->find_forbidden_terms($ad_title . ' ' . $ad_desc);
			if ($selected_category && isset($selected_category['cat_allow_price']) && !(int) $selected_category['cat_allow_price'])
			{
				$ad_price = '';
				$ad_price_type = 4;
				$ad_price_cents = 0;
			}

			if (\utf8_clean_string($ad_title) === '')
			{
				$errors[] = $this->language->lang('MARKETPLACE_TITLE_REQUIRED');
			}
			if ($cat_id <= 0)
			{
				$errors[] = $this->language->lang('MARKETPLACE_CAT_REQUIRED');
			}
			else if (!$selected_category || empty($selected_category['cat_enabled']))
			{
				$errors[] = $this->language->lang('MARKETPLACE_CAT_NOT_FOUND');
			}
			if (\utf8_clean_string($ad_desc) === '')
			{
				$errors[] = $this->language->lang('MARKETPLACE_DESC_REQUIRED');
			}
			if (!empty($matched_forbidden_terms) && empty($this->config['marketplace_security_auto_flag_forbidden']))
			{
				$errors[] = $this->language->lang('MARKETPLACE_FORBIDDEN_TERMS_FOUND', implode(', ', $matched_forbidden_terms));
			}
			if (!empty($this->config['marketplace_enable_price']) && $ad_price_type === 1 && $ad_price_cents <= 0)
			{
				$errors[] = $this->language->lang('MARKETPLACE_PRICE_INVALID');
			}
			if (in_array($ad_price_type, [3, 4], true))
			{
				$ad_price = '';
			}

			if ($selected_category)
			{
				$this->validate_category_requirements($selected_category, $ad_type, $ad_price_type, $ad_price_cents, $ad_location, $ad_phone, $errors);
				$this->validate_custom_fields((int) $selected_category['cat_id'], $this->request->variable('custom_fields', [0 => ''], true), $errors);
			}

			if (in_array((int) $contact_method, [2, 3], true) && \utf8_clean_string($ad_phone) === '')
			{
				$errors[] = $this->language->lang('MARKETPLACE_WHATSAPP_REQUIRED');
			}

			if (trim((string) $ad_paypal_email_raw) !== '' && $ad_paypal_email === '')
			{
				$errors[] = $this->language->lang('MARKETPLACE_SELLER_PAYPAL_EMAIL_INVALID');
			}

			// Check effective max ads for new ads.
			if (!$is_edit && !$this->auth->acl_get('m_marketplace_edit'))
			{
				$effective_limit = $this->get_effective_ad_limit((int) $this->user->data['user_id']);
				$sql = 'SELECT COUNT(*) as cnt FROM ' . $this->table_ads . ' WHERE user_id = ' . (int) $this->user->data['user_id'] . ' AND ad_status IN (0,1)';
				$result = $this->db->sql_query($sql);
				$count = (int) $this->db->sql_fetchfield('cnt');
				$this->db->sql_freeresult($result);

				if ($effective_limit > 0 && $count >= $effective_limit)
				{
					$errors[] = $this->language->lang('MARKETPLACE_MAX_ADS_REACHED', $effective_limit);
				}
			}

			$delete_image_ids = $is_edit ? $this->get_requested_image_ids('delete_images') : [];
			$main_image_id = $is_edit ? $this->request->variable('main_image_id', 0) : 0;
			$image_order_touched = $is_edit ? (bool) $this->request->variable('image_order_touched', 0) : false;
			$image_order_ids = ($is_edit && $image_order_touched) ? $this->get_requested_image_ids('image_order') : [];
			$uploaded_images = [];

			$can_upload_images = $this->config['marketplace_allow_images'] && (!$selected_category || !isset($selected_category['cat_allow_images']) || (int) $selected_category['cat_allow_images']);
			if ($can_upload_images)
			{
				$existing_count = $is_edit ? $this->get_image_count($ad_id) : 0;
				$delete_count = $is_edit ? $this->count_existing_images($ad_id, $delete_image_ids) : 0;
				$available_slots = max(0, (int) $this->config['marketplace_max_images'] - max(0, $existing_count - $delete_count));
				$uploaded_images = $this->handle_image_uploads($errors, $available_slots);
			}

			if (!empty($errors) && !empty($uploaded_images))
			{
				$this->cleanup_uploaded_images($uploaded_images);
			}

			if (empty($errors))
			{
				$now = time();
				$expires = $this->calculate_expiration_time($now, $cat_id);

				$data = [
					'cat_id'            => $cat_id,
					'ad_title'          => $ad_title,
					'ad_desc'           => $ad_desc,
					'ad_price'          => $ad_price,
					'ad_currency'       => $this->config['marketplace_currency_default'],
					'ad_location'       => $ad_location,
					'ad_city'           => $ad_city,
					'ad_region'         => $ad_region,
					'ad_country'        => $ad_country,
					'ad_postal_code'    => $ad_postal_code,
					'ad_location_approx'=> $ad_location_approx,
					'ad_latitude'       => $ad_latitude,
					'ad_longitude'      => $ad_longitude,
					'ad_conservation'   => $ad_conservation,
					'ad_delivery_options' => $ad_delivery_options,
					'ad_phone'          => $ad_phone,
				'ad_paypal_email'   => $ad_paypal_email,
					'ad_contact_method' => $contact_method,
					'ad_updated'        => $now,
				];

				// These fields were introduced in v1.2.0. Keep submit compatible
				// with databases that are recovering from a partial migration run.
				$package2_fields = [
					'ad_suspicious'  => !empty($matched_forbidden_terms) ? 1 : 0,
					'ad_price_type'  => $ad_price_type,
					'ad_price_cents' => $ad_price_cents,
					'ad_type'        => $ad_type,
					'ad_condition'   => $ad_condition,
					'ad_quantity'    => $ad_quantity,
				];
				foreach ($package2_fields as $column => $value)
				{
					if ($this->column_exists($this->table_ads, $column))
					{
						$data[$column] = $value;
					}
				}

				$data = $this->filter_existing_ad_columns($data);

				if ($is_edit)
				{
					$old_ad_snapshot = $ad;
					$sql = 'UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ad_id = ' . $ad_id;
					$this->db->sql_query($sql);
					$this->record_ad_edit_history($ad_id, $old_ad_snapshot, array_merge($old_ad_snapshot, $data));

					if ((int) $ad['user_id'] === (int) $this->user->data['user_id'] && ($this->config['marketplace_require_approval'] || !empty($selected_category['cat_require_approval']) || !empty($matched_forbidden_terms)) && (int) $ad['ad_status'] === 1 && !$this->auth->acl_get('m_marketplace_edit'))
					{
						$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ad_status = 0, ad_expires = 0 WHERE ad_id = ' . $ad_id);
					}

					if ($this->config['marketplace_allow_images'])
					{
						foreach ($delete_image_ids as $image_id)
						{
							$this->delete_ad_image($ad_id, $image_id);
						}

						if ($main_image_id > 0)
						{
							$this->set_main_image($ad_id, $main_image_id);
						}

						if (!empty($image_order_ids))
						{
							$this->update_image_order($ad_id, $image_order_ids);
						}
					}
				}
				else
				{
					$new_status = ($this->config['marketplace_require_approval'] || !empty($selected_category['cat_require_approval']) || !empty($matched_forbidden_terms)) ? 0 : 1;
					$data = array_merge($data, [
						'user_id'            => $this->user->data['user_id'],
						'ad_status'          => $new_status,
						'ad_created'         => $now,
						'ad_expires'         => ($new_status === 1) ? $expires : 0,
						'ad_views'           => 0,
						'ad_sold_at'         => 0,
						'ad_expired_at'      => 0,
						'ad_last_renewed'    => 0,
						'ad_approved_at'     => ($new_status === 1) ? $now : 0,
						'ad_approved_by'     => ($new_status === 1) ? (int) $this->user->data['user_id'] : 0,
						'ad_hidden_at'       => 0,
						'ad_hidden_by'       => 0,
						'ad_hidden_reason'   => '',
						'ad_last_bumped'     => 0,
						'ad_featured_until'  => 0,
						'ad_featured_by'     => 0,
						'ad_boosted_until'   => 0,
						'ad_boosted_by'      => 0,
					]);

					// Fields introduced by later migrations may be NOT NULL on
					// existing boards running MySQL strict mode. Always provide
					// explicit values when the columns exist, so new ad creation
					// does not depend on database defaults for TEXT/TIMESTAMP fields.
					$later_ad_defaults = [
						'ad_refusal_reason' => '',
						'ad_removed_at'     => 0,
						'ad_removed_by'     => 0,
						'ad_contact_count'  => 0,
					];
					foreach ($later_ad_defaults as $column => $value)
					{
						if ($this->column_exists($this->table_ads, $column))
						{
							$data[$column] = $value;
						}
					}
					$sql = 'INSERT INTO ' . $this->table_ads . ' ' . $this->db->sql_build_array('INSERT', $data);
					$this->db->sql_query($sql);
					$ad_id = (int) $this->db->sql_nextid();
					if ($new_status === 1)
					{
						$this->notify_followers_new_ad($ad_id, (int) $this->user->data['user_id'], $ad_title);
					}
				}

				if ($ad_id)
				{
					$this->save_custom_field_values($ad_id, $cat_id, $this->request->variable('custom_fields', [0 => ''], true));
				}

				if (!empty($uploaded_images) && $ad_id)
				{
					$this->save_ad_images($ad_id, $uploaded_images, $is_edit);
				}

				if ($this->config['marketplace_allow_images'] && $ad_id)
				{
					$this->ensure_main_image($ad_id);
				}

				$image_action_performed = !empty($uploaded_images) || !empty($delete_image_ids) || $main_image_id > 0 || !empty($image_order_ids);

				if ($image_action_performed && $ad_id)
				{
					// Keep the user on the ad form after image uploads/changes, but force a fresh
					// reload with a visible confirmation so they do not need to refresh manually
					// or press the publish/update button a second time to see the saved images.
					\redirect($this->helper->route('mundophpbb_marketplace_edit', [
						'ad_id' => $ad_id,
						'gallery_updated' => 1,
						'ad_saved' => $is_edit ? 0 : 1,
						'_mp' => time(),
					]));
				}

				$redirect = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $ad_id]);
				$success_msg = $is_edit ? 'MARKETPLACE_AD_UPDATED' : ($this->config['marketplace_require_approval'] ? 'MARKETPLACE_AD_POSTED_PENDING' : 'MARKETPLACE_AD_POSTED');

				\meta_refresh(3, $redirect);
				\trigger_error($this->language->lang($success_msg) . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			}
		}

		if ($submit && !empty($errors))
		{
			$ad = array_merge($ad ?: [], [
				'ad_title'        => $this->request->variable('ad_title', '', true),
				'cat_id'          => $this->request->variable('cat_id', 0),
				'ad_desc'         => $this->request->variable('ad_desc', '', true),
				'ad_price'        => $this->request->variable('ad_price', '', true),
				'ad_price_type'   => $this->sanitize_price_type($this->request->variable('ad_price_type', 2)),
				'ad_type'         => $this->sanitize_ad_type($this->request->variable('ad_type', 1)),
				'ad_condition'    => $this->sanitize_ad_condition($this->request->variable('ad_condition', 0)),
				'ad_quantity'     => $this->sanitize_quantity($this->request->variable('ad_quantity', 1)),
				'ad_location'     => $this->request->variable('ad_location', '', true),
				'ad_city'         => $this->request->variable('ad_city', '', true),
				'ad_region'       => $this->request->variable('ad_region', '', true),
				'ad_country'      => $this->request->variable('ad_country', '', true),
				'ad_postal_code'  => $this->request->variable('ad_postal_code', '', true),
				'ad_location_approx' => (int) $this->request->variable('ad_location_approx', 0),
				'ad_latitude'     => $this->request->variable('ad_latitude', '', true),
				'ad_longitude'    => $this->request->variable('ad_longitude', '', true),
				'ad_conservation' => $this->request->variable('ad_conservation', '', true),
				'ad_delivery_options' => $this->sanitize_delivery_options($this->request->variable('ad_delivery_options', [0])),
				'ad_phone'        => $this->request->variable('ad_phone', '', true),
				'ad_paypal_email' => $this->sanitize_paypal_email($this->request->variable('ad_paypal_email', '', true)),
				'ad_contact_method' => $this->request->variable('contact_method', 1),
			]);
		}

		$ad = array_merge([
			'ad_title'          => '',
			'cat_id'            => 0,
			'ad_desc'           => '',
			'ad_price'          => '',
			'ad_price_type'     => 2,
			'ad_type'           => 1,
			'ad_condition'      => 0,
			'ad_quantity'       => 1,
			'ad_location'       => '',
			'ad_city'           => '',
			'ad_region'         => '',
			'ad_country'        => '',
			'ad_postal_code'    => '',
			'ad_location_approx'=> 0,
			'ad_latitude'       => '',
			'ad_longitude'      => '',
			'ad_conservation'   => '',
			'ad_delivery_options' => '',
			'ad_phone'          => '',
			'ad_paypal_email'   => '',
			'ad_contact_method' => 1,
		], $ad ?: []);

		$categories = $this->get_categories(true);
		$current_images = $is_edit ? $this->get_ad_images($ad_id) : [];
		$available_slots = (int) $this->config['marketplace_max_images'];
		if ($is_edit)
		{
			$available_slots = max(0, (int) $this->config['marketplace_max_images'] - $this->get_image_count($ad_id));
		}

		$custom_field_values = $submit ? $this->request->variable('custom_fields', [0 => ''], true) : ($is_edit ? $this->get_ad_custom_field_values($ad_id) : []);
		$category_field_groups = $this->get_category_field_groups((int) $ad['cat_id'], $custom_field_values);

		$this->template->assign_vars([
			'S_POST_MODE'     => true,
			'S_EDIT_MODE'     => $is_edit,
			'AD'              => $ad,
			'ERRORS'          => $errors,
			'CATEGORIES'      => $categories,
			'CURRENT_IMAGES'  => $current_images,
			'S_IMAGE_GALLERY_UPDATED' => $image_gallery_updated,
			'S_AD_SAVED'      => $ad_saved,
			'U_VIEW_AD'       => $is_edit ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $ad_id]) : '',
			'MAX_IMAGES'      => (int) $this->config['marketplace_max_images'],
			'IMAGE_SLOTS_LEFT'=> $available_slots,
			'S_ALLOW_IMAGES'  => (bool) $this->config['marketplace_allow_images'] && $this->category_allows_images((int) $ad['cat_id']),
			'S_ENABLE_PRICE'  => (bool) $this->config['marketplace_enable_price'],
			'S_REQUIRE_APPROVAL' => (bool) $this->config['marketplace_require_approval'],
			'AD_TYPE_OPTIONS' => $this->get_ad_type_options((int) $ad['ad_type'], false),
			'AD_CONDITION_OPTIONS' => $this->get_ad_condition_options((int) $ad['ad_condition'], false),
			'PRICE_TYPE_OPTIONS' => $this->get_price_type_options((int) $ad['ad_price_type']),
			'DELIVERY_OPTIONS' => $this->get_delivery_options(isset($ad['ad_delivery_options']) ? $ad['ad_delivery_options'] : ''),
			'LOCATION_OPTIONS' => $this->get_marketplace_location_options(isset($ad['ad_location']) ? $ad['ad_location'] : '', true),
			'REGION_OPTIONS' => $this->get_marketplace_region_options(isset($ad['ad_region']) ? $ad['ad_region'] : '', true),
			'CATEGORY_FIELD_GROUPS' => $category_field_groups,
			'U_BACK'          => $is_edit ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $ad_id]) : $this->helper->route('mundophpbb_marketplace_index'),
			'U_ACTION'        => $is_edit ? $this->helper->route('mundophpbb_marketplace_edit', ['ad_id' => $ad_id]) : $this->helper->route('mundophpbb_marketplace_post'),
		]);

		$page_title = $is_edit ? $this->language->lang('MARKETPLACE_EDIT_AD') : $this->language->lang('MARKETPLACE_POST_AD');
		return $this->helper->render('@mundophpbb_marketplace/marketplace_post.html', $page_title);
	}

	/**
	 * Delete own ad or moderator delete.
	 */
	public function delete($ad_id)
	{
		$this->language->add_lang('common', 'mundophpbb/marketplace');

		$ad_id = (int) $ad_id;

		$sql = 'SELECT * FROM ' . $this->table_ads . ' WHERE ad_id = ' . $ad_id;
		$result = $this->db->sql_query($sql);
		$ad = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$ad)
		{
			\trigger_error($this->language->lang('MARKETPLACE_AD_NOT_FOUND'));
		}

		if (!$this->can_delete_ad($ad))
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		if (!\confirm_box(true))
		{
			$s_hidden_fields = \build_hidden_fields([
				'ad_id' => $ad_id,
			]);
			\confirm_box(false, $this->language->lang('MARKETPLACE_CONFIRM_DELETE'), $s_hidden_fields);
			return;
		}

		$this->delete_ad_images($ad_id);
		$this->db->sql_query('DELETE FROM ' . $this->table_ads . ' WHERE ad_id = ' . $ad_id);

		$redirect = $this->helper->route('mundophpbb_marketplace_index');
		\meta_refresh(2, $redirect);
		\trigger_error($this->language->lang('MARKETPLACE_AD_DELETED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
	}

	// ----------------- Helper methods -----------------

	private function base_assigns()
	{
		$this->template->assign_vars([
			'U_MARKETPLACE' => $this->helper->route('mundophpbb_marketplace_index'),
			'S_MARKETPLACE_ENABLED' => (bool) $this->config['marketplace_enabled'],
		]);
	}

	private function ensure_marketplace_available()
	{
		if (!$this->config['marketplace_enabled'])
		{
			\trigger_error($this->language->lang('MARKETPLACE_DISABLED'));
		}

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}
	}

	private function can_view_marketplace()
	{
		return $this->auth->acl_get('u_marketplace_view') || $this->auth->acl_get('m_marketplace_edit') || $this->auth->acl_get('m_marketplace_approve') || $this->auth->acl_get('m_marketplace_delete') || $this->auth->acl_get('m_marketplace_feature') || $this->auth->acl_get('m_marketplace_reports');
	}

	private function active_ads_where($alias = 'a')
	{
		$now = time();
		$prefix = $alias !== '' ? $alias . '.' : '';
		return '(' . $prefix . 'ad_status = 1 AND (' . $prefix . 'ad_expires = 0 OR ' . $prefix . 'ad_expires >= ' . (int) $now . '))';
	}

	private function sold_ads_where($alias = 'a')
	{
		if (empty($this->config['marketplace_show_sold_ads']))
		{
			return '(1 = 0)';
		}

		$prefix = $alias !== '' ? $alias . '.' : '';
		$where = '(' . $prefix . 'ad_status = 2';
		$sold_days = isset($this->config['marketplace_sold_visible_days']) ? (int) $this->config['marketplace_sold_visible_days'] : 15;
		if ($sold_days > 0)
		{
			$cutoff = time() - ($sold_days * 86400);
			$where .= ' AND (' . $prefix . 'ad_sold_at = 0 OR ' . $prefix . 'ad_sold_at >= ' . (int) $cutoff . ')';
		}
		$where .= ')';

		return $where;
	}

	private function public_ads_where($alias = 'a')
	{
		return '(' . $this->active_ads_where($alias) . ' OR ' . $this->sold_ads_where($alias) . ')';
	}

	private function is_publicly_visible_ad($ad)
	{
		$status = isset($ad['ad_status']) ? (int) $ad['ad_status'] : -1;
		$expires = isset($ad['ad_expires']) ? (int) $ad['ad_expires'] : 0;
		if ($status === 1 && ($expires === 0 || $expires >= time()))
		{
			return true;
		}

		if ($status === 2 && !empty($this->config['marketplace_show_sold_ads']))
		{
			$sold_days = isset($this->config['marketplace_sold_visible_days']) ? (int) $this->config['marketplace_sold_visible_days'] : 15;
			$sold_at = isset($ad['ad_sold_at']) ? (int) $ad['ad_sold_at'] : 0;
			return $sold_days <= 0 || $sold_at === 0 || $sold_at >= (time() - ($sold_days * 86400));
		}

		return false;
	}

	private function get_listing_filters($forced_cat_id = 0)
	{
		$q = trim($this->request->variable('q', '', true));
		$location = trim($this->request->variable('location', '', true));
		$city = trim($this->request->variable('city', '', true));
		$region = trim($this->request->variable('region', '', true));
		$country = trim($this->request->variable('country', '', true));
		$distance = max(0, min(1000, (int) $this->request->variable('distance', 0)));
		$lat_raw = trim($this->request->variable('lat', '', true));
		$lng_raw = trim($this->request->variable('lng', '', true));
		$lat = is_numeric($lat_raw) ? (float) $lat_raw : 0.0;
		$lng = is_numeric($lng_raw) ? (float) $lng_raw : 0.0;
		$price_min_raw = trim($this->request->variable('price_min', '', true));
		$price_max_raw = trim($this->request->variable('price_max', '', true));
		$sort = $this->request->variable('sort', 'recent');
		$status = $this->request->variable('status', !empty($this->config['marketplace_show_sold_ads']) ? 'all' : 'active');

		$allowed_sorts = ['recent', 'updated', 'price_asc', 'price_desc', 'views'];
		if (!in_array($sort, $allowed_sorts, true))
		{
			$sort = 'recent';
		}

		$allowed_statuses = ['active', 'sold', 'all'];
		if (!in_array($status, $allowed_statuses, true))
		{
			$status = !empty($this->config['marketplace_show_sold_ads']) ? 'all' : 'active';
		}
		if ($status !== 'active' && empty($this->config['marketplace_show_sold_ads']))
		{
			$status = 'active';
		}

		$cat_id = (int) $forced_cat_id;
		if ($cat_id <= 0)
		{
			$cat_id = $this->request->variable('cat_id', 0);
		}

		$price_min = $this->parse_price_amount($price_min_raw);
		$price_max = $this->parse_price_amount($price_max_raw);
		if ($price_min > 0 && $price_max > 0 && $price_min > $price_max)
		{
			$tmp = $price_min;
			$price_min = $price_max;
			$price_max = $tmp;
		}

		return [
			'q'             => $q,
			'cat_id'        => max(0, (int) $cat_id),
			'location'      => $location,
			'city'          => $city,
			'region'        => $region,
			'country'       => $country,
			'distance'      => $distance,
			'lat_raw'       => $lat_raw,
			'lng_raw'       => $lng_raw,
			'lat'           => $lat,
			'lng'           => $lng,
			'price_min_raw' => $price_min_raw,
			'price_max_raw' => $price_max_raw,
			'price_min'     => $price_min,
			'price_max'     => $price_max,
			'ad_type'       => $this->sanitize_filter_ad_type($this->request->variable('ad_type', 0)),
			'ad_condition'  => $this->sanitize_filter_ad_condition($this->request->variable('ad_condition', -1)),
			'with_image'    => (int) $this->request->variable('with_image', 0) ? 1 : 0,
			'status'        => $status,
			'sort'          => $sort,
		];
	}

	private function build_listing_where($filters)
	{
		$where = [];

		switch ($filters['status'])
		{
			case 'sold':
				$where[] = $this->sold_ads_where('a');
			break;

			case 'all':
				$where[] = $this->public_ads_where('a');
			break;

			case 'active':
			default:
				$where[] = $this->active_ads_where('a');
			break;
		}

		if (!empty($filters['cat_id']))
		{
			$where[] = 'a.cat_id = ' . (int) $filters['cat_id'];
		}
		if ($filters['q'] !== '')
		{
			$where[] = '(' . $this->sql_like_contains('a.ad_title', $filters['q']) . ' OR ' . $this->sql_like_contains('a.ad_desc', $filters['q']) . ')';
		}
		if ($filters['location'] !== '')
		{
			$where[] = $this->sql_like_contains('a.ad_location', $filters['location']);
		}
		if (!empty($filters['city']) && $this->column_exists($this->table_ads, 'ad_city'))
		{
			$where[] = $this->sql_like_contains('a.ad_city', $filters['city']);
		}
		if (!empty($filters['region']) && $this->column_exists($this->table_ads, 'ad_region'))
		{
			$where[] = $this->sql_like_contains('a.ad_region', $filters['region']);
		}
		if (!empty($filters['country']) && $this->column_exists($this->table_ads, 'ad_country'))
		{
			$where[] = $this->sql_like_contains('a.ad_country', $filters['country']);
		}
		if (!empty($filters['distance']) && !empty($filters['lat']) && !empty($filters['lng']) && $this->column_exists($this->table_ads, 'ad_latitude') && $this->column_exists($this->table_ads, 'ad_longitude'))
		{
			$delta = max(0.01, ((float) $filters['distance']) / 111);
			$where[] = 'CAST(a.ad_latitude AS DECIMAL(10,6)) BETWEEN ' . ((float) $filters['lat'] - $delta) . ' AND ' . ((float) $filters['lat'] + $delta);
			$where[] = 'CAST(a.ad_longitude AS DECIMAL(10,6)) BETWEEN ' . ((float) $filters['lng'] - $delta) . ' AND ' . ((float) $filters['lng'] + $delta);
		}
		if (!empty($filters['ad_type']) && $this->column_exists($this->table_ads, 'ad_type'))
		{
			$where[] = 'a.ad_type = ' . (int) $filters['ad_type'];
		}
		if ((int) $filters['ad_condition'] >= 0 && $this->column_exists($this->table_ads, 'ad_condition'))
		{
			$where[] = 'a.ad_condition = ' . (int) $filters['ad_condition'];
		}
		if (!empty($filters['price_min']) && $this->column_exists($this->table_ads, 'ad_price_cents'))
		{
			$where[] = 'a.ad_price_cents >= ' . (int) $filters['price_min'];
		}
		if (!empty($filters['price_max']) && $this->column_exists($this->table_ads, 'ad_price_cents'))
		{
			$where[] = 'a.ad_price_cents <= ' . (int) $filters['price_max'];
		}
		if (!empty($filters['distance']))
		{
			$params['distance'] = (int) $filters['distance'];
		}
		if (!empty($filters['with_image']))
		{
			$where[] = 'EXISTS (SELECT 1 FROM ' . $this->table_images . ' mi WHERE mi.ad_id = a.ad_id)';
		}

		return implode(' AND ', $where);
	}

	private function sql_like_contains($column, $text)
	{
		return $column . ' ' . $this->db->sql_like_expression($this->db->get_any_char() . $this->db->sql_escape($text) . $this->db->get_any_char());
	}

	private function get_listing_order_by($sort)
	{
		$now = (int) time();
		$order_prefix = '';

		if ((!isset($this->config['marketplace_allow_featured']) || !empty($this->config['marketplace_allow_featured'])) && $this->column_exists($this->table_ads, 'ad_featured_until'))
		{
			$order_prefix .= 'CASE WHEN a.ad_featured_until >= ' . $now . ' THEN 1 ELSE 0 END DESC, ';
		}

		if ((!isset($this->config['marketplace_allow_boosted']) || !empty($this->config['marketplace_allow_boosted'])) && $this->column_exists($this->table_ads, 'ad_boosted_until'))
		{
			$order_prefix .= 'CASE WHEN a.ad_boosted_until >= ' . $now . ' THEN 1 ELSE 0 END DESC, ';
		}

		$bumped = $this->column_exists($this->table_ads, 'ad_last_bumped')
			? 'CASE WHEN a.ad_last_bumped > a.ad_created THEN a.ad_last_bumped ELSE a.ad_created END DESC'
			: 'a.ad_created DESC';

		switch ($sort)
		{
			case 'updated':
				return $order_prefix . 'a.ad_updated DESC, ' . $bumped;
			case 'price_asc':
				return $this->column_exists($this->table_ads, 'ad_price_cents') ? $order_prefix . 'a.ad_price_cents ASC, ' . $bumped : $order_prefix . $bumped;
			case 'price_desc':
				return $this->column_exists($this->table_ads, 'ad_price_cents') ? $order_prefix . 'a.ad_price_cents DESC, ' . $bumped : $order_prefix . $bumped;
			case 'views':
				return $order_prefix . 'a.ad_views DESC, ' . $bumped;
			case 'recent':
			default:
				return $order_prefix . $bumped;
		}
	}



	private function is_marketplace_verified_seller($user_id)
	{
		$sql = 'SELECT verified_seller FROM ' . $this->table_user_security . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$verified = (int) $this->db->sql_fetchfield('verified_seller');
		$this->db->sql_freeresult($result);
		return $verified === 1;
	}

	private function is_user_publish_blocked($user_id)
	{
		if (!$this->column_exists($this->table_ads, 'ad_suspicious'))
		{
			return false;
		}
		$sql = 'SELECT seller_suspended, publish_blocked FROM ' . $this->table_user_security . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $row && (!empty($row['seller_suspended']) || !empty($row['publish_blocked']));
	}

	private function find_forbidden_terms($text)
	{
		$matches = [];
		$text_clean = utf8_strtolower((string) $text);
		$sql = 'SELECT term_text FROM ' . $this->table_forbidden_terms . ' WHERE term_enabled = 1';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$term = trim((string) $row['term_text']);
			if ($term !== '' && strpos($text_clean, utf8_strtolower($term)) !== false)
			{
				$matches[] = $term;
			}
		}
		$this->db->sql_freeresult($result);
		return array_values(array_unique($matches));
	}

	private function get_effective_ad_limit($user_id)
	{
		$limit = isset($this->config['marketplace_max_ads_per_user']) ? (int) $this->config['marketplace_max_ads_per_user'] : 10;

		$sql = 'SELECT max_ads FROM ' . $this->table_user_limits . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$user_limit = $this->db->sql_fetchfield('max_ads');
		$this->db->sql_freeresult($result);
		if ($user_limit !== false && $user_limit !== null)
		{
			return (int) $user_limit;
		}

		$sql = 'SELECT MIN(l.max_ads) AS max_ads
			FROM ' . USER_GROUP_TABLE . ' ug
			INNER JOIN ' . $this->table_group_limits . ' l ON l.group_id = ug.group_id
			WHERE ug.user_id = ' . (int) $user_id . '
				AND ug.user_pending = 0';
		$result = $this->db->sql_query($sql);
		$group_limit = $this->db->sql_fetchfield('max_ads');
		$this->db->sql_freeresult($result);
		if ($group_limit !== false && $group_limit !== null)
		{
			return (int) $group_limit;
		}

		return $limit;
	}

	private function record_ad_edit_history($ad_id, array $old_ad, array $new_ad)
	{
		if (!$this->column_exists($this->table_ads, 'ad_suspicious'))
		{
			return;
		}
		$fields = ['ad_title', 'ad_desc', 'ad_price', 'ad_location', 'ad_phone', 'ad_quantity', 'cat_id', 'ad_type', 'ad_condition'];
		$changes = [];
		foreach ($fields as $field)
		{
			$old = isset($old_ad[$field]) ? (string) $old_ad[$field] : '';
			$new = isset($new_ad[$field]) ? (string) $new_ad[$field] : '';
			if ($old !== $new)
			{
				$changes[] = $field;
			}
		}
		if (empty($changes))
		{
			return;
		}
		$sql_ary = [
			'ad_id' => (int) $ad_id,
			'user_id' => (int) $this->user->data['user_id'],
			'edit_time' => time(),
			'edit_summary' => implode(', ', $changes),
			'old_data' => json_encode(array_intersect_key($old_ad, array_flip($fields))),
			'new_data' => json_encode(array_intersect_key($new_ad, array_flip($fields))),
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_ad_edit_history . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
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

	private function get_filter_url_params($filters, $omit_cat_id = false)
	{
		$params = [];
		foreach (['q', 'location', 'city', 'region', 'country', 'lat_raw', 'lng_raw', 'price_min_raw', 'price_max_raw'] as $key)
		{
			if ($filters[$key] !== '')
			{
				$url_key = str_replace('_raw', '', $key);
				$params[$url_key] = $filters[$key];
			}
		}
		if (!$omit_cat_id && !empty($filters['cat_id']))
		{
			$params['cat_id'] = (int) $filters['cat_id'];
		}
		if (!empty($filters['ad_type']))
		{
			$params['ad_type'] = (int) $filters['ad_type'];
		}
		if ((int) $filters['ad_condition'] >= 0)
		{
			$params['ad_condition'] = (int) $filters['ad_condition'];
		}
		if (!empty($filters['distance']))
		{
			$params['distance'] = (int) $filters['distance'];
		}
		if (!empty($filters['with_image']))
		{
			$params['with_image'] = 1;
		}
		if ($filters['status'] !== (!empty($this->config['marketplace_show_sold_ads']) ? 'all' : 'active'))
		{
			$params['status'] = $filters['status'];
		}
		if ($filters['sort'] !== 'recent')
		{
			$params['sort'] = $filters['sort'];
		}
		return $params;
	}

	private function append_url_params($url, $params)
	{
		if (empty($params))
		{
			return $url;
		}
		$separator = (strpos($url, '?') === false) ? '?' : '&amp;';
		return $url . $separator . http_build_query($params, '', '&amp;');
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

	private function expire_ad_if_needed(&$ad)
	{
		$now = time();
		if ((int) $ad['ad_status'] === 1 && !empty($ad['ad_expires']) && (int) $ad['ad_expires'] < $now)
		{
			$sql_ary = [
				'ad_status'     => 3,
				'ad_expired_at' => $now,
				'ad_updated'    => $now,
			];
			$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . (int) $ad['ad_id']);
			$ad['ad_status'] = 3;
			$ad['ad_expired_at'] = $now;
			$ad['ad_updated'] = $now;
		}
	}

	private function prepare_ad_for_display(&$ad)
	{
		if (isset($ad['cat_name']))
		{
			$ad['cat_name'] = $this->translate_category_text($ad['cat_name']);
		}
		if (isset($ad['cat_desc']))
		{
			$ad['cat_desc'] = $this->translate_category_text($ad['cat_desc']);
		}

		$ad['STATUS_LANG'] = $this->get_status_lang((int) $ad['ad_status']);
		$ad['AD_TYPE_LANG'] = $this->get_ad_type_lang(isset($ad['ad_type']) ? (int) $ad['ad_type'] : 1);
		$ad['AD_CONDITION_LANG'] = $this->get_ad_condition_lang(isset($ad['ad_condition']) ? (int) $ad['ad_condition'] : 0);
		$ad['DELIVERY_OPTIONS_LANG'] = $this->format_delivery_options(isset($ad['ad_delivery_options']) ? $ad['ad_delivery_options'] : '');
		$ad['LOCATION_DISPLAY'] = $this->format_ad_location($ad);
		$ad['AD_PRICE_TYPE_LANG'] = $this->get_price_type_lang(isset($ad['ad_price_type']) ? (int) $ad['ad_price_type'] : 2);
		$ad['ad_quantity'] = isset($ad['ad_quantity']) ? max(0, (int) $ad['ad_quantity']) : 1;
		$ad['AD_QUANTITY_LANG'] = $this->format_quantity($ad['ad_quantity']);
		$ad['S_IN_STOCK'] = $ad['ad_quantity'] > 0;
		$ad['AD_EXPIRES_DISPLAY'] = '';
		$ad['AD_EXPIRES_IN_LANG'] = '';
		$ad['AD_SOLD_AT_DISPLAY'] = '';
		$ad['AD_EXPIRED_AT_DISPLAY'] = '';
		$ad['AD_APPROVED_AT_DISPLAY'] = '';
		$ad['AD_HIDDEN_AT_DISPLAY'] = '';
		$ad['AD_FEATURED_UNTIL_DISPLAY'] = '';
		$ad['AD_BOOSTED_UNTIL_DISPLAY'] = '';
		$ad['AD_LAST_BUMPED_DISPLAY'] = '';
		$ad['AD_LAST_RENEWED_DISPLAY'] = '';
		$ad['AD_BUMPED_AT_DISPLAY'] = '';
		$ad['AD_NEXT_BUMP_DISPLAY'] = '';
		$ad['S_IS_FEATURED'] = (!isset($this->config['marketplace_allow_featured']) || !empty($this->config['marketplace_allow_featured'])) && !empty($ad['ad_featured_until']) && (int) $ad['ad_featured_until'] >= time();
		$ad['S_IS_BOOSTED'] = (!isset($this->config['marketplace_allow_boosted']) || !empty($this->config['marketplace_allow_boosted'])) && !empty($ad['ad_boosted_until']) && (int) $ad['ad_boosted_until'] >= time();

		$expires = isset($ad['ad_expires']) ? (int) $ad['ad_expires'] : 0;
		if ($expires > 0)
		{
			$ad['AD_EXPIRES_DISPLAY'] = $this->user->format_date($expires);
			$days = (int) ceil(($expires - time()) / 86400);
			if ($days <= 0 && (int) $ad['ad_status'] === 1)
			{
				$ad['AD_EXPIRES_IN_LANG'] = $this->language->lang('MARKETPLACE_EXPIRES_TODAY');
			}
			else if ($days > 0 && (int) $ad['ad_status'] === 1)
			{
				$ad['AD_EXPIRES_IN_LANG'] = $this->language->lang('MARKETPLACE_EXPIRES_IN_DAYS', $days);
			}
		}

		$sold_at = isset($ad['ad_sold_at']) ? (int) $ad['ad_sold_at'] : 0;
		if ($sold_at > 0)
		{
			$ad['AD_SOLD_AT_DISPLAY'] = $this->user->format_date($sold_at);
		}

		$expired_at = isset($ad['ad_expired_at']) ? (int) $ad['ad_expired_at'] : 0;
		if ($expired_at > 0)
		{
			$ad['AD_EXPIRED_AT_DISPLAY'] = $this->user->format_date($expired_at);
		}

		$approved_at = isset($ad['ad_approved_at']) ? (int) $ad['ad_approved_at'] : 0;
		if ($approved_at > 0)
		{
			$ad['AD_APPROVED_AT_DISPLAY'] = $this->user->format_date($approved_at);
		}

		$hidden_at = isset($ad['ad_hidden_at']) ? (int) $ad['ad_hidden_at'] : 0;
		if ($hidden_at > 0)
		{
			$ad['AD_HIDDEN_AT_DISPLAY'] = $this->user->format_date($hidden_at);
		}

		$featured_until = isset($ad['ad_featured_until']) ? (int) $ad['ad_featured_until'] : 0;
		if ($featured_until > 0)
		{
			$ad['AD_FEATURED_UNTIL_DISPLAY'] = $this->user->format_date($featured_until);
		}

		$boosted_until = isset($ad['ad_boosted_until']) ? (int) $ad['ad_boosted_until'] : 0;
		if ($boosted_until > 0)
		{
			$ad['AD_BOOSTED_UNTIL_DISPLAY'] = $this->user->format_date($boosted_until);
		}

		$last_renewed = isset($ad['ad_last_renewed']) ? (int) $ad['ad_last_renewed'] : 0;
		if ($last_renewed > 0)
		{
			$ad['AD_LAST_RENEWED_DISPLAY'] = $this->user->format_date($last_renewed);
		}

		$last_bumped = isset($ad['ad_last_bumped']) ? (int) $ad['ad_last_bumped'] : 0;
		if ($last_bumped > 0)
		{
			$ad['AD_LAST_BUMPED_DISPLAY'] = $this->user->format_date($last_bumped);
			$ad['AD_BUMPED_AT_DISPLAY'] = $ad['AD_LAST_BUMPED_DISPLAY'];
		}

		if (!$this->can_bump_ad($ad))
		{
			$next_bump = $this->next_bump_time($ad);
			if ($next_bump > time())
			{
				$ad['AD_NEXT_BUMP_DISPLAY'] = $this->user->format_date($next_bump);
			}
		}
	}

	private function build_whatsapp_url($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);

		if ($digits === '')
		{
			return '';
		}

		$digits = ltrim($digits, '0');

		// Brazilian local numbers are commonly entered without the country code.
		// WhatsApp wa.me links require the international format without +, spaces or punctuation.
		if (strlen($digits) === 10 || strlen($digits) === 11)
		{
			$digits = '55' . $digits;
		}

		return 'https://wa.me/' . $digits;
	}

	private function can_edit_ad($ad)
	{
		$is_owner = ((int) $ad['user_id'] === (int) $this->user->data['user_id']) && (int) $this->user->data['user_id'] !== ANONYMOUS;
		return ($is_owner && $this->auth->acl_get('u_marketplace_edit_own')) || $this->auth->acl_get('m_marketplace_edit');
	}

	private function can_delete_ad($ad)
	{
		$is_owner = ((int) $ad['user_id'] === (int) $this->user->data['user_id']) && (int) $this->user->data['user_id'] !== ANONYMOUS;
		return ($is_owner && $this->auth->acl_get('u_marketplace_delete_own')) || $this->auth->acl_get('m_marketplace_delete');
	}

	private function can_approve_ad($ad)
	{
		return ((int) $ad['ad_status'] === 0) && $this->auth->acl_get('m_marketplace_approve');
	}

	private function can_mark_sold($ad)
	{
		$is_owner = ((int) $ad['user_id'] === (int) $this->user->data['user_id']) && (int) $this->user->data['user_id'] !== ANONYMOUS;
		return ((int) $ad['ad_status'] === 1) && (($is_owner && $this->auth->acl_get('u_marketplace_edit_own')) || $this->auth->acl_get('m_marketplace_edit'));
	}

	private function can_manage_stock($ad)
	{
		$is_owner = ((int) $this->user->data['user_id'] !== ANONYMOUS && (int) $ad['user_id'] === (int) $this->user->data['user_id']);
		return in_array((int) $ad['ad_status'], [1, 2], true) && (($is_owner && $this->auth->acl_get('u_marketplace_edit_own')) || $this->auth->acl_get('m_marketplace_edit'));
	}

	private function can_renew_ad($ad)
	{
		$is_owner = ((int) $ad['user_id'] === (int) $this->user->data['user_id']) && (int) $this->user->data['user_id'] !== ANONYMOUS;
		$status = (int) $ad['ad_status'];
		return in_array($status, [1, 3, 4], true) && (($is_owner && $this->auth->acl_get('u_marketplace_edit_own')) || $this->auth->acl_get('m_marketplace_edit'));
	}

	private function can_pause_ad($ad)
	{
		$is_owner = ((int) $ad['user_id'] === (int) $this->user->data['user_id']) && (int) $this->user->data['user_id'] !== ANONYMOUS;
		return ((int) $ad['ad_status'] === 1) && (($is_owner && $this->auth->acl_get('u_marketplace_edit_own')) || $this->auth->acl_get('m_marketplace_edit'));
	}

	private function get_categories($only_enabled = false)
	{
		$where = $only_enabled ? 'WHERE cat_enabled = 1' : '';
		$sql = 'SELECT * FROM ' . $this->table_cats . ' ' . $where . ' ORDER BY cat_order, cat_id';
		$result = $this->db->sql_query($sql);
		$cats = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row = $this->prepare_category_for_display($row);
			$row['U_CATEGORY'] = $this->helper->route('mundophpbb_marketplace_category', ['cat_id' => $row['cat_id']]);
			$cats[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $cats;
	}

	private function get_category($cat_id)
	{
		$sql = 'SELECT * FROM ' . $this->table_cats . ' WHERE cat_id = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql);
		$cat = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $cat ? $this->prepare_category_for_display($cat) : $cat;
	}

	private function prepare_category_for_display(array $category)
	{
		$category['CAT_NAME_RAW'] = isset($category['cat_name']) ? $category['cat_name'] : '';
		$category['CAT_DESC_RAW'] = isset($category['cat_desc']) ? $category['cat_desc'] : '';
		$category['CAT_NAME_DISPLAY'] = $this->translate_category_text($category['CAT_NAME_RAW']);
		$category['CAT_DESC_DISPLAY'] = $this->translate_category_text($category['CAT_DESC_RAW']);
		$category['cat_name'] = $category['CAT_NAME_DISPLAY'];
		$category['cat_desc'] = $category['CAT_DESC_DISPLAY'];

		return $category;
	}

	private function translate_category_text($text)
	{
		$text = (string) $text;
		if (strpos($text, 'MARKETPLACE_CAT_') === 0)
		{
			return $this->language->lang($text);
		}

		return $text;
	}

	private function format_price($ad)
	{
		if (!$this->config['marketplace_enable_price'])
		{
			return '';
		}

		$currency = !empty($ad['ad_currency']) ? $ad['ad_currency'] : $this->config['marketplace_currency_default'];
		$price_type = isset($ad['ad_price_type']) ? (int) $ad['ad_price_type'] : 2;
		$amount = isset($ad['ad_price_cents']) ? (int) $ad['ad_price_cents'] : $this->parse_price_amount(isset($ad['ad_price']) ? $ad['ad_price'] : '');

		switch ($price_type)
		{
			case 1:
				return $amount > 0 ? $currency . ' ' . $this->format_price_amount($amount) : $this->language->lang('MARKETPLACE_PRICE_NEGOTIABLE');
			case 2:
				return $amount > 0 ? $currency . ' ' . $this->format_price_amount($amount) . ' (' . $this->language->lang('MARKETPLACE_PRICE_TYPE_NEGOTIABLE') . ')' : $this->language->lang('MARKETPLACE_PRICE_NEGOTIABLE');
			case 3:
				return $this->language->lang('MARKETPLACE_PRICE_TYPE_FREE');
			case 4:
				return $this->language->lang('MARKETPLACE_PRICE_TYPE_ON_REQUEST');
		}

		return $this->language->lang('MARKETPLACE_PRICE_NEGOTIABLE');
	}

	private function parse_price_amount($price)
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
			$decimal = ($last_comma > $last_dot) ? ',' : '.';
			$thousand = ($decimal === ',') ? '.' : ',';
			$price = str_replace($thousand, '', $price);
			$price = str_replace($decimal, '.', $price);
		}
		else if ($last_comma !== false)
		{
			$price = preg_match('/,\d{1,2}$/', $price) ? str_replace(',', '.', $price) : str_replace(',', '', $price);
		}
		else if ($last_dot !== false && !preg_match('/\.\d{1,2}$/', $price))
		{
			$price = str_replace('.', '', $price);
		}

		if (!is_numeric($price) || (float) $price < 0)
		{
			return 0;
		}

		return (int) round(((float) $price) * 100);
	}

	private function format_price_amount($amount)
	{
		return number_format(((int) $amount) / 100, 2, ',', '.');
	}

	private function sanitize_ad_type($value)
	{
		$value = (int) $value;
		return in_array($value, [1, 2, 3, 4, 5, 6], true) ? $value : 1;
	}

	private function sanitize_filter_ad_type($value)
	{
		$value = (int) $value;
		return in_array($value, [0, 1, 2, 3, 4, 5, 6], true) ? $value : 0;
	}

	private function sanitize_ad_condition($value)
	{
		$value = (int) $value;
		return in_array($value, [0, 1, 2, 3], true) ? $value : 0;
	}

	private function sanitize_filter_ad_condition($value)
	{
		$value = (int) $value;
		return in_array($value, [-1, 0, 1, 2, 3], true) ? $value : -1;
	}

	private function sanitize_price_type($value)
	{
		$value = (int) $value;
		return in_array($value, [1, 2, 3, 4], true) ? $value : 2;
	}

	private function sanitize_quantity($value)
	{
		$value = (int) $value;
		return max(0, min(999999, $value));
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

	private function get_ad_type_lang($type)
	{
		$keys = [
			1 => 'MARKETPLACE_TYPE_SELL',
			2 => 'MARKETPLACE_TYPE_BUY',
			3 => 'MARKETPLACE_TYPE_TRADE',
			4 => 'MARKETPLACE_TYPE_SERVICE',
			5 => 'MARKETPLACE_TYPE_RENT',
			6 => 'MARKETPLACE_TYPE_WANTED',
		];
		$type = $this->sanitize_ad_type($type);
		return $this->language->lang($keys[$type]);
	}

	private function get_ad_condition_lang($condition)
	{
		$keys = [
			0 => 'MARKETPLACE_CONDITION_NA',
			1 => 'MARKETPLACE_CONDITION_NEW',
			2 => 'MARKETPLACE_CONDITION_USED',
			3 => 'MARKETPLACE_CONDITION_REFURBISHED',
		];
		$condition = $this->sanitize_ad_condition($condition);
		return $this->language->lang($keys[$condition]);
	}

	private function get_price_type_lang($price_type)
	{
		$keys = [
			1 => 'MARKETPLACE_PRICE_TYPE_FIXED',
			2 => 'MARKETPLACE_PRICE_TYPE_NEGOTIABLE',
			3 => 'MARKETPLACE_PRICE_TYPE_FREE',
			4 => 'MARKETPLACE_PRICE_TYPE_ON_REQUEST',
		];
		$price_type = $this->sanitize_price_type($price_type);
		return $this->language->lang($keys[$price_type]);
	}

	private function get_ad_type_options($selected = 1, $include_all = false)
	{
		$options = [];
		if ($include_all)
		{
			$options[] = ['VALUE' => 0, 'LABEL' => $this->language->lang('MARKETPLACE_ALL_TYPES'), 'SELECTED' => ((int) $selected === 0)];
		}
		foreach ([1, 2, 3, 4, 5, 6] as $value)
		{
			$options[] = ['VALUE' => $value, 'LABEL' => $this->get_ad_type_lang($value), 'SELECTED' => ((int) $selected === $value)];
		}
		return $options;
	}

	private function get_ad_condition_options($selected = 0, $include_all = false)
	{
		$options = [];
		if ($include_all)
		{
			$options[] = ['VALUE' => -1, 'LABEL' => $this->language->lang('MARKETPLACE_ALL_CONDITIONS'), 'SELECTED' => ((int) $selected === -1)];
		}
		foreach ([0, 1, 2, 3] as $value)
		{
			$options[] = ['VALUE' => $value, 'LABEL' => $this->get_ad_condition_lang($value), 'SELECTED' => ((int) $selected === $value)];
		}
		return $options;
	}

	private function get_price_type_options($selected = 2)
	{
		$options = [];
		foreach ([1, 2, 3, 4] as $value)
		{
			$options[] = ['VALUE' => $value, 'LABEL' => $this->get_price_type_lang($value), 'SELECTED' => ((int) $selected === $value)];
		}
		return $options;
	}

	private function get_public_status_options($selected = 'active')
	{
		$options = [
			'active' => 'MARKETPLACE_STATUS_ACTIVE_ONLY',
		];

		if (!empty($this->config['marketplace_show_sold_ads']))
		{
			$options['all'] = 'MARKETPLACE_STATUS_ACTIVE_AND_SOLD';
			$options['sold'] = 'MARKETPLACE_STATUS_SOLD_ONLY';
		}

		$rows = [];
		foreach ($options as $value => $key)
		{
			$rows[] = ['VALUE' => $value, 'LABEL' => $this->language->lang($key), 'SELECTED' => ($selected === $value)];
		}
		return $rows;
	}

	private function get_sort_options($selected = 'recent')
	{
		$options = [
			'recent'     => 'MARKETPLACE_SORT_RECENT',
			'updated'    => 'MARKETPLACE_SORT_UPDATED',
			'price_asc'  => 'MARKETPLACE_SORT_PRICE_ASC',
			'price_desc' => 'MARKETPLACE_SORT_PRICE_DESC',
			'views'      => 'MARKETPLACE_SORT_VIEWS',
		];
		$rows = [];
		foreach ($options as $value => $key)
		{
			$rows[] = ['VALUE' => $value, 'LABEL' => $this->language->lang($key), 'SELECTED' => ($selected === $value)];
		}
		return $rows;
	}

	private function get_main_image($ad_id)
	{
		$sql = 'SELECT image_id FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id . ' ORDER BY image_is_main DESC, image_order ASC';
		$result = $this->db->sql_query_limit($sql, 1);
		$image_id = (int) $this->db->sql_fetchfield('image_id');
		$this->db->sql_freeresult($result);

		return $image_id ? $this->get_image_url($image_id) : '';
	}



	private function sanitize_coordinate($value, $limit)
	{
		$value = trim((string) $value);
		if ($value === '' || !is_numeric($value))
		{
			return '';
		}
		$float = (float) $value;
		$limit = (float) $limit;
		if ($float < -$limit || $float > $limit)
		{
			return '';
		}
		return number_format($float, 6, '.', '');
	}

	private function sanitize_delivery_options($values)
	{
		if (!is_array($values))
		{
			$values = explode(',', (string) $values);
		}
		$allowed = ['shipping', 'pickup', 'both'];
		$clean = [];
		foreach ($values as $value)
		{
			$value = trim((string) $value);
			if (in_array($value, $allowed, true))
			{
				$clean[] = $value;
			}
		}
		$clean = array_values(array_unique($clean));
		return implode(',', $clean);
	}

	private function get_marketplace_location_options($selected = '', $include_defaults = true)
	{
		$defaults = $include_defaults ? [
			'Retirada no local',
			'Entrega combinada',
			'Envio pelos Correios',
			'Envio por transportadora',
			'Online',
		] : [];

		return $this->build_distinct_text_options('ad_location', $selected, $defaults);
	}

	private function get_marketplace_region_options($selected = '', $include_defaults = true)
	{
		$defaults = $include_defaults ? [
			'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG',
			'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
		] : [];

		return $this->build_distinct_text_options('ad_region', $selected, $defaults);
	}

	private function build_distinct_text_options($column, $selected = '', array $defaults = [])
	{
		$allowed_columns = ['ad_location', 'ad_region'];
		if (!in_array($column, $allowed_columns, true))
		{
			return [];
		}

		$values = [];
		foreach ($defaults as $value)
		{
			$value = trim((string) $value);
			if ($value !== '')
			{
				$values[$value] = $value;
			}
		}

		$selected = trim((string) $selected);
		if ($selected !== '')
		{
			$values[$selected] = $selected;
		}

		if ($this->column_exists($this->table_ads, $column))
		{
			$sql = 'SELECT DISTINCT ' . $column . ' AS option_value
				FROM ' . $this->table_ads . "
				WHERE " . $column . " <> ''
				ORDER BY " . $column . ' ASC';
			$result = $this->db->sql_query_limit($sql, 100);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$value = trim((string) $row['option_value']);
				if ($value !== '')
				{
					$values[$value] = $value;
				}
			}
			$this->db->sql_freeresult($result);
		}

		$options = [];
		foreach ($values as $value => $label)
		{
			$options[] = [
				'VALUE' => $value,
				'LABEL' => $label,
				'SELECTED' => ($selected !== '' && $selected === $value),
			];
		}

		return $options;
	}

	private function get_delivery_options($selected = '')
	{
		$selected = array_filter(explode(',', (string) $selected));
		$options = [
			'shipping' => 'MARKETPLACE_DELIVERY_SHIPPING',
			'pickup' => 'MARKETPLACE_DELIVERY_PICKUP',
			'both' => 'MARKETPLACE_DELIVERY_BOTH',
		];
		$rows = [];
		foreach ($options as $value => $key)
		{
			$rows[] = ['VALUE' => $value, 'LABEL' => $this->language->lang($key), 'CHECKED' => in_array($value, $selected, true)];
		}
		return $rows;
	}

	private function format_delivery_options($selected = '')
	{
		$selected = array_filter(explode(',', (string) $selected));
		if (empty($selected))
		{
			return '';
		}
		$labels = [];
		foreach ($this->get_delivery_options(implode(',', $selected)) as $option)
		{
			if (!empty($option['CHECKED']))
			{
				$labels[] = $option['LABEL'];
			}
		}
		return implode(', ', $labels);
	}

	private function compose_location($city, $region, $country)
	{
		$parts = array_filter([trim((string) $city), trim((string) $region), trim((string) $country)]);
		return implode(' - ', $parts);
	}

	private function format_ad_location($ad)
	{
		$parts = [];
		foreach (['ad_city', 'ad_region', 'ad_country'] as $key)
		{
			if (!empty($ad[$key])) { $parts[] = $ad[$key]; }
		}
		if (empty($parts) && !empty($ad['ad_location']))
		{
			return $ad['ad_location'];
		}
		return implode(' - ', $parts);
	}

	private function build_map_query($ad)
	{
		if (!empty($ad['ad_latitude']) && !empty($ad['ad_longitude']))
		{
			return $ad['ad_latitude'] . ',' . $ad['ad_longitude'];
		}
		$parts = [];
		foreach (['ad_city', 'ad_region', 'ad_country'] as $key)
		{
			if (!empty($ad[$key])) { $parts[] = $ad[$key]; }
		}
		if (empty($parts) && !empty($ad['ad_location'])) { $parts[] = $ad['ad_location']; }
		return implode(', ', $parts);
	}


	private function build_osm_embed_url($lat, $lng)
	{
		$lat = (float) $lat;
		$lng = (float) $lng;
		$delta = 0.01;
		$bbox = ($lng - $delta) . ',' . ($lat - $delta) . ',' . ($lng + $delta) . ',' . ($lat + $delta);
		return 'https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode($bbox) . '&layer=mapnik&marker=' . rawurlencode($lat . ',' . $lng);
	}

	private function custom_fields_available()
	{
		return !empty($this->table_category_fields) && !empty($this->table_ad_field_values);
	}

	private function get_category_custom_fields($cat_id)
	{
		$cat_id = (int) $cat_id;
		if ($cat_id <= 0 || !$this->custom_fields_available()) { return []; }
		$sql = 'SELECT * FROM ' . $this->table_category_fields . ' WHERE cat_id = ' . $cat_id . ' ORDER BY field_order ASC, field_id ASC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result)) { $rows[] = $row; }
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_ad_custom_field_values($ad_id)
	{
		$ad_id = (int) $ad_id;
		if ($ad_id <= 0 || !$this->custom_fields_available()) { return []; }
		$sql = 'SELECT field_id, field_value FROM ' . $this->table_ad_field_values . ' WHERE ad_id = ' . $ad_id;
		$result = $this->db->sql_query($sql);
		$values = [];
		while ($row = $this->db->sql_fetchrow($result)) { $values[(int) $row['field_id']] = $row['field_value']; }
		$this->db->sql_freeresult($result);
		return $values;
	}

	private function get_category_field_groups($selected_cat_id = 0, array $values = [])
	{
		if (!$this->custom_fields_available()) { return []; }
		$sql = 'SELECT f.*, c.cat_name FROM ' . $this->table_category_fields . ' f LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = f.cat_id ORDER BY c.cat_order ASC, f.field_order ASC, f.field_id ASC';
		$result = $this->db->sql_query($sql);
		$groups = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$cid = (int) $row['cat_id'];
			if (!isset($groups[$cid]))
			{
				$groups[$cid] = ['CAT_ID' => $cid, 'CAT_NAME' => $this->translate_category_text($row['cat_name']), 'S_ACTIVE' => ($cid === (int) $selected_cat_id), 'FIELDS' => []];
			}
			$value = isset($values[(int) $row['field_id']]) ? $values[(int) $row['field_id']] : '';
			$row['FIELD_VALUE'] = $value;
			$row['S_REQUIRED'] = !empty($row['field_required']);
			$groups[$cid]['FIELDS'][] = $row;
		}
		$this->db->sql_freeresult($result);
		return array_values($groups);
	}

	private function validate_custom_fields($cat_id, array $submitted, array &$errors)
	{
		foreach ($this->get_category_custom_fields($cat_id) as $field)
		{
			$value = isset($submitted[(int) $field['field_id']]) ? trim((string) $submitted[(int) $field['field_id']]) : '';
			if (!empty($field['field_required']) && $value === '')
			{
				$errors[] = $this->language->lang('MARKETPLACE_CUSTOM_FIELD_REQUIRED', $field['field_label']);
			}
		}
	}

	private function save_custom_field_values($ad_id, $cat_id, array $submitted)
	{
		$ad_id = (int) $ad_id;
		if ($ad_id <= 0 || !$this->custom_fields_available()) { return; }
		$fields = $this->get_category_custom_fields((int) $cat_id);
		$this->db->sql_query('DELETE FROM ' . $this->table_ad_field_values . ' WHERE ad_id = ' . $ad_id);
		foreach ($fields as $field)
		{
			$field_id = (int) $field['field_id'];
			$value = isset($submitted[$field_id]) ? trim((string) $submitted[$field_id]) : '';
			if ($value === '') { continue; }
			$sql_ary = ['ad_id' => $ad_id, 'field_id' => $field_id, 'field_value' => $value];
			$this->db->sql_query('INSERT INTO ' . $this->table_ad_field_values . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
		}
	}

	private function get_ad_custom_fields_for_display($ad_id)
	{
		$ad_id = (int) $ad_id;
		if ($ad_id <= 0 || !$this->custom_fields_available()) { return []; }
		$sql = 'SELECT f.field_label, v.field_value FROM ' . $this->table_ad_field_values . ' v INNER JOIN ' . $this->table_category_fields . ' f ON f.field_id = v.field_id WHERE v.ad_id = ' . $ad_id . ' ORDER BY f.field_order ASC, f.field_id ASC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result)) { $rows[] = $row; }
		$this->db->sql_freeresult($result);
		return $rows;
	}

	private function get_more_ads_from_user($user_id, $current_ad_id, $limit = 6)
	{
		$user_id = (int) $user_id;
		$current_ad_id = (int) $current_ad_id;
		$limit = max(1, min(12, (int) $limit));

		if ($user_id <= 0)
		{
			return [];
		}

		$sql = 'SELECT a.*, u.username, u.user_colour, c.cat_name
				FROM ' . $this->table_ads . ' a
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
				LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
				WHERE a.user_id = ' . $user_id . '
					AND a.ad_id <> ' . $current_ad_id . '
					AND ' . $this->public_ads_where('a') . '
				ORDER BY a.ad_last_bumped DESC, a.ad_created DESC';
		$result = $this->db->sql_query_limit($sql, $limit);

		$ads = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$row['U_POSTER'] = \append_sid("{$this->root_path}memberlist.{$this->php_ext}", ['mode' => 'viewprofile', 'u' => (int) $row['user_id']]);
			$row['AD_PRICE_DISPLAY'] = $this->format_price($row);
			$row['MAIN_IMAGE'] = $this->get_main_image((int) $row['ad_id']);
			$this->prepare_ad_for_display($row);
			$ads[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $ads;
	}

	private function get_ad_images($ad_id)
	{
		$sql = 'SELECT * FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id . ' ORDER BY image_is_main DESC, image_order ASC';
		$result = $this->db->sql_query($sql);
		$images = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['SRC'] = $this->get_image_url((int) $row['image_id']);
			$row['S_IS_MAIN'] = !empty($row['image_is_main']);
			$images[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $images;
	}

	private function get_image_url($image_id)
	{
		return $this->helper->route('mundophpbb_marketplace_image', [
			'image_id' => (int) $image_id,
			'v' => time(),
		]);
	}

	private function get_status_lang($status)
	{
		$status_keys = [
			0 => 'MARKETPLACE_STATUS_PENDING',
			1 => 'MARKETPLACE_STATUS_ACTIVE',
			2 => 'MARKETPLACE_STATUS_SOLD',
			3 => 'MARKETPLACE_STATUS_EXPIRED',
			4 => 'MARKETPLACE_STATUS_HIDDEN',
		];

		$key = isset($status_keys[(int) $status]) ? $status_keys[(int) $status] : 'MARKETPLACE_STATUS_UNKNOWN';
		return $this->language->lang($key);
	}

	private function image_not_found_response()
	{
		return new \Symfony\Component\HttpFoundation\Response('', 404, [
			'Cache-Control' => 'no-store',
			'X-Content-Type-Options' => 'nosniff',
		]);
	}

	private function image_placeholder_response()
	{
		$this->language->add_lang('common', 'mundophpbb/marketplace');
		$text = htmlspecialchars($this->language->lang('MARKETPLACE_IMAGE_UNAVAILABLE'), ENT_XML1, 'UTF-8');
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="420" viewBox="0 0 640 420" role="img" aria-label="' . $text . '"><rect width="640" height="420" rx="18" fill="#f1f3f5"/><path d="M156 294l92-112 72 86 48-58 116 84H156z" fill="#d2d7de"/><circle cx="410" cy="142" r="36" fill="#d2d7de"/><text x="320" y="355" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="24" fill="#6b7280">' . $text . '</text></svg>';

		$response = new \Symfony\Component\HttpFoundation\Response($svg, 200);
		$response->headers->set('Content-Type', 'image/svg+xml; charset=UTF-8');
		$response->headers->set('X-Content-Type-Options', 'nosniff');
		$response->headers->set('Cache-Control', 'private, no-cache');

		return $response;
	}

	private function is_safe_image_filename($filename)
	{
		return $filename !== '' && basename($filename) === $filename && (bool) preg_match('/^[A-Za-z0-9_.-]+\.(jpe?g|png|gif|webp)$/i', $filename);
	}

	private function detect_image_mime($path)
	{
		$info = @getimagesize($path);
		if (!is_array($info) || empty($info['mime']))
		{
			return '';
		}

		$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
		return in_array($info['mime'], $allowed, true) ? $info['mime'] : '';
	}

	private function ensure_upload_path()
	{
		if (!is_dir($this->upload_path))
		{
			@mkdir($this->upload_path, 0755, true);
		}

		if (is_dir($this->upload_path) && !file_exists($this->upload_path . 'index.htm'))
		{
			@file_put_contents($this->upload_path . 'index.htm', '');
		}

		$htaccess = $this->upload_path . '.htaccess';
		if (is_dir($this->upload_path) && !file_exists($htaccess))
		{
			@file_put_contents($htaccess, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
		}
	}

	private function render_description($text)
	{
		$text = \censor_text($text);
		return nl2br(\make_clickable($text));
	}

	/**
	 * Handle multiple image uploads.
	 * Returns array of saved filenames.
	 */
	private function handle_image_uploads(&$errors, $available_slots = null)
	{
		$this->ensure_upload_path();

		$allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
		$max_size = 2 * 1024 * 1024;

		$files = $this->request->variable(
			'images',
			['name' => ['']],
			true,
			\phpbb\request\request_interface::FILES
		);
		$saved = [];

		if (empty($files) || !isset($files['name']) || !is_array($files['name']))
		{
			return $saved;
		}

		foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $key)
		{
			if (!isset($files[$key]) || !is_array($files[$key]))
			{
				$files[$key] = [];
			}
		}

		$attempts = $this->count_upload_attempts($files);
		if ($attempts === 0)
		{
			return $saved;
		}

		$max_allowed = (int) $this->config['marketplace_max_images'];
		$available_slots = ($available_slots === null) ? $max_allowed : min($max_allowed, max(0, (int) $available_slots));
		if ($available_slots <= 0 || $attempts > $available_slots)
		{
			$errors[] = $this->language->lang('MARKETPLACE_IMAGE_LIMIT_REACHED', $max_allowed);
			return $saved;
		}

		$count = count($files['name']);
		for ($i = 0; $i < $count; $i++)
		{
			$name = isset($files['name'][$i]) ? (string) $files['name'][$i] : '';
			$tmp  = isset($files['tmp_name'][$i]) ? (string) $files['tmp_name'][$i] : '';
			$error = isset($files['error'][$i]) ? (int) $files['error'][$i] : UPLOAD_ERR_NO_FILE;
			$size = isset($files['size'][$i]) ? (int) $files['size'][$i] : 0;

			if ($error === UPLOAD_ERR_NO_FILE || ($name === '' && $tmp === ''))
			{
				continue;
			}

			if ($error !== UPLOAD_ERR_OK)
			{
				$errors[] = $this->language->lang('MARKETPLACE_IMAGE_UPLOAD_FAILED', $name);
				continue;
			}

			$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
			if (!in_array($ext, $allowed_exts, true))
			{
				$errors[] = $this->language->lang('MARKETPLACE_INVALID_IMAGE_EXT', $name);
				continue;
			}
			if ($size > $max_size)
			{
				$errors[] = $this->language->lang('MARKETPLACE_IMAGE_TOO_BIG', $name);
				continue;
			}

			if (!@getimagesize($tmp))
			{
				$errors[] = $this->language->lang('MARKETPLACE_INVALID_IMAGE_EXT', $name);
				continue;
			}

			$new_name = 'mp_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
			$dest = $this->upload_path . $new_name;

			if (@move_uploaded_file($tmp, $dest))
			{
				$saved[] = $new_name;
			}
			else
			{
				$errors[] = $this->language->lang('MARKETPLACE_IMAGE_UPLOAD_FAILED', $name);
			}
		}

		return $saved;
	}

	private function count_upload_attempts($files)
	{
		$count = isset($files['name']) && is_array($files['name']) ? count($files['name']) : 0;
		$attempts = 0;
		for ($i = 0; $i < $count; $i++)
		{
			$name = isset($files['name'][$i]) ? (string) $files['name'][$i] : '';
			$tmp = isset($files['tmp_name'][$i]) ? (string) $files['tmp_name'][$i] : '';
			$error = isset($files['error'][$i]) ? (int) $files['error'][$i] : UPLOAD_ERR_NO_FILE;
			if ($error !== UPLOAD_ERR_NO_FILE && ($name !== '' || $tmp !== ''))
			{
				$attempts++;
			}
		}
		return $attempts;
	}

	private function cleanup_uploaded_images($filenames)
	{
		foreach ($filenames as $filename)
		{
			if (!$this->is_safe_image_filename($filename))
			{
				continue;
			}
			$path = $this->upload_path . $filename;
			if (file_exists($path))
			{
				@unlink($path);
			}
		}
	}

	private function save_ad_images($ad_id, $filenames, $is_edit = false)
	{
		$order = $this->get_next_image_order($ad_id);
		$has_main = $this->ad_has_main_image($ad_id);
		foreach ($filenames as $idx => $filename)
		{
			$is_main = (!$has_main && $idx === 0) ? 1 : 0;
			$sql_ary = [
				'ad_id'          => $ad_id,
				'image_filename' => $filename,
				'image_order'    => $order,
				'image_is_main'  => $is_main,
			];
			$this->db->sql_query('INSERT INTO ' . $this->table_images . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
			if ($is_main)
			{
				$has_main = true;
			}
			$order += 10;
		}
	}

	private function get_requested_image_ids($field)
	{
		$ids = $this->request->variable($field, [0]);
		if (!is_array($ids))
		{
			return [];
		}
		$clean = [];
		foreach ($ids as $id)
		{
			$id = (int) $id;
			if ($id > 0)
			{
				$clean[$id] = $id;
			}
		}
		return array_values($clean);
	}

	private function get_image_count($ad_id)
	{
		$sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);
		return $count;
	}

	private function count_existing_images($ad_id, $image_ids)
	{
		if (empty($image_ids))
		{
			return 0;
		}
		$sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->table_images . '
			WHERE ad_id = ' . (int) $ad_id . '
			AND ' . $this->db->sql_in_set('image_id', array_map('intval', $image_ids));
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);
		return $count;
	}

	private function get_next_image_order($ad_id)
	{
		$sql = 'SELECT MAX(image_order) AS max_order FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		$max = (int) $this->db->sql_fetchfield('max_order');
		$this->db->sql_freeresult($result);
		return $max + 10;
	}

	private function update_image_order($ad_id, array $image_ids)
	{
		$order = 10;
		foreach ($image_ids as $image_id)
		{
			$image_id = (int) $image_id;
			if ($image_id <= 0)
			{
				continue;
			}

			$sql = 'UPDATE ' . $this->table_images . '
				SET image_order = ' . (int) $order . '
				WHERE ad_id = ' . (int) $ad_id . '
					AND image_id = ' . (int) $image_id;
			$this->db->sql_query($sql);
			$order += 10;
		}
	}

	private function ad_has_main_image($ad_id)
	{
		$sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id . ' AND image_is_main = 1';
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);
		return $count > 0;
	}

	private function set_main_image($ad_id, $image_id)
	{
		$sql = 'SELECT image_id FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id . ' AND image_id = ' . (int) $image_id;
		$result = $this->db->sql_query($sql);
		$exists = (int) $this->db->sql_fetchfield('image_id');
		$this->db->sql_freeresult($result);
		if (!$exists)
		{
			return;
		}

		$this->db->sql_query('UPDATE ' . $this->table_images . ' SET image_is_main = 0 WHERE ad_id = ' . (int) $ad_id);
		$this->db->sql_query('UPDATE ' . $this->table_images . ' SET image_is_main = 1 WHERE ad_id = ' . (int) $ad_id . ' AND image_id = ' . (int) $image_id);
	}

	private function ensure_main_image($ad_id)
	{
		if ($this->ad_has_main_image($ad_id))
		{
			return;
		}

		$sql = 'SELECT image_id FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id . ' ORDER BY image_order ASC, image_id ASC';
		$result = $this->db->sql_query_limit($sql, 1);
		$image_id = (int) $this->db->sql_fetchfield('image_id');
		$this->db->sql_freeresult($result);
		if ($image_id > 0)
		{
			$this->set_main_image($ad_id, $image_id);
		}
	}

	private function delete_ad_image($ad_id, $image_id)
	{
		$sql = 'SELECT image_filename FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id . ' AND image_id = ' . (int) $image_id;
		$result = $this->db->sql_query($sql);
		$filename = (string) $this->db->sql_fetchfield('image_filename');
		$this->db->sql_freeresult($result);

		if ($filename !== '' && $this->is_safe_image_filename($filename))
		{
			$path = $this->upload_path . $filename;
			if (file_exists($path))
			{
				@unlink($path);
			}
		}

		$this->db->sql_query('DELETE FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id . ' AND image_id = ' . (int) $image_id);
	}

	private function delete_ad_images($ad_id)
	{
		$images = $this->get_ad_images($ad_id);
		foreach ($images as $img)
		{
			$filename = (string) $img['image_filename'];
			if (!$this->is_safe_image_filename($filename))
			{
				continue;
			}

			$path = $this->upload_path . $filename;
			if (file_exists($path))
			{
				@unlink($path);
			}
		}
		$this->db->sql_query('DELETE FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id);
	}


	private function validate_category_requirements($category, $ad_type, $ad_price_type, $ad_price_cents, $ad_location, $ad_phone, &$errors)
	{
		if (!$this->category_allows_type($category, (int) $ad_type))
		{
			$errors[] = $this->language->lang('MARKETPLACE_CAT_TYPE_NOT_ALLOWED');
		}

		if (!empty($category['cat_require_price']) && !empty($this->config['marketplace_enable_price']) && (!isset($category['cat_allow_price']) || (int) $category['cat_allow_price']))
		{
			if (in_array((int) $ad_price_type, [3, 4], true) || (int) $ad_price_cents <= 0)
			{
				$errors[] = $this->language->lang('MARKETPLACE_CAT_PRICE_REQUIRED');
			}
		}

		if (!empty($category['cat_require_location']) && \utf8_clean_string($ad_location) === '')
		{
			$errors[] = $this->language->lang('MARKETPLACE_CAT_LOCATION_REQUIRED');
		}

		if (!empty($category['cat_require_phone']) && \utf8_clean_string($ad_phone) === '')
		{
			$errors[] = $this->language->lang('MARKETPLACE_CAT_PHONE_REQUIRED');
		}
	}


	private function category_allows_type($category, $type)
	{
		if (!$category || !isset($category['cat_allowed_types']) || trim((string) $category['cat_allowed_types']) === '')
		{
			return true;
		}

		$allowed = array_map('intval', explode(',', (string) $category['cat_allowed_types']));
		return in_array((int) $type, $allowed, true);
	}

	private function category_allows_images($cat_id)
	{
		if ((int) $cat_id <= 0)
		{
			return true;
		}

		$category = $this->get_category((int) $cat_id);
		return !$category || !isset($category['cat_allow_images']) || (int) $category['cat_allow_images'];
	}

	private function next_bump_time($ad)
	{
		$interval = max(0, (int) $this->config['marketplace_bump_interval_days']) * 86400;
		if ($interval <= 0)
		{
			return 0;
		}

		$last = !empty($ad['ad_last_bumped']) ? (int) $ad['ad_last_bumped'] : (int) $ad['ad_created'];
		return $last + $interval;
	}

	private function can_bump_ad($ad)
	{
		if (empty($this->config['marketplace_allow_bump']) || (int) $ad['ad_status'] !== 1)
		{
			return false;
		}

		$is_owner = ((int) $ad['user_id'] === (int) $this->user->data['user_id']) && (int) $this->user->data['user_id'] !== ANONYMOUS;
		if (!(($is_owner && ($this->auth->acl_get('u_marketplace_bump_own') || $this->auth->acl_get('u_marketplace_edit_own'))) || $this->auth->acl_get('m_marketplace_edit')))
		{
			return false;
		}

		if ($this->auth->acl_get('m_marketplace_edit'))
		{
			return true;
		}

		$next_bump = $this->next_bump_time($ad);
		return $next_bump <= time();
	}

	private function can_feature_ad($ad)
	{
		return (!isset($this->config['marketplace_allow_featured']) || !empty($this->config['marketplace_allow_featured'])) && (int) $ad['ad_status'] === 1 && ($this->auth->acl_get('m_marketplace_feature') || $this->auth->acl_get('m_marketplace_edit')) && (empty($ad['ad_featured_until']) || (int) $ad['ad_featured_until'] < time());
	}

	private function can_unfeature_ad($ad)
	{
		return (!isset($this->config['marketplace_allow_featured']) || !empty($this->config['marketplace_allow_featured'])) && ($this->auth->acl_get('m_marketplace_feature') || $this->auth->acl_get('m_marketplace_edit')) && !empty($ad['ad_featured_until']) && (int) $ad['ad_featured_until'] >= time();
	}

	private function can_boost_ad($ad)
	{
		return (!isset($this->config['marketplace_allow_boosted']) || !empty($this->config['marketplace_allow_boosted'])) && (int) $ad['ad_status'] === 1 && ($this->auth->acl_get('m_marketplace_feature') || $this->auth->acl_get('m_marketplace_edit')) && (empty($ad['ad_boosted_until']) || (int) $ad['ad_boosted_until'] < time());
	}

	private function can_unboost_ad($ad)
	{
		return (!isset($this->config['marketplace_allow_boosted']) || !empty($this->config['marketplace_allow_boosted'])) && ($this->auth->acl_get('m_marketplace_feature') || $this->auth->acl_get('m_marketplace_edit')) && !empty($ad['ad_boosted_until']) && (int) $ad['ad_boosted_until'] >= time();
	}


	private function can_request_promotion($ad, $type)
	{
		if (empty($this->config['marketplace_allow_promotion_requests']) || (int) $this->user->data['user_id'] === ANONYMOUS)
		{
			return false;
		}

		if ((int) $ad['ad_status'] !== 1 || (int) $ad['user_id'] !== (int) $this->user->data['user_id'])
		{
			return false;
		}

		if ($type === 'featured')
		{
			if (isset($this->config['marketplace_allow_featured']) && empty($this->config['marketplace_allow_featured']))
			{
				return false;
			}
			if (!empty($ad['ad_featured_until']) && (int) $ad['ad_featured_until'] >= time())
			{
				return false;
			}
		}
		else if ($type === 'boosted')
		{
			if (isset($this->config['marketplace_allow_boosted']) && empty($this->config['marketplace_allow_boosted']))
			{
				return false;
			}
			if (!empty($ad['ad_boosted_until']) && (int) $ad['ad_boosted_until'] >= time())
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		return !$this->has_pending_promotion((int) $ad['ad_id'], $type);
	}

	private function has_pending_promotion($ad_id, $type)
	{
		$sql = 'SELECT promotion_id
			FROM ' . $this->table_promotions . "
			WHERE ad_id = " . (int) $ad_id . "
				AND promotion_type = '" . $this->db->sql_escape($type) . "'
				AND promotion_status IN (0, 3)";
		$result = $this->db->sql_query_limit($sql, 1);
		$promotion_id = (int) $this->db->sql_fetchfield('promotion_id');
		$this->db->sql_freeresult($result);

		return $promotion_id > 0;
	}


	private function get_available_promotion_packages($type)
	{
		$sql = 'SELECT * FROM ' . $this->table_promotion_packages . "
			WHERE package_enabled = 1
				AND package_type = '" . $this->db->sql_escape($type) . "'
			ORDER BY package_order ASC, package_days ASC, package_id ASC";
		$result = $this->db->sql_query($sql);
		$packages = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row = $this->apply_package_discounts($row, false);
			$row['PACKAGE_PRICE_DISPLAY'] = $this->format_package_price((int) $row['package_amount_cents'], $row['package_currency']);
			$packages[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $packages;
	}

	private function get_requested_promotion_package($type)
	{
		$package_id = $this->request->variable('package_id', 0);
		if (!$package_id)
		{
			return null;
		}

		$sql = 'SELECT * FROM ' . $this->table_promotion_packages . "
			WHERE package_id = " . (int) $package_id . "
				AND package_enabled = 1
				AND package_type = '" . $this->db->sql_escape($type) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$package = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $package ? $this->apply_package_discounts($package, true) : null;
	}

	private function format_package_price($amount_cents, $currency)
	{
		if ((int) $amount_cents <= 0)
		{
			return $this->language->lang('MARKETPLACE_PACKAGE_FREE_MANUAL');
		}
		return trim((string) $currency . ' ' . number_format(((int) $amount_cents) / 100, 2, ',', '.'));
	}

	private function create_promotion_request($ad, $type, $days, $package = null)
	{
		$payment_provider = $this->get_requested_payment_provider($package);
		$requires_payment = ($payment_provider !== 'manual');
		$payment_reference = $requires_payment ? $this->generate_payment_reference((int) $ad['ad_id'], $type) : '';

		$sql_ary = [
			'ad_id' => (int) $ad['ad_id'],
			'user_id' => (int) $this->user->data['user_id'],
			'promotion_type' => (string) $type,
			'package_id' => $package ? (int) $package['package_id'] : 0,
			'promotion_status' => $requires_payment ? 3 : 0,
			'promotion_days' => max(1, (int) $days),
			'promotion_amount_cents' => $package ? (int) $package['package_amount_cents'] : 0,
			'promotion_currency' => $package ? (string) $package['package_currency'] : (isset($ad['ad_currency']) ? (string) $ad['ad_currency'] : ''),
			'payment_provider' => $payment_provider,
			'payment_reference' => $payment_reference,
			'promotion_requested' => time(),
			'promotion_decided' => 0,
			'promotion_decided_by' => 0,
			'promotion_note' => $package ? (string) $package['package_title'] . (!empty($package['DISCOUNT_NOTE']) ? ' | ' . (string) $package['DISCOUNT_NOTE'] : '') : '',
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_promotions . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
		$promotion_id = (int) $this->db->sql_nextid();
		if ($payment_provider === 'pix')
		{
			$this->log_manual_payment_request('pix', $payment_reference, $promotion_id, (int) $sql_ary['promotion_amount_cents'], (string) $sql_ary['promotion_currency']);
		}
		return $promotion_id;
	}


	private function apply_package_discounts(array $package, $consume_coupon = false)
	{
		$original_amount = isset($package['package_amount_cents']) ? (int) $package['package_amount_cents'] : 0;
		$amount = $original_amount;
		$notes = [];

		if ($this->is_package_free_for_user($package))
		{
			$amount = 0;
			$notes[] = $this->language->lang('MARKETPLACE_FREE_BY_GROUP');
		}

		$period_discount = $this->get_active_period_discount(isset($package['package_type']) ? (string) $package['package_type'] : '');
		if ($amount > 0 && $period_discount)
		{
			$amount = $this->apply_discount_amount($amount, $period_discount['discount_type'], (int) $period_discount['discount_value']);
			$notes[] = $this->language->lang('MARKETPLACE_PROMOTIONAL_PERIOD_APPLIED');
		}

		$coupon_code = strtoupper(preg_replace('/[^A-Z0-9_-]/i', '', $this->request->variable('coupon_code', '', true)));
		if ($amount > 0 && $coupon_code !== '')
		{
			$coupon = $this->get_valid_coupon($coupon_code, isset($package['package_currency']) ? (string) $package['package_currency'] : '');
			if ($coupon)
			{
				$amount = $this->apply_discount_amount($amount, $coupon['discount_type'], (int) $coupon['discount_value']);
				$notes[] = $this->language->lang('MARKETPLACE_COUPON_APPLIED', $coupon['coupon_code']);
				if ($consume_coupon)
				{
					$this->db->sql_query('UPDATE ' . $this->table_coupons . ' SET coupon_used_count = coupon_used_count + 1 WHERE coupon_id = ' . (int) $coupon['coupon_id']);
				}
			}
		}

		$package['package_original_amount_cents'] = $original_amount;
		$package['package_amount_cents'] = max(0, (int) $amount);
		$package['DISCOUNT_NOTE'] = implode(', ', $notes);
		return $package;
	}

	private function apply_discount_amount($amount, $type, $value)
	{
		$amount = max(0, (int) $amount);
		$value = max(0, (int) $value);
		if ($type === 'fixed')
		{
			return max(0, $amount - $value);
		}
		return max(0, (int) floor($amount * (100 - min(100, $value)) / 100));
	}

	private function get_valid_coupon($code, $currency)
	{
		$now = time();
		$sql = 'SELECT * FROM ' . $this->table_coupons . "
			WHERE coupon_enabled = 1
				AND coupon_code = '" . $this->db->sql_escape((string) $code) . "'
				AND (coupon_starts = 0 OR coupon_starts <= " . (int) $now . ")
				AND (coupon_ends = 0 OR coupon_ends >= " . (int) $now . ")
				AND (coupon_usage_limit = 0 OR coupon_used_count < coupon_usage_limit)";
		$result = $this->db->sql_query_limit($sql, 1);
		$coupon = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$coupon)
		{
			return null;
		}
		if ($coupon['discount_type'] === 'fixed' && strtoupper((string) $coupon['coupon_currency']) !== strtoupper((string) $currency))
		{
			return null;
		}
		return $coupon;
	}

	private function get_active_period_discount($package_type)
	{
		$now = time();
		$sql = 'SELECT * FROM ' . $this->table_promo_periods . "
			WHERE period_enabled = 1
				AND (period_package_type = 'all' OR period_package_type = '" . $this->db->sql_escape((string) $package_type) . "')
				AND (period_starts = 0 OR period_starts <= " . (int) $now . ")
				AND (period_ends = 0 OR period_ends >= " . (int) $now . ")
			ORDER BY period_id DESC";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $row ?: null;
	}

	private function is_package_free_for_user(array $package)
	{
		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			return false;
		}

		$column = '';
		if ($package['package_type'] === 'featured')
		{
			$column = 'free_featured';
		}
		else if ($package['package_type'] === 'boosted' || $package['package_type'] === 'boost_bundle')
		{
			$column = 'free_boosted';
		}
		else if ($package['package_type'] === 'seller_plan')
		{
			$column = 'free_seller_plan';
		}
		if ($column === '')
		{
			return false;
		}

		$sql = 'SELECT gf.' . $column . '
			FROM ' . USER_GROUP_TABLE . ' ug
			INNER JOIN ' . $this->table_group_freebies . ' gf ON gf.group_id = ug.group_id
			WHERE ug.user_id = ' . (int) $this->user->data['user_id'] . '
				AND ug.user_pending = 0
				AND gf.' . $column . ' = 1';
		$result = $this->db->sql_query_limit($sql, 1);
		$free = (int) $this->db->sql_fetchfield($column);
		$this->db->sql_freeresult($result);
		return $free === 1;
	}

	private function should_create_paypal_payment($package)
	{
		return $package && !empty($this->config['marketplace_paypal_enabled']) && (!isset($this->config['marketplace_gateway_paypal_enabled']) || !empty($this->config['marketplace_gateway_paypal_enabled'])) && $this->get_paypal_business_account() !== '' && (int) $package['package_amount_cents'] > 0;
	}


	private function should_create_pix_payment($package)
	{
		return $package && $this->is_pix_gateway_ready() && (int) $package['package_amount_cents'] > 0;
	}

	private function is_pix_gateway_ready()
	{
		return !empty($this->config['marketplace_gateway_pix_enabled']) && trim((string) (isset($this->config['marketplace_gateway_pix_key']) ? $this->config['marketplace_gateway_pix_key'] : '')) !== '';
	}

	private function get_requested_payment_provider($package)
	{
		if (!$package || (int) $package['package_amount_cents'] <= 0)
		{
			return 'manual';
		}

		$gateway = strtolower($this->request->variable('payment_gateway', ''));
		if ($gateway === 'pix' && $this->should_create_pix_payment($package))
		{
			return 'pix';
		}
		if ($gateway === 'paypal' && $this->should_create_paypal_payment($package))
		{
			return 'paypal';
		}
		if ($this->should_create_paypal_payment($package))
		{
			return 'paypal';
		}
		if ($this->should_create_pix_payment($package))
		{
			return 'pix';
		}
		return 'manual';
	}

	private function generate_payment_reference($ad_id, $type)
	{
		return 'MP-' . (int) $ad_id . '-' . preg_replace('/[^A-Z0-9]/i', '', (string) $type) . '-' . time() . '-' . mt_rand(1000, 9999);
	}


	private function sanitize_paypal_email($email)
	{
		$email = trim((string) $email);
		$email = str_replace(["\r", "\n", "\t"], '', $email);
		if ($email === '')
		{
			return '';
		}

		return filter_var($email, FILTER_VALIDATE_EMAIL) ? substr($email, 0, 255) : '';
	}

	private function get_seller_paypal_account($ad)
	{
		return isset($ad['ad_paypal_email']) ? $this->sanitize_paypal_email($ad['ad_paypal_email']) : '';
	}

	private function get_paypal_business_account()
	{
		if (!empty($this->config['marketplace_paypal_sandbox']))
		{
			return $this->sanitize_paypal_email(isset($this->config['marketplace_paypal_sandbox_business']) ? (string) $this->config['marketplace_paypal_sandbox_business'] : '');
		}

		return $this->sanitize_paypal_email(isset($this->config['marketplace_paypal_business']) ? (string) $this->config['marketplace_paypal_business'] : '');
	}

	private function get_paypal_purchase_business_account($ad)
	{
		// PayPal Sandbox only accepts sandbox merchant accounts as the checkout receiver.
		// Real seller PayPal addresses must not be sent to sandbox, otherwise PayPal returns INVALID_BUSINESS_ERROR.
		if (!empty($this->config['marketplace_paypal_sandbox']))
		{
			return $this->get_paypal_business_account();
		}

		return $this->get_seller_paypal_account($ad);
	}


	private function promotion_uses_pix($promotion_id)
	{
		$sql = 'SELECT payment_provider FROM ' . $this->table_promotions . ' WHERE promotion_id = ' . (int) $promotion_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$provider = (string) $this->db->sql_fetchfield('payment_provider');
		$this->db->sql_freeresult($result);
		return strtolower($provider) === 'pix';
	}

	private function mask_pix_key($value, $type = 'cpf')
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

	private function get_pix_key_type_lang($type)
	{
		$type = strtolower((string) $type);
		$map = [
			'cpf' => 'CPF',
			'cnpj' => 'CNPJ',
			'email' => 'E-mail',
			'phone' => $this->language->lang('MARKETPLACE_PHONE'),
			'random' => $this->language->lang('MARKETPLACE_GATEWAY_PIX_RANDOM_KEY'),
		];
		return isset($map[$type]) ? $map[$type] : strtoupper($type);
	}

	private function build_paypal_payment_url($promotion_id)
	{
		$sql = 'SELECT p.*, a.ad_title
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE p.promotion_id = ' . (int) $promotion_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$promotion = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$promotion || $promotion['payment_provider'] !== 'paypal' || (int) $promotion['promotion_amount_cents'] <= 0)
		{
			return '';
		}

		$business = $this->get_paypal_business_account();
		if ($business === '')
		{
			return '';
		}

		$amount = number_format(((int) $promotion['promotion_amount_cents']) / 100, 2, '.', '');
		$currency = !empty($promotion['promotion_currency']) ? strtoupper((string) $promotion['promotion_currency']) : (isset($this->config['marketplace_paypal_currency']) ? strtoupper((string) $this->config['marketplace_paypal_currency']) : 'BRL');
		$currency = preg_replace('/[^A-Z]/', '', $currency);
		if (strlen($currency) !== 3)
		{
			$currency = 'BRL';
		}

		$item = $this->language->lang('MARKETPLACE_PAYPAL_ITEM_NAME', (string) $promotion['promotion_note'], (string) $promotion['ad_title']);
		$return_url = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $promotion['ad_id']], true);
		$base = !empty($this->config['marketplace_paypal_sandbox']) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';

		$params = [
			'cmd' => '_xclick',
			'business' => $business,
			'item_name' => $item,
			'amount' => $amount,
			'currency_code' => $currency,
			'custom' => (string) $promotion['payment_reference'],
			'return' => $return_url,
			'cancel_return' => $return_url,
			'notify_url' => $this->helper->route('mundophpbb_marketplace_paypal_ipn', [], true),
			'no_shipping' => '1',
			'no_note' => '1',
		];

		return $base . '?' . http_build_query($params, '', '&');
	}


	private function verify_paypal_ipn($raw_post)
	{
		$endpoint = !empty($this->config['marketplace_paypal_sandbox']) ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';
		$payload = 'cmd=_notify-validate&' . $raw_post;

		$context = stream_context_create([
			'http' => [
				'method' => 'POST',
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
					"Content-Length: " . strlen($payload) . "\r\n" .
					"Connection: close\r\n",
				'content' => $payload,
				'timeout' => 30,
			],
		]);

		$response = @file_get_contents($endpoint, false, $context);

		return trim((string) $response) === 'VERIFIED';
	}

	private function process_paypal_ipn(array $ipn_data)
	{
		$payment_status = isset($ipn_data['payment_status']) ? strtolower(trim((string) $ipn_data['payment_status'])) : '';
		$reference = isset($ipn_data['custom']) ? trim((string) $ipn_data['custom']) : '';
		$promotion = false;

		if ($reference !== '')
		{
			$sql = 'SELECT p.*, a.ad_title, a.ad_status
				FROM ' . $this->table_promotions . ' p
				LEFT JOIN ' . $this->table_ads . " a ON a.ad_id = p.ad_id
				WHERE p.payment_reference = '" . $this->db->sql_escape($reference) . "'
					AND p.payment_provider = 'paypal'";
			$result = $this->db->sql_query_limit($sql, 1);
			$promotion = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
		}

		if ($payment_status !== 'completed')
		{
			$this->log_payment_ipn($ipn_data, 'verified', 'IGNORED_STATUS', $promotion ? (int) $promotion['promotion_id'] : 0, 'paypal');
			if ($promotion)
			{
				$this->add_notification((int) $promotion['user_id'], (int) $promotion['ad_id'], 'payment_rejected', $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_REJECTED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_REJECTED_MESSAGE', $promotion['ad_title'], strtoupper($payment_status)));
			}
			else
			{
				$this->add_system_notification(0, 'payment_invalid', $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_SYSTEM_MESSAGE', $reference !== '' ? $reference : '-'));
			}
			return 'IGNORED_STATUS';
		}

		if ($reference === '')
		{
			$this->log_payment_ipn($ipn_data, 'verified', 'MISSING_REFERENCE', 0, 'paypal');
			$this->add_system_notification(0, 'payment_invalid', $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_SYSTEM_MESSAGE', '-'));
			return 'MISSING_REFERENCE';
		}

		if (!$promotion)
		{
			$this->log_payment_ipn($ipn_data, 'verified', 'PROMOTION_NOT_FOUND', 0, 'paypal');
			$this->add_system_notification(0, 'payment_invalid', $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_SYSTEM_MESSAGE', $reference));
			return 'PROMOTION_NOT_FOUND';
		}

		if ((int) $promotion['promotion_status'] === 1)
		{
			$this->log_payment_ipn($ipn_data, 'verified', 'ALREADY_APPROVED', (int) $promotion['promotion_id'], 'paypal');
			return 'ALREADY_APPROVED';
		}

		if ((int) $promotion['promotion_status'] !== 3)
		{
			$this->log_payment_ipn($ipn_data, 'verified', 'PROMOTION_NOT_AWAITING_PAYMENT', (int) $promotion['promotion_id'], 'paypal');
			$this->add_notification((int) $promotion['user_id'], (int) $promotion['ad_id'], 'payment_invalid', $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_MESSAGE', $promotion['ad_title']));
			return 'PROMOTION_NOT_AWAITING_PAYMENT';
		}

		if (!$this->validate_paypal_promotion_payment($promotion, $ipn_data))
		{
			$this->log_payment_ipn($ipn_data, 'verified', 'PAYMENT_MISMATCH', (int) $promotion['promotion_id'], 'paypal');
			$this->add_notification((int) $promotion['user_id'], (int) $promotion['ad_id'], 'payment_invalid', $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_MESSAGE', $promotion['ad_title']));
			return 'PAYMENT_MISMATCH';
		}

		if ((int) $promotion['ad_status'] !== 1)
		{
			$this->log_payment_ipn($ipn_data, 'verified', 'AD_NOT_ACTIVE', (int) $promotion['promotion_id'], 'paypal');
			$this->add_notification((int) $promotion['user_id'], (int) $promotion['ad_id'], 'payment_invalid', $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_INVALID_MESSAGE', $promotion['ad_title']));
			return 'AD_NOT_ACTIVE';
		}

		$this->approve_paid_promotion($promotion, isset($ipn_data['txn_id']) ? (string) $ipn_data['txn_id'] : '');
		$this->log_payment_ipn($ipn_data, 'verified', 'OK', (int) $promotion['promotion_id'], 'paypal');
		$this->add_notification((int) $promotion['user_id'], (int) $promotion['ad_id'], 'payment_confirmed', $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_CONFIRMED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PAYMENT_CONFIRMED_MESSAGE', $promotion['ad_title']));

		return 'OK';
	}


	private function log_payment_ipn(array $ipn_data, $verification_status, $validation_status, $promotion_id = 0, $provider = 'paypal')
	{
		$reference = isset($ipn_data['custom']) ? substr(trim((string) $ipn_data['custom']), 0, 255) : '';
		$transaction_id = isset($ipn_data['txn_id']) ? substr(preg_replace('/[^A-Z0-9._-]/i', '', (string) $ipn_data['txn_id']), 0, 255) : '';
		$payment_status = isset($ipn_data['payment_status']) ? substr(trim((string) $ipn_data['payment_status']), 0, 50) : '';
		$currency = isset($ipn_data['mc_currency']) ? strtoupper(substr(preg_replace('/[^A-Z]/', '', (string) $ipn_data['mc_currency']), 0, 10)) : '';
		$gross = isset($ipn_data['mc_gross']) ? (float) str_replace(',', '.', (string) $ipn_data['mc_gross']) : 0.0;
		$receiver = isset($ipn_data['receiver_email']) ? $this->sanitize_paypal_email($ipn_data['receiver_email']) : '';
		if ($receiver === '' && isset($ipn_data['business']))
		{
			$receiver = $this->sanitize_paypal_email($ipn_data['business']);
		}

		$sql_ary = [
			'promotion_id' => (int) $promotion_id,
			'payment_provider' => substr((string) $provider, 0, 50),
			'payment_reference' => $reference,
			'payment_transaction_id' => $transaction_id,
			'payment_status' => $payment_status,
			'payment_verification_status' => substr((string) $verification_status, 0, 50),
			'payment_validation_status' => substr((string) $validation_status, 0, 100),
			'payment_amount_cents' => (int) round($gross * 100),
			'payment_currency' => $currency,
			'payment_receiver' => $receiver,
			'payment_raw' => json_encode($ipn_data),
			'payment_created' => time(),
		];

		$this->db->sql_query('INSERT INTO ' . $this->table_payment_logs . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}


	private function log_manual_payment_request($provider, $reference, $promotion_id, $amount_cents, $currency)
	{
		if (empty($this->table_payment_logs) || trim((string) $reference) === '')
		{
			return;
		}
		$sql_ary = [
			'promotion_id' => (int) $promotion_id,
			'payment_provider' => substr((string) $provider, 0, 50),
			'payment_reference' => substr((string) $reference, 0, 255),
			'payment_transaction_id' => '',
			'payment_status' => 'pending',
			'payment_verification_status' => 'manual',
			'payment_validation_status' => 'PENDING_MANUAL_CONFIRMATION',
			'payment_amount_cents' => (int) $amount_cents,
			'payment_currency' => substr((string) $currency, 0, 10),
			'payment_receiver' => substr((string) (isset($this->config['marketplace_gateway_pix_key']) ? $this->config['marketplace_gateway_pix_key'] : ''), 0, 255),
			'payment_raw' => json_encode(['provider' => (string) $provider, 'reference' => (string) $reference, 'created_by' => (int) $this->user->data['user_id']]),
			'payment_created' => time(),
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_payment_logs . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}

	private function validate_paypal_promotion_payment(array $promotion, array $ipn_data)
	{
		$receiver_email = isset($ipn_data['receiver_email']) ? $this->sanitize_paypal_email($ipn_data['receiver_email']) : '';
		$business = isset($ipn_data['business']) ? $this->sanitize_paypal_email($ipn_data['business']) : '';
		$expected_business = $this->sanitize_paypal_email($this->get_paypal_business_account());

		if ($expected_business === '' || ($receiver_email !== $expected_business && $business !== $expected_business))
		{
			return false;
		}

		$gross = isset($ipn_data['mc_gross']) ? (float) str_replace(',', '.', (string) $ipn_data['mc_gross']) : 0.0;
		$expected_gross = ((int) $promotion['promotion_amount_cents']) / 100;
		if (abs($gross - $expected_gross) > 0.009)
		{
			return false;
		}

		$currency = isset($ipn_data['mc_currency']) ? strtoupper(preg_replace('/[^A-Z]/', '', (string) $ipn_data['mc_currency'])) : '';
		$expected_currency = !empty($promotion['promotion_currency']) ? strtoupper(preg_replace('/[^A-Z]/', '', (string) $promotion['promotion_currency'])) : (isset($this->config['marketplace_paypal_currency']) ? strtoupper(preg_replace('/[^A-Z]/', '', (string) $this->config['marketplace_paypal_currency'])) : 'BRL');

		return $currency !== '' && $currency === $expected_currency;
	}

	private function approve_paid_promotion(array $promotion, $transaction_id = '')
	{
		$now = time();
		$days = max(1, (int) $promotion['promotion_days']);

		if ($promotion['promotion_type'] === 'featured')
		{
			$sql_ary = [
				'ad_featured_until' => $now + ($days * 86400),
				'ad_featured_by' => 0,
				'ad_updated' => $now,
			];
		}
		else if ($promotion['promotion_type'] === 'boosted')
		{
			$sql_ary = [
				'ad_boosted_until' => $now + ($days * 86400),
				'ad_boosted_by' => 0,
				'ad_updated' => $now,
			];
		}
		else
		{
			return;
		}

		$sql_ary = $this->filter_existing_ad_columns($sql_ary);
		$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . (int) $promotion['ad_id']);

		$promotion_note = (string) $promotion['promotion_note'];
		if ($transaction_id !== '' && strpos($promotion_note, $transaction_id) === false)
		{
			$promotion_note = trim($promotion_note . ' | PayPal TXN: ' . preg_replace('/[^A-Z0-9]/i', '', $transaction_id));
		}

		$update_ary = [
			'promotion_status' => 1,
			'promotion_decided' => $now,
			'promotion_decided_by' => 0,
			'promotion_note' => $promotion_note,
		];
		$this->db->sql_query('UPDATE ' . $this->table_promotions . ' SET ' . $this->db->sql_build_array('UPDATE', $update_ary) . ' WHERE promotion_id = ' . (int) $promotion['promotion_id']);

		$this->add_notification((int) $promotion['user_id'], (int) $promotion['ad_id'], 'promotion_auto_approved', $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_AUTO_APPROVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PROMOTION_APPROVED_MESSAGE', $promotion['ad_title'], $this->get_promotion_type_lang($promotion['promotion_type'])));
	}


	private function can_buy_ad($ad)
	{
		if ((int) $this->user->data['user_id'] === ANONYMOUS || (int) $ad['user_id'] === (int) $this->user->data['user_id'])
		{
			return false;
		}

		if ((int) $ad['ad_status'] !== 1 || (isset($ad['ad_quantity']) && (int) $ad['ad_quantity'] <= 0))
		{
			return false;
		}

		return !$this->has_pending_purchase((int) $ad['ad_id'], (int) $this->user->data['user_id']) && $this->has_available_purchase_slot($ad);
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

	private function can_buy_ad_with_paypal($ad)
	{
		if (empty($this->config['marketplace_direct_purchase_enabled']) || empty($this->config['marketplace_paypal_enabled']) || (isset($this->config['marketplace_gateway_paypal_enabled']) && empty($this->config['marketplace_gateway_paypal_enabled'])) || $this->get_paypal_purchase_business_account($ad) === '')
		{
			return false;
		}

		if (!$this->can_buy_ad($ad))
		{
			return false;
		}

		$price_type = isset($ad['ad_price_type']) ? (int) $ad['ad_price_type'] : 2;
		$amount = isset($ad['ad_price_cents']) ? (int) $ad['ad_price_cents'] : $this->parse_price_amount(isset($ad['ad_price']) ? $ad['ad_price'] : '');

		return $amount > 0 && in_array($price_type, [1, 2], true);
	}


	private function has_available_purchase_slot($ad)
	{
		$quantity = isset($ad['ad_quantity']) ? max(0, (int) $ad['ad_quantity']) : 1;
		if ($quantity <= 0)
		{
			return false;
		}

		$sql = 'SELECT COUNT(*) AS total
			FROM ' . $this->table_purchases . '
			WHERE ad_id = ' . (int) $ad['ad_id'] . '
				AND purchase_status IN (0, 3)';
		$result = $this->db->sql_query($sql);
		$reserved = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $reserved < $quantity;
	}

	private function has_pending_purchase($ad_id, $buyer_user_id)
	{
		$sql = 'SELECT purchase_id
			FROM ' . $this->table_purchases . '
			WHERE ad_id = ' . (int) $ad_id . '
				AND buyer_user_id = ' . (int) $buyer_user_id . '
				AND purchase_status IN (0, 3)';
		$result = $this->db->sql_query_limit($sql, 1);
		$purchase_id = (int) $this->db->sql_fetchfield('purchase_id');
		$this->db->sql_freeresult($result);

		return $purchase_id > 0;
	}

	private function create_purchase_request($ad, $status = 0, $provider = 'manual')
	{
		$amount = isset($ad['ad_price_cents']) ? (int) $ad['ad_price_cents'] : $this->parse_price_amount(isset($ad['ad_price']) ? $ad['ad_price'] : '');
		$currency = !empty($ad['ad_currency']) ? strtoupper((string) $ad['ad_currency']) : (isset($this->config['marketplace_paypal_currency']) ? strtoupper((string) $this->config['marketplace_paypal_currency']) : 'BRL');
		$currency = preg_replace('/[^A-Z]/', '', $currency);
		if (strlen($currency) !== 3)
		{
			$currency = isset($this->config['marketplace_paypal_currency']) ? strtoupper((string) $this->config['marketplace_paypal_currency']) : 'BRL';
		}

		$sql_ary = [
			'ad_id' => (int) $ad['ad_id'],
			'buyer_user_id' => (int) $this->user->data['user_id'],
			'seller_user_id' => (int) $ad['user_id'],
			'purchase_status' => (int) $status,
			'purchase_amount_cents' => max(0, $amount),
			'purchase_currency' => $currency,
			'payment_provider' => (string) $provider,
			'payment_reference' => in_array((string) $provider, ['paypal', 'pix'], true) ? $this->generate_payment_reference((int) $ad['ad_id'], 'buy') : '',
			'purchase_created' => time(),
			'purchase_decided' => 0,
			'purchase_decided_by' => 0,
			'purchase_note' => '',
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_purchases . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
		$purchase_id = (int) $this->db->sql_nextid();
		if ((string) $provider === 'pix')
		{
			$this->log_manual_payment_request('pix', (string) $sql_ary['payment_reference'], 0, (int) $sql_ary['purchase_amount_cents'], (string) $sql_ary['purchase_currency']);
		}

		$this->add_notification((int) $ad['user_id'], (int) $ad['ad_id'], 'purchase_pending_seller', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_PENDING_SELLER_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_PENDING_SELLER_MESSAGE', $ad['ad_title']));

		return $purchase_id;
	}


	private function can_buy_ad_with_pix($ad)
	{
		if (empty($this->config['marketplace_direct_purchase_enabled']) || !$this->is_pix_gateway_ready())
		{
			return false;
		}
		return $this->can_buy_ad($ad);
	}

	private function build_paypal_purchase_url($purchase_id)
	{
		$sql = 'SELECT p.*, a.ad_title, a.ad_paypal_email
			FROM ' . $this->table_purchases . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE p.purchase_id = ' . (int) $purchase_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$purchase = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$purchase || $purchase['payment_provider'] !== 'paypal' || (int) $purchase['purchase_amount_cents'] <= 0)
		{
			return '';
		}

		$business = $this->get_paypal_purchase_business_account($purchase);
		if ($business === '')
		{
			return '';
		}

		$amount = number_format(((int) $purchase['purchase_amount_cents']) / 100, 2, '.', '');
		$currency = !empty($purchase['purchase_currency']) ? strtoupper((string) $purchase['purchase_currency']) : (isset($this->config['marketplace_paypal_currency']) ? strtoupper((string) $this->config['marketplace_paypal_currency']) : 'BRL');
		$currency = preg_replace('/[^A-Z]/', '', $currency);
		if (strlen($currency) !== 3)
		{
			$currency = 'BRL';
		}

		$item = $this->language->lang('MARKETPLACE_PAYPAL_PURCHASE_ITEM_NAME', (string) $purchase['ad_title']);
		$return_url = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $purchase['ad_id']], true);
		$base = !empty($this->config['marketplace_paypal_sandbox']) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';

		$params = [
			'cmd' => '_xclick',
			'business' => $business,
			'item_name' => $item,
			'amount' => $amount,
			'currency_code' => $currency,
			'custom' => (string) $purchase['payment_reference'],
			'return' => $return_url,
			'cancel_return' => $return_url,
			'no_shipping' => '1',
			'no_note' => '1',
		];

		return $base . '?' . http_build_query($params, '', '&');
	}

	private function can_report_ad($ad)
	{
		if (empty($this->config['marketplace_allow_reports']) || (int) $this->user->data['user_id'] === ANONYMOUS || !$this->auth->acl_get('u_marketplace_report'))
		{
			return false;
		}

		if ((int) $ad['user_id'] === (int) $this->user->data['user_id'])
		{
			return false;
		}

		return $this->is_publicly_visible_ad($ad) && !$this->has_open_report((int) $ad['ad_id'], (int) $this->user->data['user_id']);
	}

	private function create_report($ad, $reason, $report_type = 'ad')
	{
		$reason = trim((string) $reason);
		if ($this->has_open_report((int) $ad['ad_id'], (int) $this->user->data['user_id']))
		{
			\trigger_error($this->language->lang('MARKETPLACE_REPORT_ALREADY_OPEN'));
		}

		if ($reason === '')
		{
			\trigger_error($this->language->lang('MARKETPLACE_REPORT_REASON_REQUIRED'));
		}

		$report_type = in_array($report_type, ['ad', 'seller', 'buyer'], true) ? $report_type : 'ad';
		$sql_ary = [
			'ad_id'          => (int) $ad['ad_id'],
			'reporter_id'    => (int) $this->user->data['user_id'],
			'report_reason'  => $reason,
			'report_type'    => $report_type,
			'target_user_id' => ($report_type === 'seller') ? (int) $ad['user_id'] : 0,
			'report_status'  => 0,
			'report_created' => time(),
			'report_note'    => '',
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_reports . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
		$this->add_system_notification((int) $ad['ad_id'], 'report_received', $this->language->lang('MARKETPLACE_NOTIFICATION_REPORT_RECEIVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_REPORT_RECEIVED_MESSAGE', $ad['ad_title']));
	}


	private function has_open_report($ad_id, $reporter_id)
	{
		$sql = 'SELECT report_id FROM ' . $this->table_reports . '
			WHERE ad_id = ' . (int) $ad_id . '
				AND reporter_id = ' . (int) $reporter_id . '
				AND report_status = 0';
		$result = $this->db->sql_query_limit($sql, 1);
		$report_id = (int) $this->db->sql_fetchfield('report_id');
		$this->db->sql_freeresult($result);

		return $report_id > 0;
	}


	private function has_follows_table()
	{
		return isset($this->config['marketplace_version']) && version_compare((string) $this->config['marketplace_version'], '1.4.12', '>=');
	}

	private function can_follow_seller($ad)
	{
		if ((isset($this->config['marketplace_allow_follows']) && empty($this->config['marketplace_allow_follows'])) || !$this->has_follows_table())
		{
			return false;
		}

		$user_id = (int) $this->user->data['user_id'];
		$seller_id = isset($ad['user_id']) ? (int) $ad['user_id'] : 0;
		return $user_id !== ANONYMOUS && $seller_id > 0 && $seller_id !== $user_id && (int) $ad['ad_status'] === 1;
	}

	private function is_following_seller($seller_id)
	{
		$user_id = (int) $this->user->data['user_id'];
		$seller_id = (int) $seller_id;
		if ($user_id === ANONYMOUS || $seller_id <= 0 || !$this->has_follows_table())
		{
			return false;
		}

		$sql = 'SELECT follow_id FROM ' . $this->table_follows . '
			WHERE follower_user_id = ' . $user_id . '
				AND followed_user_id = ' . $seller_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$follow_id = (int) $this->db->sql_fetchfield('follow_id');
		$this->db->sql_freeresult($result);

		return $follow_id > 0;
	}

	private function follow_seller($seller_id)
	{
		$user_id = (int) $this->user->data['user_id'];
		$seller_id = (int) $seller_id;
		if ($user_id === ANONYMOUS || $seller_id <= 0 || $seller_id === $user_id || !$this->has_follows_table() || $this->is_following_seller($seller_id))
		{
			return;
		}

		$sql_ary = [
			'follower_user_id' => $user_id,
			'followed_user_id' => $seller_id,
			'follow_created'   => time(),
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_follows . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}

	private function unfollow_seller($seller_id)
	{
		$user_id = (int) $this->user->data['user_id'];
		$seller_id = (int) $seller_id;
		if ($user_id === ANONYMOUS || $seller_id <= 0 || !$this->has_follows_table())
		{
			return;
		}

		$this->db->sql_query('DELETE FROM ' . $this->table_follows . '
			WHERE follower_user_id = ' . $user_id . '
				AND followed_user_id = ' . $seller_id);
	}

	private function notify_followers_new_ad($ad_id, $seller_id, $ad_title)
	{
		if ((isset($this->config['marketplace_allow_follows']) && empty($this->config['marketplace_allow_follows'])) || !$this->has_follows_table())
		{
			return;
		}

		$seller_id = (int) $seller_id;
		$sql = 'SELECT follower_user_id FROM ' . $this->table_follows . '
			WHERE followed_user_id = ' . $seller_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$follower_id = (int) $row['follower_user_id'];
			if ($follower_id > 0 && $follower_id !== $seller_id)
			{
				$this->add_notification($follower_id, (int) $ad_id, 'seller_new_ad', $this->language->lang('MARKETPLACE_NOTIFICATION_FOLLOW_NEW_AD_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_FOLLOW_NEW_AD_MESSAGE', $ad_title));
			}
		}
		$this->db->sql_freeresult($result);
	}


	private function db_tools_table_exists($table)
	{
		if (empty($table))
		{
			return false;
		}
		if (isset($this->column_exists_cache['table:' . $table]))
		{
			return $this->column_exists_cache['table:' . $table];
		}
		$sql = 'SELECT 1 FROM ' . $table;
		$result = @$this->db->sql_query_limit($sql, 1);
		if ($result)
		{
			$this->db->sql_freeresult($result);
			$this->column_exists_cache['table:' . $table] = true;
			return true;
		}
		$this->column_exists_cache['table:' . $table] = false;
		return false;
	}

	private function is_favorite_ad($ad_id)
	{
		if ((int) $this->user->data['user_id'] === ANONYMOUS || !$this->db_tools_table_exists($this->table_favorites))
		{
			return false;
		}
		$sql = 'SELECT 1 FROM ' . $this->table_favorites . ' WHERE user_id = ' . (int) $this->user->data['user_id'] . ' AND ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$exists = (bool) $this->db->sql_fetchfield(0);
		$this->db->sql_freeresult($result);
		return $exists;
	}

	private function toggle_user_ad_collection($ad_id, $action)
	{
		$this->language->add_lang('common', 'mundophpbb/marketplace');
		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			\login_box('', $this->language->lang('LOGIN_REQUIRED'));
		}
		$redirect = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $ad_id]);
		$sql = 'SELECT ad_id FROM ' . $this->table_ads . ' WHERE ad_id = ' . $ad_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$exists = (bool) $this->db->sql_fetchfield('ad_id');
		$this->db->sql_freeresult($result);
		if (!$exists)
		{
			\trigger_error($this->language->lang('MARKETPLACE_AD_NOT_FOUND'));
		}

		$table = ($action === 'favorite') ? $this->table_favorites : $this->table_compare;
		if (!$this->db_tools_table_exists($table))
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		$user_id = (int) $this->user->data['user_id'];
		$sql = 'SELECT 1 FROM ' . $table . ' WHERE user_id = ' . $user_id . ' AND ad_id = ' . $ad_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$has = (bool) $this->db->sql_fetchfield(0);
		$this->db->sql_freeresult($result);

		if ($action === 'favorite')
		{
			if ($has)
			{
				$this->db->sql_query('DELETE FROM ' . $table . ' WHERE user_id = ' . $user_id . ' AND ad_id = ' . $ad_id);
				$message = 'MARKETPLACE_FAVORITE_REMOVED';
			}
			else
			{
				$this->safe_insert_user_ad_collection($table, [
					'user_id'       => $user_id,
					'ad_id'         => $ad_id,
					'favorite_time' => time(),
				]);
				$message = 'MARKETPLACE_FAVORITE_ADDED';
			}
		}
		else if ($action === 'compare_remove')
		{
			$this->db->sql_query('DELETE FROM ' . $table . ' WHERE user_id = ' . $user_id . ' AND ad_id = ' . $ad_id);
			$redirect = $this->helper->route('mundophpbb_marketplace_compare');
			$message = 'MARKETPLACE_COMPARE_REMOVED';
		}
		else
		{
			if (!$has)
			{
				$sql = 'SELECT COUNT(*) AS total FROM ' . $table . ' WHERE user_id = ' . $user_id;
				$result = $this->db->sql_query($sql);
				$total = (int) $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);
				if ($total >= 4)
				{
					\trigger_error($this->language->lang('MARKETPLACE_COMPARE_LIMIT'));
				}
				$this->safe_insert_user_ad_collection($table, [
					'user_id'      => $user_id,
					'ad_id'        => $ad_id,
					'compare_time' => time(),
				]);
			}
			$redirect = $this->helper->route('mundophpbb_marketplace_compare');
			$message = 'MARKETPLACE_COMPARE_ADDED';
		}
		\meta_refresh(1, $redirect);
		\trigger_error($this->language->lang($message) . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
	}


	private function safe_insert_user_ad_collection($table, array $sql_ary)
	{
		// Favoritos e comparacao possuem indice unico user_id + ad_id.
		// Cliques duplos ou requisicoes simultaneas podem passar pela checagem previa
		// e tentar inserir a mesma combinacao. Neste caso, ignoramos a duplicidade
		// em vez de deixar o DBAL gerar erro SQL fatal para o usuario.
		$this->db->sql_return_on_error(true);
		$result = $this->db->sql_query('INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
		$this->db->sql_return_on_error(false);

		return $result !== false;
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


	private function add_system_notification($ad_id, $type, $title, $message)
	{
		$sql_ary = [
			'user_id'              => 0,
			'ad_id'                => (int) $ad_id,
			'notification_type'    => substr((string) $type, 0, 50),
			'notification_title'   => substr((string) $title, 0, 255),
			'notification_message' => (string) $message,
			'notification_read'    => 0,
			'notification_time'    => time(),
		];

		$this->db->sql_query('INSERT INTO ' . $this->table_notifications . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}


	private function has_messages_table()
	{
		return !empty($this->table_messages) && $this->db_tools_table_exists($this->table_messages) && $this->db_tools_table_exists($this->table_conversations);
	}

	private function marketplace_contact_is_blocked($blocker_user_id, $blocked_user_id, $ad_id = 0)
	{
		if (!$this->db_tools_table_exists($this->table_message_blocks))
		{
			return false;
		}

		$where = 'blocker_user_id = ' . (int) $blocker_user_id . ' AND blocked_user_id = ' . (int) $blocked_user_id;
		$where .= ' AND (ad_id = 0 OR ad_id = ' . (int) $ad_id . ')';
		$sql = 'SELECT block_id FROM ' . $this->table_message_blocks . ' WHERE ' . $where;
		$result = $this->db->sql_query_limit($sql, 1);
		$blocked = (bool) $this->db->sql_fetchfield('block_id');
		$this->db->sql_freeresult($result);

		return $blocked;
	}

	private function can_send_marketplace_message($ad)
	{
		$user_id = (int) $this->user->data['user_id'];
		if (!$this->has_messages_table() || $user_id === ANONYMOUS || (int) $ad['ad_status'] !== 1 || (int) $ad['user_id'] === $user_id)
		{
			return false;
		}

		if ($this->marketplace_contact_is_blocked((int) $ad['user_id'], $user_id, (int) $ad['ad_id']) || $this->marketplace_contact_is_blocked($user_id, (int) $ad['user_id'], (int) $ad['ad_id']))
		{
			return false;
		}

		return true;
	}

	private function get_or_create_marketplace_conversation($ad)
	{
		$buyer_id = (int) $this->user->data['user_id'];
		$seller_id = (int) $ad['user_id'];
		$ad_id = (int) $ad['ad_id'];

		$sql = 'SELECT conversation_id FROM ' . $this->table_conversations . '
			WHERE ad_id = ' . $ad_id . '
				AND buyer_user_id = ' . $buyer_id . '
				AND seller_user_id = ' . $seller_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$conversation_id = (int) $this->db->sql_fetchfield('conversation_id');
		$this->db->sql_freeresult($result);

		if ($conversation_id > 0)
		{
			return $conversation_id;
		}

		$now = time();
		$this->db->sql_query('INSERT INTO ' . $this->table_conversations . ' ' . $this->db->sql_build_array('INSERT', [
			'ad_id' => $ad_id,
			'buyer_user_id' => $buyer_id,
			'seller_user_id' => $seller_id,
			'conversation_status' => 0,
			'conversation_created' => $now,
			'conversation_updated' => $now,
			'last_message_time' => 0,
		]));

		return (int) $this->db->sql_nextid();
	}

	private function check_message_antispam($user_id)
	{
		$honeypot = $this->request->variable('mp_contact_homepage', '', true);
		if ($honeypot !== '')
		{
			\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_SPAM_DETECTED'));
		}

		$form_time = $this->request->variable('mp_contact_time', 0);
		if ($form_time <= 0 || time() - (int) $form_time < 2)
		{
			\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_SPAM_DETECTED'));
		}

		$limit = isset($this->config['marketplace_message_limit_per_hour']) ? (int) $this->config['marketplace_message_limit_per_hour'] : 10;
		$limit = max(1, $limit);
		$since = time() - 3600;
		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_messages . '
			WHERE sender_user_id = ' . (int) $user_id . '
				AND message_time >= ' . $since;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		if ($total >= $limit)
		{
			\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_LIMIT_REACHED'));
		}
	}

	private function send_marketplace_message_from_ad($ad)
	{
		if (!$this->can_send_marketplace_message($ad))
		{
			\trigger_error($this->language->lang('MARKETPLACE_CONTACT_UNAVAILABLE'));
		}

		$user_id = (int) $this->user->data['user_id'];
		$this->check_message_antispam($user_id);

		$message = trim($this->request->variable('message_text', '', true));
		if ($message === '')
		{
			\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_EMPTY'));
		}
		$message = truncate_string($message, 4000, 4000, false, '');

		$conversation_id = $this->get_or_create_marketplace_conversation($ad);
		$now = time();
		$sql_ary = [
			'conversation_id' => $conversation_id,
			'ad_id' => (int) $ad['ad_id'],
			'sender_user_id' => $user_id,
			'recipient_user_id' => (int) $ad['user_id'],
			'message_text' => $message,
			'message_ip' => (string) $this->user->ip,
			'message_time' => $now,
			'message_read' => 0,
			'message_reported' => 0,
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_messages . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
		$this->db->sql_query('UPDATE ' . $this->table_conversations . ' SET conversation_updated = ' . $now . ', last_message_time = ' . $now . ' WHERE conversation_id = ' . $conversation_id);

		if ($this->column_exists($this->table_ads, 'ad_contact_count'))
		{
			$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ad_contact_count = ad_contact_count + 1 WHERE ad_id = ' . (int) $ad['ad_id']);
		}

		$this->add_notification((int) $ad['user_id'], (int) $ad['ad_id'], 'message_received', $this->language->lang('MARKETPLACE_NOTIFICATION_MESSAGE_RECEIVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_MESSAGE_RECEIVED_MESSAGE', $ad['ad_title']));
	}

	private function handle_quick_action($action, &$ad)
	{
		if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_action'))
		{
			\trigger_error($this->language->lang('FORM_INVALID'));
		}

		$ad_id = (int) $ad['ad_id'];
		$now = time();
		$redirect = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $ad_id]);

		switch ($action)
		{
			case 'follow_seller':
				if (!$this->can_follow_seller($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$this->follow_seller((int) $ad['user_id']);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_SELLER_FOLLOWED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'unfollow_seller':
				if ((int) $this->user->data['user_id'] === ANONYMOUS)
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$this->unfollow_seller((int) $ad['user_id']);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_SELLER_UNFOLLOWED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'approve':
				if (!$this->can_approve_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$sql_ary = [
					'ad_status'     => 1,
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
				$this->add_notification((int) $ad['user_id'], $ad_id, 'approved', $this->language->lang('MARKETPLACE_NOTIFICATION_APPROVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_APPROVED_MESSAGE', $ad['ad_title']));
				$this->notify_followers_new_ad($ad_id, (int) $ad['user_id'], $ad['ad_title']);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_AD_APPROVED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'pause':
				if (!$this->can_pause_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$sql_ary = [
					'ad_status'        => 4,
					'ad_hidden_at'     => $now,
					'ad_hidden_by'     => (int) $this->user->data['user_id'],
					'ad_hidden_reason' => $this->language->lang('MARKETPLACE_PAUSED_BY_OWNER'),
					'ad_updated'       => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_AD_PAUSED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'mark_sold':
				if (!$this->can_mark_sold($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$sql_ary = [
					'ad_status'  => 2,
					'ad_sold_at' => $now,
					'ad_quantity'=> 0,
					'ad_updated' => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				if ((int) $ad['user_id'] !== (int) $this->user->data['user_id'])
				{
					$this->add_notification((int) $ad['user_id'], $ad_id, 'sold', $this->language->lang('MARKETPLACE_NOTIFICATION_SOLD_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_SOLD_MESSAGE', $ad['ad_title']));
				}
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_AD_MARKED_SOLD') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'stock_increase':
			case 'stock_decrease':
			case 'stock_out':
				if (!$this->can_manage_stock($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

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

				$sql_ary = [
					'ad_quantity' => $quantity,
					'ad_updated'  => $now,
				];
				if ($quantity <= 0)
				{
					$sql_ary['ad_status'] = 2;
					$sql_ary['ad_sold_at'] = $now;
				}
				else if ((int) $ad['ad_status'] === 2)
				{
					$sql_ary['ad_status'] = 1;
					$sql_ary['ad_sold_at'] = 0;
					if (empty($ad['ad_expires']))
					{
						$sql_ary['ad_expires'] = $this->calculate_expiration_time($now, (int) $ad['cat_id']);
					}
				}
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_STOCK_UPDATED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'renew':
				if (!$this->can_renew_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$is_expired = ((int) $ad['ad_status'] === 3);
				$new_status = ($is_expired && $this->config['marketplace_require_approval'] && !$this->auth->acl_get('m_marketplace_edit')) ? 0 : 1;
				$sql_ary = [
					'ad_status'       => $new_status,
					'ad_expires'      => ($new_status === 1) ? $this->calculate_expiration_time($now, (int) $ad['cat_id']) : 0,
					'ad_updated'      => $now,
					'ad_last_renewed' => $now,
					'ad_sold_at'      => 0,
					'ad_expired_at'   => 0,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang($new_status === 0 ? 'MARKETPLACE_AD_RENEWED_PENDING' : 'MARKETPLACE_AD_RENEWED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;


			case 'bump':
				if (!$this->can_bump_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$sql_ary = [
					'ad_last_bumped' => $now,
					'ad_updated'     => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_AD_BUMPED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'feature':
				if (isset($this->config['marketplace_allow_featured']) && empty($this->config['marketplace_allow_featured']))
				{
					\trigger_error($this->language->lang('MARKETPLACE_FEATURED_DISABLED'));
				}

				if (!$this->can_feature_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
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
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_AD_FEATURED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'boost':
				if (isset($this->config['marketplace_allow_boosted']) && empty($this->config['marketplace_allow_boosted']))
				{
					\trigger_error($this->language->lang('MARKETPLACE_BOOSTED_DISABLED'));
				}

				if (!$this->can_boost_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
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
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_AD_BOOSTED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'unboost':
				if (!$this->can_unboost_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$sql_ary = [
					'ad_boosted_until' => 0,
					'ad_boosted_by'    => 0,
					'ad_updated'       => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'unboosted', $this->language->lang('MARKETPLACE_NOTIFICATION_UNBOOSTED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_UNBOOSTED_MESSAGE', $ad['ad_title']));
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_AD_UNBOOSTED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'unfeature':
				if (!$this->can_unfeature_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$sql_ary = [
					'ad_featured_until' => 0,
					'ad_featured_by'    => 0,
					'ad_updated'        => $now,
				];
				$sql_ary = $this->filter_existing_ad_columns($sql_ary);
				$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . $ad_id);
				$this->add_notification((int) $ad['user_id'], $ad_id, 'unfeatured', $this->language->lang('MARKETPLACE_NOTIFICATION_UNFEATURED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_UNFEATURED_MESSAGE', $ad['ad_title']));
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_AD_UNFEATURED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;


			case 'request_featured':
				if (!$this->can_request_promotion($ad, 'featured'))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$package = $this->get_requested_promotion_package('featured');
				$days = $package ? (int) $package['package_days'] : max(1, $this->request->variable('featured_days', isset($this->config['marketplace_featured_days']) ? (int) $this->config['marketplace_featured_days'] : 14));
				$promotion_id = $this->create_promotion_request($ad, 'featured', $days, $package);
				$paypal_url = $this->build_paypal_payment_url($promotion_id);
				if ($paypal_url)
				{
					// External PayPal redirects must bypass phpBB's local redirect check.
					\redirect($paypal_url, false, true);
				}
				if ($this->promotion_uses_pix($promotion_id))
				{
					\redirect($this->helper->route('mundophpbb_marketplace_payment', ['context' => 'promotion', 'id' => $promotion_id]));
				}

				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_PROMOTION_REQUEST_SENT') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'request_boosted':
				if (!$this->can_request_promotion($ad, 'boosted'))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$package = $this->get_requested_promotion_package('boosted');
				$days = $package ? (int) $package['package_days'] : max(1, $this->request->variable('boosted_days', isset($this->config['marketplace_boosted_days']) ? (int) $this->config['marketplace_boosted_days'] : 7));
				$promotion_id = $this->create_promotion_request($ad, 'boosted', $days, $package);
				$paypal_url = $this->build_paypal_payment_url($promotion_id);
				if ($paypal_url)
				{
					// External PayPal redirects must bypass phpBB's local redirect check.
					\redirect($paypal_url, false, true);
				}
				if ($this->promotion_uses_pix($promotion_id))
				{
					\redirect($this->helper->route('mundophpbb_marketplace_payment', ['context' => 'promotion', 'id' => $promotion_id]));
				}

				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_PROMOTION_REQUEST_SENT') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;


			case 'send_message':
				if ((int) $this->user->data['user_id'] === ANONYMOUS || (int) $ad['ad_status'] !== 1 || (int) $ad['user_id'] === (int) $this->user->data['user_id'])
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$this->send_marketplace_message_from_ad($ad);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_SENT') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'buy':
				if (!$this->can_buy_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$this->create_purchase_request($ad, 0, 'manual');
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_REQUEST_SENT') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			case 'buy_paypal':
				if (!$this->can_buy_ad_with_paypal($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$purchase_id = $this->create_purchase_request($ad, 3, 'paypal');
				$paypal_url = $this->build_paypal_purchase_url($purchase_id);
				if (!$paypal_url)
				{
					\trigger_error($this->language->lang('MARKETPLACE_PAYPAL_NOT_CONFIGURED'));
				}

				// External PayPal redirects must bypass phpBB's local redirect check.
				\redirect($paypal_url, false, true);
			break;

			case 'buy_pix':
				if (!$this->can_buy_ad_with_pix($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}
				$purchase_id = $this->create_purchase_request($ad, 3, 'pix');
				\redirect($this->helper->route('mundophpbb_marketplace_payment', ['context' => 'purchase', 'id' => $purchase_id]));
			break;

			case 'report':
				if (!$this->can_report_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$reason = $this->request->variable('report_reason', '', true);
				$report_type = $this->request->variable('report_type', 'ad');
				$this->create_report($ad, $reason, $report_type);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_REPORT_SENT') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			default:
				\trigger_error($this->language->lang('MARKETPLACE_ACTION_NOT_ALLOWED'));
			break;
		}
	}
}
