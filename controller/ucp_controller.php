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
		$table_notifications
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
		if ($action === 'mark_notifications_read')
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_action'))
			{
				\trigger_error($this->language->lang('FORM_INVALID'));
			}

			$this->mark_notifications_read((int) $this->user->data['user_id']);
			\trigger_error($this->language->lang('MARKETPLACE_NOTIFICATIONS_MARKED_READ') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
		}

		$start = $this->request->variable('start', 0);
		$per_page = 10;

		$user_id = (int) $this->user->data['user_id'];
		$notifications = $this->get_notifications($user_id);
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
			'NOTIFICATIONS' => $notifications,
			'UNREAD_NOTIFICATIONS' => $unread_notifications,
			'U_POST_NEW'    => $this->helper->route('mundophpbb_marketplace_post'),
			'S_CAN_POST'    => $this->auth->acl_get('u_marketplace_post'),
			'U_ACTION'      => $this->u_action,
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

	private function get_notifications($user_id)
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
		$result = $this->db->sql_query_limit($sql, 10);
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

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
