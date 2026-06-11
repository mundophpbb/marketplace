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
 * UCP controller for user's own marketplace ads.
 */
class ucp_controller
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
	/** @var string */
	protected $root_path;
	/** @var string */
	protected $php_ext;
	protected $table_ads;
	protected $table_cats;
	protected $table_images;
	protected $table_notifications;
	protected $table_purchases;
	protected $table_follows;
	protected $table_promotions;
	protected $table_payment_logs;
	/** @var string */
	protected $u_action;

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
		$root_path,
		$php_ext,
		$table_ads,
		$table_cats,
		$table_images,
		$table_notifications,
		$table_purchases,
		$table_follows,
		$table_promotions,
		$table_payment_logs
	)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->language = $language;
		$this->user = $user;
		$this->db = $db;
		$this->request = $request;
		$this->pagination = $pagination;
		$this->auth = $auth;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->table_ads = $table_ads;
		$this->table_cats = $table_cats;
		$this->table_images = $table_images;
		$this->table_notifications = $table_notifications;
		$this->table_purchases = $table_purchases;
		$this->table_follows = $table_follows;
		$this->table_promotions = $table_promotions;
		$this->table_payment_logs = $table_payment_logs;
	}

	public function main()
	{
		$this->language->add_lang('common', 'mundophpbb/marketplace');

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		\add_form_key('mundophpbb_marketplace_action');

		$action = $this->request->variable('action', '');
		if (in_array($action, ['mark_notifications_read', 'approve_sale', 'reject_sale', 'unfollow_seller'], true))
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_action'))
			{
				\trigger_error($this->language->lang('FORM_INVALID'));
			}

			if ($action === 'mark_notifications_read')
			{
				$this->mark_notifications_read((int) $this->user->data['user_id']);
				\trigger_error($this->language->lang('MARKETPLACE_NOTIFICATIONS_MARKED_READ') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}

			if ($action === 'unfollow_seller')
			{
				$this->unfollow_seller($this->request->variable('seller_id', 0), (int) $this->user->data['user_id']);
				\trigger_error($this->language->lang('MARKETPLACE_SELLER_UNFOLLOWED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}

			$purchase_id = $this->request->variable('purchase_id', 0);
			$this->handle_sale_purchase_action($action, $purchase_id, (int) $this->user->data['user_id']);
		}

		$start = $this->request->variable('start', 0);
		$per_page = 10;

		$user_id = (int) $this->user->data['user_id'];
		$unread_notifications = $this->count_unread_notifications($user_id);

		$sql = 'SELECT a.*, c.cat_name
				FROM ' . $this->table_ads . ' a
				LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
				WHERE a.user_id = ' . $user_id . '
				ORDER BY a.ad_created DESC';

		$result = $this->db->sql_query_limit($sql, $per_page, $start);

		$my_ads = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => $row['ad_id']]);
			$row['U_ACTION'] = $row['U_VIEW'];
			$row['U_EDIT'] = $this->auth->acl_get('u_marketplace_edit_own') ? $this->helper->route('mundophpbb_marketplace_edit', ['ad_id' => $row['ad_id']]) : '';
			$row['U_DELETE'] = $this->auth->acl_get('u_marketplace_delete_own') ? $this->helper->route('mundophpbb_marketplace_delete', ['ad_id' => $row['ad_id']]) : '';
			$row['AD_PRICE_DISPLAY'] = $this->format_price($row);
			$row['MAIN_IMAGE'] = $this->get_main_image((int) $row['ad_id']);
			$row['cat_name'] = $this->translate_category_text(isset($row['cat_name']) ? $row['cat_name'] : '');
			$row['S_CAN_MARK_SOLD'] = ((int) $row['ad_status'] === 1 && $this->auth->acl_get('u_marketplace_edit_own'));
			$row['S_CAN_MANAGE_STOCK'] = (in_array((int) $row['ad_status'], [1, 2], true) && $this->auth->acl_get('u_marketplace_edit_own'));
			$row['S_CAN_RENEW'] = (in_array((int) $row['ad_status'], [1, 3], true) && $this->auth->acl_get('u_marketplace_edit_own'));
			$row['S_CAN_BUMP'] = $this->can_bump_ad($row);
			$this->prepare_ad_for_display($row);
			$my_ads[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(*) as total FROM ' . $this->table_ads . ' WHERE user_id = ' . $user_id;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$this->pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $total, $per_page, $start);

		$this->template->assign_vars([
			'MY_ADS'        => $my_ads,
			'TOTAL_MY_ADS'  => $total,
			'UNREAD_NOTIFICATIONS' => $unread_notifications,
			'U_POST_NEW'    => $this->helper->route('mundophpbb_marketplace_post'),
			'S_CAN_POST'    => $this->auth->acl_get('u_marketplace_post'),
			'U_ACTION'      => $this->u_action,
		]);
	}


	public function notifications()
	{
		$this->language->add_lang(['common', 'ucp'], 'mundophpbb/marketplace');

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		\add_form_key('mundophpbb_marketplace_action');

		$action = $this->request->variable('action', '');
		if (in_array($action, ['mark_notifications_read', 'approve_sale', 'reject_sale', 'unfollow_seller'], true))
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_action'))
			{
				\trigger_error($this->language->lang('FORM_INVALID'));
			}

			if ($action === 'mark_notifications_read')
			{
				$this->mark_notifications_read((int) $this->user->data['user_id']);
				\trigger_error($this->language->lang('MARKETPLACE_NOTIFICATIONS_MARKED_READ') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}

			if ($action === 'unfollow_seller')
			{
				$this->unfollow_seller($this->request->variable('seller_id', 0), (int) $this->user->data['user_id']);
				\trigger_error($this->language->lang('MARKETPLACE_SELLER_UNFOLLOWED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}

			$purchase_id = $this->request->variable('purchase_id', 0);
			$this->handle_sale_purchase_action($action, $purchase_id, (int) $this->user->data['user_id']);
		}

		$user_id = (int) $this->user->data['user_id'];
		$notifications = $this->get_notifications($user_id, 50);
		$unread_notifications = $this->count_unread_notifications($user_id);
		$pending_sales = $this->get_pending_sales($user_id);
		$followed_sellers = $this->get_followed_sellers($user_id);
		$user_promotions = $this->get_user_promotions($user_id);

		$this->template->assign_vars([
			'NOTIFICATIONS' => $notifications,
			'UNREAD_NOTIFICATIONS' => $unread_notifications,
			'PENDING_SALES' => $pending_sales,
			'S_HAS_PENDING_SALES' => !empty($pending_sales),
			'FOLLOWED_SELLERS' => $followed_sellers,
			'S_HAS_FOLLOWED_SELLERS' => !empty($followed_sellers),
			'USER_PROMOTIONS' => $user_promotions,
			'S_HAS_USER_PROMOTIONS' => !empty($user_promotions),
			'U_ACTION' => $this->u_action,
		]);
	}

	private function can_view_marketplace()
	{
		return $this->auth->acl_get('u_marketplace_view') || $this->auth->acl_get('m_marketplace_edit') || $this->auth->acl_get('m_marketplace_approve') || $this->auth->acl_get('m_marketplace_delete') || $this->auth->acl_get('m_marketplace_feature') || $this->auth->acl_get('m_marketplace_reports');
	}

	private function has_notifications_table()
	{
		return isset($this->config['marketplace_version']) && version_compare((string) $this->config['marketplace_version'], '1.3.0', '>=');
	}


	private function has_follows_table()
	{
		return isset($this->config['marketplace_version']) && version_compare((string) $this->config['marketplace_version'], '1.4.12', '>=');
	}


	private function has_promotions_table()
	{
		return !empty($this->table_promotions) && isset($this->config['marketplace_version']) && version_compare((string) $this->config['marketplace_version'], '1.4.6', '>=');
	}

	private function has_payment_logs_table()
	{
		return !empty($this->table_payment_logs) && isset($this->config['marketplace_version']) && version_compare((string) $this->config['marketplace_version'], '1.4.13', '>=');
	}

	private function get_user_promotions($user_id)
	{
		if (!$this->has_promotions_table())
		{
			return [];
		}

		$sql = 'SELECT p.*, a.ad_title, a.ad_featured_until, a.ad_boosted_until
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE p.user_id = ' . (int) $user_id . '
			ORDER BY p.promotion_requested DESC, p.promotion_id DESC';
		$result = $this->db->sql_query_limit($sql, 50);
		$promotions = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['PROMOTION_TYPE_LANG'] = $this->get_promotion_type_lang($row['promotion_type']);
			$row['PROMOTION_STATUS_LANG'] = $this->get_promotion_status_lang((int) $row['promotion_status']);
			$row['PROMOTION_REQUESTED_DISPLAY'] = !empty($row['promotion_requested']) ? $this->user->format_date((int) $row['promotion_requested']) : '';
			$row['PROMOTION_PRICE_DISPLAY'] = $this->format_purchase_price((int) $row['promotion_amount_cents'], $row['promotion_currency']);
			$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper($row['payment_provider']) : '-';
			$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? $row['payment_reference'] : '-';
			$row['PAYMENT_LAST_STATUS_DISPLAY'] = $this->get_latest_payment_log_status((int) $row['promotion_id']);
			$row['U_VIEW'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$until = ($row['promotion_type'] === 'featured') ? (int) $row['ad_featured_until'] : (int) $row['ad_boosted_until'];
			$row['PROMOTION_UNTIL_DISPLAY'] = $until > 0 ? $this->user->format_date($until) : '-';
			$row['S_PROMOTION_ACTIVE'] = ((int) $row['promotion_status'] === 1 && $until >= time());
			$promotions[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $promotions;
	}

	private function get_latest_payment_log_status($promotion_id)
	{
		if (!$this->has_payment_logs_table())
		{
			return '';
		}

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
			default:
				return $status !== '' ? $status : '-';
		}
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

	private function get_followed_sellers($user_id)
	{
		if (!$this->has_follows_table() || (isset($this->config['marketplace_allow_follows']) && empty($this->config['marketplace_allow_follows'])))
		{
			return [];
		}

		$sql = 'SELECT f.*, u.username, u.user_colour, COUNT(a.ad_id) AS active_ads
			FROM ' . $this->table_follows . ' f
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = f.followed_user_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.user_id = f.followed_user_id AND a.ad_status = 1 AND (a.ad_expires = 0 OR a.ad_expires >= ' . time() . ')
			WHERE f.follower_user_id = ' . (int) $user_id . '
			GROUP BY f.follow_id, f.follower_user_id, f.followed_user_id, f.follow_created, u.username, u.user_colour
			ORDER BY f.follow_created DESC';
		$result = $this->db->sql_query_limit($sql, 20);
		$sellers = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['FOLLOW_CREATED_DISPLAY'] = !empty($row['follow_created']) ? $this->user->format_date((int) $row['follow_created']) : '';
			$row['U_PROFILE'] = \append_sid("{$this->root_path}memberlist.{$this->php_ext}", ['mode' => 'viewprofile', 'u' => (int) $row['followed_user_id']]);
			$sellers[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $sellers;
	}

	private function unfollow_seller($seller_id, $follower_id)
	{
		if (!$this->has_follows_table())
		{
			return;
		}

		$this->db->sql_query('DELETE FROM ' . $this->table_follows . '
			WHERE follower_user_id = ' . (int) $follower_id . '
				AND followed_user_id = ' . (int) $seller_id);
	}

	private function get_notifications($user_id, $limit = 10)
	{
		if (!$this->has_notifications_table())
		{
			return [];
		}

		$sql = 'SELECT n.*, a.ad_title
			FROM ' . $this->table_notifications . ' n
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = n.ad_id
			WHERE n.user_id = ' . (int) $user_id . '
			ORDER BY n.notification_time DESC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		$notifications = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['NOTIFICATION_TIME_DISPLAY'] = !empty($row['notification_time']) ? $this->user->format_date((int) $row['notification_time']) : '';
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$notifications[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $notifications;
	}

	private function count_unread_notifications($user_id)
	{
		if (!$this->has_notifications_table())
		{
			return 0;
		}

		$sql = 'SELECT COUNT(*) AS total
			FROM ' . $this->table_notifications . '
			WHERE user_id = ' . (int) $user_id . '
				AND notification_read = 0';
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	private function mark_notifications_read($user_id)
	{
		if (!$this->has_notifications_table())
		{
			return;
		}

		$this->db->sql_query('UPDATE ' . $this->table_notifications . ' SET notification_read = 1 WHERE user_id = ' . (int) $user_id);
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
		if (empty($this->config['marketplace_allow_bump']) || (int) $ad['ad_status'] !== 1 || !($this->auth->acl_get('u_marketplace_bump_own') || $this->auth->acl_get('u_marketplace_edit_own')))
		{
			return false;
		}

		$next_bump = $this->next_bump_time($ad);
		return $next_bump <= time();
	}

	private function prepare_ad_for_display(&$ad)
	{
		if (isset($ad['cat_name']))
		{
			$ad['cat_name'] = $this->translate_category_text($ad['cat_name']);
		}

		$ad['STATUS_LANG'] = $this->get_status_lang($ad['ad_status']);
		$ad['AD_TYPE_LANG'] = $this->get_ad_type_lang(isset($ad['ad_type']) ? (int) $ad['ad_type'] : 1);
		$ad['AD_CONDITION_LANG'] = $this->get_ad_condition_lang(isset($ad['ad_condition']) ? (int) $ad['ad_condition'] : 0);
		$ad['ad_quantity'] = isset($ad['ad_quantity']) ? max(0, (int) $ad['ad_quantity']) : 1;
		$ad['AD_QUANTITY_LANG'] = $this->format_quantity($ad['ad_quantity']);
		$ad['AD_EXPIRES_DISPLAY'] = '';
		$ad['AD_EXPIRES_IN_LANG'] = '';
		$ad['AD_SOLD_AT_DISPLAY'] = '';
		$ad['AD_EXPIRED_AT_DISPLAY'] = '';
		$ad['AD_FEATURED_UNTIL_DISPLAY'] = '';
		$ad['AD_BOOSTED_UNTIL_DISPLAY'] = '';
		$ad['AD_LAST_BUMPED_DISPLAY'] = '';
		$ad['AD_LAST_RENEWED_DISPLAY'] = '';
		$ad['AD_BUMPED_AT_DISPLAY'] = '';
		$ad['AD_NEXT_BUMP_DISPLAY'] = '';
		$ad['S_IS_FEATURED'] = !empty($ad['ad_featured_until']) && (int) $ad['ad_featured_until'] >= time();
		$ad['S_IS_BOOSTED'] = !empty($ad['ad_boosted_until']) && (int) $ad['ad_boosted_until'] >= time();

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



	private function get_main_image($ad_id)
	{
		$sql = 'SELECT image_id FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id . ' ORDER BY image_is_main DESC, image_order ASC';
		$result = $this->db->sql_query_limit($sql, 1);
		$image_id = (int) $this->db->sql_fetchfield('image_id');
		$this->db->sql_freeresult($result);

		return $image_id ? $this->helper->route('mundophpbb_marketplace_image', [
			'image_id' => $image_id,
			'v' => time(),
		]) : '';
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

		$price_type = isset($ad['ad_price_type']) ? (int) $ad['ad_price_type'] : (!empty($ad['ad_price']) && $ad['ad_price'] !== '0' ? 1 : 2);
		$currency = !empty($ad['ad_currency']) ? $ad['ad_currency'] : $this->config['marketplace_currency_default'];
		$cents = isset($ad['ad_price_cents']) ? (int) $ad['ad_price_cents'] : 0;

		switch ($price_type)
		{
			case 1:
				return $cents > 0 ? $currency . ' ' . number_format($cents / 100, 2, ',', '.') : $this->language->lang('MARKETPLACE_PRICE_NEGOTIABLE');
			case 2:
				return $cents > 0 ? $currency . ' ' . number_format($cents / 100, 2, ',', '.') . ' (' . $this->language->lang('MARKETPLACE_PRICE_TYPE_NEGOTIABLE') . ')' : $this->language->lang('MARKETPLACE_PRICE_NEGOTIABLE');
			case 3:
				return $this->language->lang('MARKETPLACE_PRICE_FREE');
			case 4:
				return $this->language->lang('MARKETPLACE_PRICE_TYPE_ON_REQUEST');
		}

		return $this->language->lang('MARKETPLACE_PRICE_NEGOTIABLE');
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


	private function get_pending_sales($seller_user_id)
	{
		if (empty($this->table_purchases))
		{
			return [];
		}

		$sql = 'SELECT p.*, a.ad_title, a.ad_quantity, b.username AS buyer_username, b.user_colour AS buyer_user_colour
			FROM ' . $this->table_purchases . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' b ON b.user_id = p.buyer_user_id
			WHERE p.seller_user_id = ' . (int) $seller_user_id . '
				AND p.purchase_status IN (0, 3)
			ORDER BY p.purchase_created ASC';
		$result = $this->db->sql_query_limit($sql, 25);
		$sales = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$row['PURCHASE_STATUS_LANG'] = $this->get_purchase_status_lang((int) $row['purchase_status']);
			$row['PURCHASE_CREATED_DISPLAY'] = !empty($row['purchase_created']) ? $this->user->format_date((int) $row['purchase_created']) : '';
			$row['PURCHASE_PRICE_DISPLAY'] = $this->format_purchase_price((int) $row['purchase_amount_cents'], $row['purchase_currency']);
			$row['PAYMENT_REFERENCE_DISPLAY'] = isset($row['payment_reference']) ? (string) $row['payment_reference'] : '';
			$sales[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $sales;
	}

	private function handle_sale_purchase_action($action, $purchase_id, $seller_user_id)
	{
		$purchase = $this->get_sale_purchase($purchase_id, $seller_user_id);
		if (!$purchase || !in_array((int) $purchase['purchase_status'], [0, 3], true))
		{
			\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_FOUND'));
		}

		switch ($action)
		{
			case 'approve_sale':
				$this->apply_purchase_stock_change($purchase);
				$this->update_purchase_status((int) $purchase_id, 1);
				$this->add_notification((int) $purchase['buyer_user_id'], (int) $purchase['ad_id'], 'purchase_approved', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_APPROVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_APPROVED_MESSAGE', $purchase['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_APPROVED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			break;

			case 'reject_sale':
				$this->update_purchase_status((int) $purchase_id, 2);
				$this->add_notification((int) $purchase['buyer_user_id'], (int) $purchase['ad_id'], 'purchase_rejected', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_REJECTED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_REJECTED_MESSAGE', $purchase['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_REJECTED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			break;
		}
	}

	private function get_sale_purchase($purchase_id, $seller_user_id)
	{
		$sql = 'SELECT p.*, a.ad_title, a.ad_quantity
			FROM ' . $this->table_purchases . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE p.purchase_id = ' . (int) $purchase_id . '
				AND p.seller_user_id = ' . (int) $seller_user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$purchase = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $purchase;
	}

	private function apply_purchase_stock_change($purchase)
	{
		$quantity = isset($purchase['ad_quantity']) ? max(0, (int) $purchase['ad_quantity']) : 1;
		$new_quantity = max(0, $quantity - 1);
		$sql_ary = [
			'ad_quantity' => $new_quantity,
			'ad_updated' => time(),
		];
		if ($new_quantity <= 0)
		{
			$sql_ary['ad_status'] = 2;
			$sql_ary['ad_sold_at'] = time();
		}
		$this->db->sql_query('UPDATE ' . $this->table_ads . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE ad_id = ' . (int) $purchase['ad_id']);
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
		$map = [
			0 => 'MARKETPLACE_PURCHASE_STATUS_PENDING',
			1 => 'MARKETPLACE_PURCHASE_STATUS_APPROVED',
			2 => 'MARKETPLACE_PURCHASE_STATUS_REJECTED',
			3 => 'MARKETPLACE_PURCHASE_STATUS_AWAITING_PAYMENT',
		];
		return $this->language->lang($map[$status] ?? 'MARKETPLACE_PURCHASE_STATUS_PENDING');
	}

	private function format_purchase_price($amount_cents, $currency)
	{
		$currency = preg_replace('/[^A-Z]/', '', strtoupper((string) $currency));
		if (strlen($currency) !== 3)
		{
			$currency = 'BRL';
		}
		return $currency . ' ' . number_format(max(0, (int) $amount_cents) / 100, 2, ',', '.');
	}

	private function add_notification($user_id, $ad_id, $type, $title, $message)
	{
		$user_id = (int) $user_id;
		if ($user_id <= 0 || $user_id === ANONYMOUS || !$this->has_notifications_table())
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

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
