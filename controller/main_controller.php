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
		$table_notifications
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
			$row['U_POSTER'] = \append_sid("{$this->root_path}memberlist.{$this->php_ext}", ['mode' => 'viewprofile', 'u' => $row['user_id']]);
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

		$sql = 'SELECT a.*, u.username, u.user_colour, u.user_id as poster_id, c.cat_name, c.cat_id
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

		$ad['AD_PRICE_DISPLAY'] = $this->format_price($ad);
		$ad['cat_name'] = $this->translate_category_text(isset($ad['cat_name']) ? $ad['cat_name'] : '');
		$ad['cat_desc'] = $this->translate_category_text(isset($ad['cat_desc']) ? $ad['cat_desc'] : '');
		$ad['U_CATEGORY'] = $this->helper->route('mundophpbb_marketplace_category', ['cat_id' => (int) $ad['cat_id']]);
		$ad['AD_DESC_HTML'] = $this->render_description($ad['ad_desc']);
		$ad['U_POSTER'] = \append_sid("{$this->root_path}memberlist.{$this->php_ext}", ['mode' => 'viewprofile', 'u' => $ad['user_id']]);
		$ad['U_PM'] = ((int) $this->user->data['user_id'] !== ANONYMOUS && (int) $ad['ad_status'] === 1 && !$is_owner) ? \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => 'pm', 'mode' => 'compose', 'u' => $ad['user_id'], 'subject' => $this->language->lang('MARKETPLACE_PM_SUBJECT', $ad['ad_title'])]) : '';
		$ad['U_EDIT'] = $this->can_edit_ad($ad) ? $this->helper->route('mundophpbb_marketplace_edit', ['ad_id' => $ad_id]) : '';
		$ad['U_DELETE'] = $this->can_delete_ad($ad) ? $this->helper->route('mundophpbb_marketplace_delete', ['ad_id' => $ad_id]) : '';
		$ad['U_ACTION'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $ad_id]);
		$this->prepare_ad_for_display($ad);

		$this->template->assign_vars([
			'AD'               => $ad,
			'IMAGES'           => $images,
			'S_CAN_CONTACT'    => ((int) $this->user->data['user_id'] !== ANONYMOUS && (int) $ad['ad_status'] === 1 && !$is_owner),
			'S_OWN_AD'         => $is_owner,
			'S_IS_MOD'         => $is_mod,
			'S_CAN_APPROVE'    => $this->can_approve_ad($ad),
			'S_CAN_MARK_SOLD'  => $this->can_mark_sold($ad),
			'S_CAN_MANAGE_STOCK' => $this->can_manage_stock($ad),
			'S_CAN_RENEW'      => $this->can_renew_ad($ad),
			'S_CAN_BUMP'       => $this->can_bump_ad($ad),
			'S_CAN_FEATURE'    => $this->can_feature_ad($ad),
			'S_CAN_UNFEATURE'  => $this->can_unfeature_ad($ad),
			'S_CAN_REPORT'     => $this->can_report_ad($ad),
			'S_SHOW_PRICE'     => (bool) $this->config['marketplace_enable_price'],
		]);

		return $this->helper->render('@mundophpbb_marketplace/marketplace_view.html', $ad['ad_title']);
	}

	/**
	 * Serve an uploaded marketplace image through phpBB routing.
	 */
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
			$ad_phone   = $this->request->variable('ad_phone', '', true);
			$contact_method = $this->request->variable('contact_method', 1);
			$ad_type = $this->sanitize_ad_type($this->request->variable('ad_type', 1));
			$ad_condition = $this->sanitize_ad_condition($this->request->variable('ad_condition', 0));
			$ad_quantity = $this->sanitize_quantity($this->request->variable('ad_quantity', 1));
			$ad_price_type = $this->sanitize_price_type($this->request->variable('ad_price_type', 2));
			$ad_price_cents = in_array($ad_price_type, [3, 4], true) ? 0 : $this->parse_price_amount($ad_price);
			$selected_category = $cat_id > 0 ? $this->get_category($cat_id) : false;
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
			}

			// Check max ads for new ads.
			if (!$is_edit && !$this->auth->acl_get('m_marketplace_edit'))
			{
				$sql = 'SELECT COUNT(*) as cnt FROM ' . $this->table_ads . ' WHERE user_id = ' . (int) $this->user->data['user_id'] . ' AND ad_status IN (0,1)';
				$result = $this->db->sql_query($sql);
				$count = (int) $this->db->sql_fetchfield('cnt');
				$this->db->sql_freeresult($result);

				if ($count >= (int) $this->config['marketplace_max_ads_per_user'])
				{
					$errors[] = $this->language->lang('MARKETPLACE_MAX_ADS_REACHED', (int) $this->config['marketplace_max_ads_per_user']);
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
					'ad_phone'          => $ad_phone,
					'ad_contact_method' => $contact_method,
					'ad_updated'        => $now,
				];

				// These fields were introduced in v1.2.0. Keep submit compatible
				// with databases that are recovering from a partial migration run.
				$package2_fields = [
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

				if ($is_edit)
				{
					$sql = 'UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ad_id = ' . $ad_id;
					$this->db->sql_query($sql);

					if ((int) $ad['user_id'] === (int) $this->user->data['user_id'] && $this->config['marketplace_require_approval'] && (int) $ad['ad_status'] === 1 && !$this->auth->acl_get('m_marketplace_edit'))
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
					$new_status = $this->config['marketplace_require_approval'] ? 0 : 1;
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
					]);
					$sql = 'INSERT INTO ' . $this->table_ads . ' ' . $this->db->sql_build_array('INSERT', $data);
					$this->db->sql_query($sql);
					$ad_id = (int) $this->db->sql_nextid();
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
				'ad_phone'        => $this->request->variable('ad_phone', '', true),
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
			'ad_phone'          => '',
			'ad_contact_method' => 1,
		], $ad ?: []);

		$categories = $this->get_categories(true);
		$current_images = $is_edit ? $this->get_ad_images($ad_id) : [];
		$available_slots = (int) $this->config['marketplace_max_images'];
		if ($is_edit)
		{
			$available_slots = max(0, (int) $this->config['marketplace_max_images'] - $this->get_image_count($ad_id));
		}

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

		if ($this->column_exists($this->table_ads, 'ad_featured_until'))
		{
			$order_prefix = 'CASE WHEN a.ad_featured_until >= ' . $now . ' THEN 1 ELSE 0 END DESC, ';
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
		foreach (['q', 'location', 'price_min_raw', 'price_max_raw'] as $key)
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
		$ad['AD_LAST_BUMPED_DISPLAY'] = '';
		$ad['AD_BUMPED_AT_DISPLAY'] = '';
		$ad['AD_NEXT_BUMP_DISPLAY'] = '';
		$ad['S_IS_FEATURED'] = !empty($ad['ad_featured_until']) && (int) $ad['ad_featured_until'] >= time();

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
		return in_array($status, [1, 3], true) && (($is_owner && $this->auth->acl_get('u_marketplace_edit_own')) || $this->auth->acl_get('m_marketplace_edit'));
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
		return (int) $ad['ad_status'] === 1 && ($this->auth->acl_get('m_marketplace_feature') || $this->auth->acl_get('m_marketplace_edit')) && (empty($ad['ad_featured_until']) || (int) $ad['ad_featured_until'] < time());
	}

	private function can_unfeature_ad($ad)
	{
		return ($this->auth->acl_get('m_marketplace_feature') || $this->auth->acl_get('m_marketplace_edit')) && !empty($ad['ad_featured_until']) && (int) $ad['ad_featured_until'] >= time();
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

	private function create_report($ad, $reason)
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

		$sql_ary = [
			'ad_id'          => (int) $ad['ad_id'],
			'reporter_id'    => (int) $this->user->data['user_id'],
			'report_reason'  => $reason,
			'report_status'  => 0,
			'report_created' => time(),
			'report_note'    => '',
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_reports . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
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
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_AD_APPROVED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
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

			case 'report':
				if (!$this->can_report_ad($ad))
				{
					\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
				}

				$reason = $this->request->variable('report_reason', '', true);
				$this->create_report($ad, $reason);
				\meta_refresh(2, $redirect);
				\trigger_error($this->language->lang('MARKETPLACE_REPORT_SENT') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));
			break;

			default:
				\trigger_error($this->language->lang('MARKETPLACE_ACTION_NOT_ALLOWED'));
			break;
		}
	}
}
