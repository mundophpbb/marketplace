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
	protected $table_reviews;
	protected $table_follows;
	protected $table_promotions;
	protected $table_payment_logs;
	protected $table_conversations;
	protected $table_messages;
	protected $table_message_blocks;
	protected $table_favorites;
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
		$this->table_reviews = preg_replace('/marketplace_purchases$/', 'marketplace_reviews', $table_purchases);
		$this->table_follows = $table_follows;
		$this->table_promotions = $table_promotions;
		$this->table_payment_logs = $table_payment_logs;
		$this->table_conversations = preg_replace('/marketplace_ads$/', 'marketplace_conversations', $table_ads);
		$this->table_messages = preg_replace('/marketplace_ads$/', 'marketplace_messages', $table_ads);
		$this->table_message_blocks = preg_replace('/marketplace_ads$/', 'marketplace_message_blocks', $table_ads);
		$this->table_favorites = preg_replace('/marketplace_ads$/', 'marketplace_favorites', $table_ads);
	}

	public function main()
	{
		$this->language->add_lang(['common', 'ucp'], 'mundophpbb/marketplace');

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		$user_id = (int) $this->user->data['user_id'];
		$stats = $this->get_ucp_summary_stats($user_id);

		$this->template->assign_vars([
			'U_MARKETPLACE_ADS' => \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'ads']),
			'U_MARKETPLACE_PROMOTIONS' => \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'promotions']),
			'U_MARKETPLACE_NOTIFICATIONS' => \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'notifications']),
			'U_MARKETPLACE_PURCHASES' => \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'purchases']),
			'U_MARKETPLACE_SALES' => \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'sales']),
			'U_MARKETPLACE_FAVORITES' => \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'favorites']),
			'U_MARKETPLACE_CONVERSATIONS' => \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'conversations']),
			'U_MARKETPLACE_PAYMENTS' => \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'payments']),
			'U_POST_NEW' => $this->helper->route('mundophpbb_marketplace_post'),
			'S_CAN_POST' => $this->auth->acl_get('u_marketplace_post'),
			'U_ACTION' => $this->u_action,
			'MP_SUMMARY' => $stats,
		]);
	}


	public function ads()
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
			$row['S_CAN_RENEW'] = (in_array((int) $row['ad_status'], [1, 3, 4], true) && $this->auth->acl_get('u_marketplace_edit_own'));
			$row['S_CAN_PAUSE'] = ((int) $row['ad_status'] === 1 && $this->auth->acl_get('u_marketplace_edit_own'));
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
		if (in_array($action, ['mark_notifications_read', 'delete_notification', 'delete_old_notifications', 'approve_sale', 'reject_sale', 'unfollow_seller'], true))
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_action'))
			{
				\trigger_error($this->language->lang('FORM_INVALID'));
			}

			$user_id = (int) $this->user->data['user_id'];

			if ($action === 'mark_notifications_read')
			{
				$this->mark_notifications_read($user_id);
				\trigger_error($this->language->lang('MARKETPLACE_NOTIFICATIONS_MARKED_READ') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}

			if ($action === 'delete_notification')
			{
				$this->delete_notification($this->request->variable('notification_id', 0), $user_id);
				\trigger_error($this->language->lang('MARKETPLACE_NOTIFICATION_DELETED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}

			if ($action === 'delete_old_notifications')
			{
				$days = max(1, $this->request->variable('days', 90));
				$this->delete_old_notifications($user_id, $days);
				\trigger_error($this->language->lang('MARKETPLACE_OLD_NOTIFICATIONS_DELETED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}

			if ($action === 'unfollow_seller')
			{
				$this->unfollow_seller($this->request->variable('seller_id', 0), $user_id);
				\trigger_error($this->language->lang('MARKETPLACE_SELLER_UNFOLLOWED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}

			$purchase_id = $this->request->variable('purchase_id', 0);
			if ($purchase_id)
			{
				$this->handle_sale_purchase_action($action, $purchase_id, $user_id);
			}
		}

		$user_id = (int) $this->user->data['user_id'];
		$filter_type = $this->request->variable('notification_type', '');
		$filter_read = $this->request->variable('notification_read', '');
		$notifications = $this->get_notifications($user_id, 50, $filter_type, $filter_read);
		$notification_types = $this->get_notification_types($user_id);
		$unread_notifications = $this->count_unread_notifications($user_id);
		$pending_sales = $this->get_pending_sales($user_id);
		$followed_sellers = $this->get_followed_sellers($user_id);

		$this->template->assign_vars([
			'NOTIFICATIONS' => $notifications,
			'NOTIFICATION_TYPES' => $notification_types,
			'S_FILTER_NOTIFICATION_TYPE' => $filter_type,
			'S_FILTER_NOTIFICATION_READ' => $filter_read,
			'UNREAD_NOTIFICATIONS' => $unread_notifications,
			'PENDING_SALES' => $pending_sales,
			'S_HAS_PENDING_SALES' => !empty($pending_sales),
			'FOLLOWED_SELLERS' => $followed_sellers,
			'S_HAS_FOLLOWED_SELLERS' => !empty($followed_sellers),
			'U_ACTION' => $this->u_action,
		]);
	}


	public function purchases()
	{
		$this->language->add_lang(['common', 'ucp'], 'mundophpbb/marketplace');

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		\add_form_key('mundophpbb_marketplace_action');
		$user_id = (int) $this->user->data['user_id'];
		$action = $this->request->variable('action', '');

		if (in_array($action, ['cancel_purchase', 'complete_purchase', 'submit_rating', 'report_rating'], true))
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_action'))
			{
				\trigger_error($this->language->lang('FORM_INVALID'));
			}

			if ($action === 'submit_rating')
			{
				$this->submit_purchase_rating($this->request->variable('purchase_id', 0), $user_id, 'buyer');
			}
			else if ($action === 'report_rating')
			{
				$this->report_review($this->request->variable('review_id', 0), $user_id);
			}
			else
			{
				$this->handle_buyer_purchase_action($action, $this->request->variable('purchase_id', 0), $user_id);
			}
		}

		$filter_status = $this->request->variable('status', -1);
		$purchases = $this->get_purchase_history($user_id, $filter_status);

		$this->template->assign_vars([
			'PURCHASES' => $purchases,
			'S_HAS_PURCHASES' => !empty($purchases),
			'S_FILTER_STATUS' => $filter_status,
			'U_ACTION' => $this->u_action,
		]);
	}

	public function sales()
	{
		$this->language->add_lang(['common', 'ucp'], 'mundophpbb/marketplace');

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		\add_form_key('mundophpbb_marketplace_action');
		$user_id = (int) $this->user->data['user_id'];
		$action = $this->request->variable('action', '');

		if (in_array($action, ['approve_sale', 'reject_sale', 'complete_sale', 'submit_rating', 'report_rating'], true))
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_action'))
			{
				\trigger_error($this->language->lang('FORM_INVALID'));
			}

			if ($action === 'submit_rating')
			{
				$this->submit_purchase_rating($this->request->variable('purchase_id', 0), $user_id, 'seller');
			}
			else if ($action === 'report_rating')
			{
				$this->report_review($this->request->variable('review_id', 0), $user_id);
			}
			else
			{
				$this->handle_sale_purchase_action($action, $this->request->variable('purchase_id', 0), $user_id);
			}
		}

		$filter_status = $this->request->variable('status', -1);
		$sales = $this->get_sales_history($user_id, $filter_status);

		$this->template->assign_vars([
			'SALES' => $sales,
			'S_HAS_SALES' => !empty($sales),
			'S_FILTER_STATUS' => $filter_status,
			'U_ACTION' => $this->u_action,
		]);
	}


	public function promotions()
	{
		$this->language->add_lang(['common', 'ucp'], 'mundophpbb/marketplace');

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		$user_id = (int) $this->user->data['user_id'];
		$filter_status = $this->request->variable('status', -1);
		$promotions = $this->get_user_promotions($user_id, $filter_status);

		$this->template->assign_vars([
			'USER_PROMOTIONS' => $promotions,
			'S_HAS_USER_PROMOTIONS' => !empty($promotions),
			'S_FILTER_STATUS' => $filter_status,
			'U_ACTION' => $this->u_action,
		]);
	}

	public function favorites()
	{
		$this->language->add_lang(['common', 'ucp'], 'mundophpbb/marketplace');

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		\add_form_key('mundophpbb_marketplace_action');
		$user_id = (int) $this->user->data['user_id'];
		$action = $this->request->variable('action', '');

		if (in_array($action, ['remove_favorite', 'unfollow_seller'], true))
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_action'))
			{
				\trigger_error($this->language->lang('FORM_INVALID'));
			}
			if ($action === 'unfollow_seller')
			{
				$this->unfollow_seller($this->request->variable('seller_id', 0), $user_id);
				\trigger_error($this->language->lang('MARKETPLACE_SELLER_UNFOLLOWED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}
			$this->remove_favorite($this->request->variable('ad_id', 0), $user_id);
			\trigger_error($this->language->lang('MARKETPLACE_FAVORITE_REMOVED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
		}

		$favorites = $this->get_user_favorites($user_id);
		$followed_sellers = $this->get_followed_sellers($user_id);

		$this->template->assign_vars([
			'FAVORITES' => $favorites,
			'S_HAS_FAVORITES' => !empty($favorites),
			'FOLLOWED_SELLERS' => $followed_sellers,
			'S_HAS_FOLLOWED_SELLERS' => !empty($followed_sellers),
			'U_ACTION' => $this->u_action,
		]);
	}

	public function payments()
	{
		$this->language->add_lang(['common', 'ucp'], 'mundophpbb/marketplace');

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		$user_id = (int) $this->user->data['user_id'];
		$payments = $this->get_user_payment_history($user_id);

		$this->template->assign_vars([
			'PAYMENTS' => $payments,
			'S_HAS_PAYMENTS' => !empty($payments),
			'U_ACTION' => $this->u_action,
		]);
	}


	public function conversations()
	{
		$this->language->add_lang(['common', 'ucp'], 'mundophpbb/marketplace');

		if (!$this->can_view_marketplace())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		if (!$this->has_messages_table())
		{
			\trigger_error($this->language->lang('MARKETPLACE_NO_PERMISSION'));
		}

		\add_form_key('mundophpbb_marketplace_message');
		$user_id = (int) $this->user->data['user_id'];
		$action = $this->request->variable('action', '');
		if (in_array($action, ['reply_message', 'mark_conversation_read', 'block_contact', 'report_message'], true))
		{
			if (!$this->request->is_set_post('submit_action') || !\check_form_key('mundophpbb_marketplace_message'))
			{
				\trigger_error($this->language->lang('FORM_INVALID'));
			}

			$conversation_id = $this->request->variable('conversation_id', 0);
			if ($action === 'reply_message')
			{
				$this->reply_marketplace_conversation($conversation_id, $user_id);
				\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_SENT') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}
			else if ($action === 'mark_conversation_read')
			{
				$this->mark_conversation_read($conversation_id, $user_id);
				\trigger_error($this->language->lang('MARKETPLACE_CONVERSATION_MARKED_READ') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}
			else if ($action === 'block_contact')
			{
				$this->block_conversation_contact($conversation_id, $user_id);
				\trigger_error($this->language->lang('MARKETPLACE_CONTACT_BLOCKED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}
			else if ($action === 'report_message')
			{
				$this->report_marketplace_message($this->request->variable('message_id', 0), $user_id);
				\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_REPORTED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}
		}

		$filter = $this->request->variable('filter', 'all');
		$conversations = $this->get_user_conversations($user_id, $filter);
		$this->template->assign_vars([
			'CONVERSATIONS' => $conversations,
			'S_HAS_CONVERSATIONS' => !empty($conversations),
			'S_FILTER_CONVERSATION' => $filter,
			'U_ACTION' => $this->u_action,
		]);
	}



	private function get_ucp_summary_stats($user_id)
	{
		$user_id = (int) $user_id;
		$stats = [
			'ADS_TOTAL' => $this->count_user_rows($this->table_ads, 'user_id = ' . $user_id),
			'ADS_ACTIVE' => $this->count_user_rows($this->table_ads, 'user_id = ' . $user_id . ' AND ad_status = 1'),
			'ADS_PENDING' => $this->count_user_rows($this->table_ads, 'user_id = ' . $user_id . ' AND ad_status = 0'),
			'PROMOTIONS' => $this->has_promotions_table() ? $this->count_user_rows($this->table_promotions, 'user_id = ' . $user_id) : 0,
			'UNREAD_NOTIFICATIONS' => $this->count_unread_notifications($user_id),
			'PURCHASES' => $this->count_user_rows($this->table_purchases, 'buyer_user_id = ' . $user_id),
			'SALES' => $this->count_user_rows($this->table_purchases, 'seller_user_id = ' . $user_id),
			'FAVORITES' => $this->table_exists($this->table_favorites) ? $this->count_user_rows($this->table_favorites, 'user_id = ' . $user_id) : 0,
			'MESSAGES' => $this->has_messages_table() ? $this->count_user_rows($this->table_messages, 'recipient_user_id = ' . $user_id . ' AND message_read = 0') : 0,
			'PAYMENTS' => $this->has_payment_logs_table() ? $this->count_user_payment_logs($user_id) : 0,
		];

		return $stats;
	}

	private function count_user_rows($table, $where)
	{
		if (empty($table) || !$this->table_exists($table))
		{
			return 0;
		}

		$sql = 'SELECT COUNT(*) AS total FROM ' . $table . ' WHERE ' . $where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	private function count_user_payment_logs($user_id)
	{
		$sql = 'SELECT COUNT(*) AS total
			FROM ' . $this->table_payment_logs . ' l
			LEFT JOIN ' . $this->table_promotions . ' p ON p.promotion_id = l.promotion_id
			WHERE p.user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
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

	private function get_user_promotions($user_id, $filter_status = -1)
	{
		if (!$this->has_promotions_table())
		{
			return [];
		}

		$where = 'p.user_id = ' . (int) $user_id;
		if ((int) $filter_status >= 0)
		{
			$where .= ' AND p.promotion_status = ' . (int) $filter_status;
		}

		$sql = 'SELECT p.*, a.ad_title, a.ad_featured_until, a.ad_boosted_until
			FROM ' . $this->table_promotions . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE ' . $where . '
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
			case 'boost_bundle':
				return $this->language->lang('MARKETPLACE_PACKAGE_TYPE_BOOST_BUNDLE');
			case 'ad_quota':
				return $this->language->lang('MARKETPLACE_PACKAGE_TYPE_AD_QUOTA');
			case 'seller_plan':
				return $this->language->lang('MARKETPLACE_PACKAGE_TYPE_SELLER_PLAN');
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

	private function get_notifications($user_id, $limit = 10, $filter_type = '', $filter_read = '')
	{
		if (!$this->has_notifications_table())
		{
			return [];
		}

		$where = ['n.user_id = ' . (int) $user_id];
		if ($filter_type !== '')
		{
			$where[] = "n.notification_type = '" . $this->db->sql_escape($filter_type) . "'";
		}
		if ($filter_read !== '' && in_array($filter_read, ['0', '1'], true))
		{
			$where[] = 'n.notification_read = ' . (int) $filter_read;
		}

		$sql = 'SELECT n.*, a.ad_title
			FROM ' . $this->table_notifications . ' n
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = n.ad_id
			WHERE ' . implode(' AND ', $where) . '
			ORDER BY n.notification_time DESC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		$notifications = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['NOTIFICATION_TIME_DISPLAY'] = !empty($row['notification_time']) ? $this->user->format_date((int) $row['notification_time']) : '';
			$row['NOTIFICATION_TYPE_LANG'] = $this->get_notification_type_lang($row['notification_type']);
			$row['U_AD'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$row['U_CONVERSATION'] = $this->get_notification_conversation_url($row, (int) $user_id);
			$notifications[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $notifications;
	}

	private function get_notification_conversation_url($notification, $user_id)
	{
		$type = isset($notification['notification_type']) ? (string) $notification['notification_type'] : '';
		$ad_id = isset($notification['ad_id']) ? (int) $notification['ad_id'] : 0;
		if ($ad_id <= 0 || !in_array($type, ['message_received', 'message_reply'], true) || !$this->has_messages_table())
		{
			return '';
		}

		$sql = 'SELECT conversation_id
			FROM ' . $this->table_conversations . '
			WHERE ad_id = ' . $ad_id . '
				AND (buyer_user_id = ' . (int) $user_id . ' OR seller_user_id = ' . (int) $user_id . ')
			ORDER BY last_message_time DESC, conversation_updated DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$conversation_id = (int) $this->db->sql_fetchfield('conversation_id');
		$this->db->sql_freeresult($result);

		$url = \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'conversations']);
		return $conversation_id ? $url . '#mp-conversation-' . $conversation_id : $url;
	}

	private function get_notification_types($user_id)
	{
		if (!$this->has_notifications_table())
		{
			return [];
		}

		$sql = 'SELECT DISTINCT notification_type
			FROM ' . $this->table_notifications . "
			WHERE user_id = " . (int) $user_id . "
				AND notification_type <> ''
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

	private function get_notification_type_lang($type)
	{
		$key = 'MARKETPLACE_NOTIFICATION_TYPE_' . strtoupper(preg_replace('/[^A-Z0-9_]/i', '_', (string) $type));
		$label = $this->language->lang($key);
		return ($label === $key) ? (string) $type : $label;
	}

	private function delete_notification($notification_id, $user_id)
	{
		if (!$this->has_notifications_table() || $notification_id <= 0)
		{
			return;
		}

		$this->db->sql_query('DELETE FROM ' . $this->table_notifications . ' WHERE notification_id = ' . (int) $notification_id . ' AND user_id = ' . (int) $user_id);
	}

	private function delete_old_notifications($user_id, $days)
	{
		if (!$this->has_notifications_table())
		{
			return;
		}

		$cutoff = time() - (max(1, (int) $days) * 86400);
		$this->db->sql_query('DELETE FROM ' . $this->table_notifications . ' WHERE user_id = ' . (int) $user_id . ' AND notification_time > 0 AND notification_time < ' . (int) $cutoff);
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



	private function has_reviews_table()
	{
		return !empty($this->table_reviews) && isset($this->config['marketplace_version']) && version_compare((string) $this->config['marketplace_version'], '1.4.20', '>=');
	}

	private function submit_purchase_rating($purchase_id, $user_id, $role)
	{
		if (!$this->has_reviews_table())
		{
			\trigger_error($this->language->lang('MARKETPLACE_REVIEW_NOT_AVAILABLE'));
		}

		$purchase = ($role === 'seller') ? $this->get_sale_purchase($purchase_id, $user_id) : $this->get_buyer_purchase($purchase_id, $user_id);
		if (!$purchase || (int) $purchase['purchase_status'] !== 5)
		{
			\trigger_error($this->language->lang('MARKETPLACE_REVIEW_ONLY_COMPLETED'));
		}
		if ($this->has_user_reviewed_purchase((int) $purchase_id, (int) $user_id, $role))
		{
			\trigger_error($this->language->lang('MARKETPLACE_REVIEW_ALREADY_SENT'));
		}

		$score = max(1, min(5, $this->request->variable('review_score', 0)));
		$comment = trim($this->request->variable('review_comment', '', true));
		$reviewed_user_id = ($role === 'seller') ? (int) $purchase['buyer_user_id'] : (int) $purchase['seller_user_id'];
		if ($reviewed_user_id <= 0 || $reviewed_user_id === (int) $user_id)
		{
			\trigger_error($this->language->lang('MARKETPLACE_REVIEW_NOT_AVAILABLE'));
		}

		$sql_ary = [
			'purchase_id' => (int) $purchase_id,
			'ad_id' => (int) $purchase['ad_id'],
			'reviewer_user_id' => (int) $user_id,
			'reviewed_user_id' => $reviewed_user_id,
			'reviewer_role' => $role,
			'review_score' => $score,
			'review_comment' => $comment,
			'review_time' => time(),
			'review_reported' => 0,
			'review_report_reason' => '',
			'review_reported_by' => 0,
			'review_reported_time' => 0,
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_reviews . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));

		$type = ($role === 'seller') ? 'buyer_reviewed' : 'seller_reviewed';
		$this->add_notification($reviewed_user_id, (int) $purchase['ad_id'], $type, $this->language->lang('MARKETPLACE_NOTIFICATION_REVIEW_RECEIVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_REVIEW_RECEIVED_MESSAGE', $purchase['ad_title']));
		\trigger_error($this->language->lang('MARKETPLACE_REVIEW_SENT') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
	}

	private function report_review($review_id, $user_id)
	{
		if (!$this->has_reviews_table())
		{
			\trigger_error($this->language->lang('MARKETPLACE_REVIEW_NOT_AVAILABLE'));
		}
		$reason = trim($this->request->variable('review_report_reason', '', true));
		if ($reason === '')
		{
			$reason = $this->language->lang('MARKETPLACE_REVIEW_REPORT_DEFAULT_REASON');
		}
		$sql_ary = [
			'review_reported' => 1,
			'review_report_reason' => $reason,
			'review_reported_by' => (int) $user_id,
			'review_reported_time' => time(),
		];
		$this->db->sql_query('UPDATE ' . $this->table_reviews . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE review_id = ' . (int) $review_id . ' AND reviewed_user_id = ' . (int) $user_id);
		\trigger_error($this->language->lang('MARKETPLACE_REVIEW_REPORTED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
	}

	private function has_user_reviewed_purchase($purchase_id, $user_id, $role)
	{
		if (!$this->has_reviews_table())
		{
			return true;
		}
		$sql = 'SELECT review_id FROM ' . $this->table_reviews . '
			WHERE purchase_id = ' . (int) $purchase_id . '
				AND reviewer_user_id = ' . (int) $user_id . "
				AND reviewer_role = '" . $this->db->sql_escape($role) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$review_id = (int) $this->db->sql_fetchfield('review_id');
		$this->db->sql_freeresult($result);
		return $review_id > 0;
	}

	private function get_purchase_review_by_user($purchase_id, $user_id, $role)
	{
		return $this->get_purchase_review($purchase_id, 'reviewer_user_id', $user_id, $role);
	}

	private function get_purchase_review_for_user($purchase_id, $user_id, $context)
	{
		$other_role = ($context === 'seller') ? 'buyer' : 'seller';
		return $this->get_purchase_review($purchase_id, 'reviewed_user_id', $user_id, $other_role);
	}

	private function get_purchase_review($purchase_id, $user_field, $user_id, $role)
	{
		if (!$this->has_reviews_table())
		{
			return false;
		}
		$sql = 'SELECT * FROM ' . $this->table_reviews . '
			WHERE purchase_id = ' . (int) $purchase_id . '
				AND ' . $user_field . ' = ' . (int) $user_id . "
				AND reviewer_role = '" . $this->db->sql_escape($role) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if ($row)
		{
			$row['REVIEW_TIME_DISPLAY'] = !empty($row['review_time']) ? $this->user->format_date((int) $row['review_time']) : '';
			$row['REVIEW_SCORE_STARS'] = str_repeat('★', max(0, (int) $row['review_score'])) . str_repeat('☆', max(0, 5 - (int) $row['review_score']));
			$row['S_CAN_REPORT_REVIEW'] = ((int) $row['reviewed_user_id'] === (int) $user_id && empty($row['review_reported']));
		}
		return $row;
	}


	private function get_purchase_history($buyer_user_id, $filter_status = -1)
	{
		if (empty($this->table_purchases))
		{
			return [];
		}

		$where = ['p.buyer_user_id = ' . (int) $buyer_user_id];
		if ((int) $filter_status >= 0)
		{
			$where[] = 'p.purchase_status = ' . (int) $filter_status;
		}

		$sql = 'SELECT p.*, a.ad_title, a.ad_status, s.username AS seller_username, s.user_colour AS seller_user_colour
			FROM ' . $this->table_purchases . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' s ON s.user_id = p.seller_user_id
			WHERE ' . implode(' AND ', $where) . '
			ORDER BY p.purchase_created DESC, p.purchase_id DESC';
		$result = $this->db->sql_query_limit($sql, 100);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row = $this->prepare_purchase_row($row, 'buyer');
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	private function get_sales_history($seller_user_id, $filter_status = -1)
	{
		if (empty($this->table_purchases))
		{
			return [];
		}

		$where = ['p.seller_user_id = ' . (int) $seller_user_id];
		if ((int) $filter_status >= 0)
		{
			$where[] = 'p.purchase_status = ' . (int) $filter_status;
		}

		$sql = 'SELECT p.*, a.ad_title, a.ad_status, a.ad_quantity, b.username AS buyer_username, b.user_colour AS buyer_user_colour
			FROM ' . $this->table_purchases . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			LEFT JOIN ' . USERS_TABLE . ' b ON b.user_id = p.buyer_user_id
			WHERE ' . implode(' AND ', $where) . '
			ORDER BY p.purchase_created DESC, p.purchase_id DESC';
		$result = $this->db->sql_query_limit($sql, 100);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row = $this->prepare_purchase_row($row, 'seller');
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	private function prepare_purchase_row($row, $context)
	{
		$row['U_VIEW'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
		$row['PURCHASE_STATUS_LANG'] = $this->get_purchase_status_lang((int) $row['purchase_status']);
		$row['PURCHASE_CREATED_DISPLAY'] = !empty($row['purchase_created']) ? $this->user->format_date((int) $row['purchase_created']) : '';
		$row['PURCHASE_DECIDED_DISPLAY'] = !empty($row['purchase_decided']) ? $this->user->format_date((int) $row['purchase_decided']) : '';
		$row['PURCHASE_PRICE_DISPLAY'] = $this->format_purchase_price((int) $row['purchase_amount_cents'], $row['purchase_currency']);
		$row['PAYMENT_REFERENCE_DISPLAY'] = !empty($row['payment_reference']) ? (string) $row['payment_reference'] : '-';
		$row['PAYMENT_PROVIDER_LANG'] = !empty($row['payment_provider']) ? strtoupper((string) $row['payment_provider']) : '-';
		$row['U_PAYMENT'] = ($context === 'buyer' && strtolower((string) $row['payment_provider']) === 'pix' && (int) $row['purchase_status'] === 3) ? $this->helper->route('mundophpbb_marketplace_payment', ['context' => 'purchase', 'id' => (int) $row['purchase_id']]) : '';
		$row['S_CAN_CANCEL'] = ($context === 'buyer' && in_array((int) $row['purchase_status'], [0, 3], true));
		$row['S_CAN_COMPLETE_BUYER'] = ($context === 'buyer' && (int) $row['purchase_status'] === 1);
		$row['S_CAN_APPROVE'] = ($context === 'seller' && in_array((int) $row['purchase_status'], [0, 3], true));
		$row['S_CAN_REJECT'] = ($context === 'seller' && in_array((int) $row['purchase_status'], [0, 3], true));
		$row['S_CAN_COMPLETE_SELLER'] = ($context === 'seller' && (int) $row['purchase_status'] === 1);
		$row['S_CAN_RATE'] = ((int) $row['purchase_status'] === 5 && !$this->has_user_reviewed_purchase((int) $row['purchase_id'], (int) $this->user->data['user_id'], $context));
		$row['GIVEN_REVIEW'] = $this->get_purchase_review_by_user((int) $row['purchase_id'], (int) $this->user->data['user_id'], $context);
		$row['RECEIVED_REVIEW'] = $this->get_purchase_review_for_user((int) $row['purchase_id'], (int) $this->user->data['user_id'], $context);

		return $row;
	}


	private function get_user_favorites($user_id)
	{
		if (!$this->table_exists($this->table_favorites))
		{
			return [];
		}

		$sql = 'SELECT f.favorite_time, a.*, c.cat_name, u.username, u.user_colour
			FROM ' . $this->table_favorites . ' f
			INNER JOIN ' . $this->table_ads . ' a ON a.ad_id = f.ad_id
			LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.user_id
			WHERE f.user_id = ' . (int) $user_id . '
			ORDER BY f.favorite_time DESC';
		$result = $this->db->sql_query_limit($sql, 100);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$row['AD_PRICE_DISPLAY'] = $this->format_price($row);
			$row['MAIN_IMAGE'] = $this->get_main_image((int) $row['ad_id']);
			$row['FAVORITE_TIME_DISPLAY'] = !empty($row['favorite_time']) ? $this->user->format_date((int) $row['favorite_time']) : '';
			$row['SELLER_PROFILE'] = \append_sid("{$this->root_path}memberlist.{$this->php_ext}", ['mode' => 'viewprofile', 'u' => (int) $row['user_id']]);
			$this->prepare_ad_for_display($row);
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	private function remove_favorite($ad_id, $user_id)
	{
		if (!$this->table_exists($this->table_favorites))
		{
			return;
		}

		$sql = 'DELETE FROM ' . $this->table_favorites . '
			WHERE ad_id = ' . (int) $ad_id . '
				AND user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}

	private function get_user_payment_history($user_id)
	{
		if (!$this->has_payment_logs_table() || !$this->has_promotions_table())
		{
			return [];
		}

		$sql = 'SELECT l.*, p.promotion_type, p.promotion_status, p.payment_reference AS promotion_reference, a.ad_id, a.ad_title
			FROM ' . $this->table_payment_logs . ' l
			LEFT JOIN ' . $this->table_promotions . ' p ON p.promotion_id = l.promotion_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE p.user_id = ' . (int) $user_id . '
			ORDER BY l.payment_created DESC, l.payment_log_id DESC';
		$result = $this->db->sql_query_limit($sql, 100);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['U_VIEW'] = !empty($row['ad_id']) ? $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]) : '';
			$row['PAYMENT_CREATED_DISPLAY'] = !empty($row['payment_created']) ? $this->user->format_date((int) $row['payment_created']) : '';
			$row['PAYMENT_AMOUNT_DISPLAY'] = $this->format_purchase_price((int) $row['payment_amount_cents'], $row['payment_currency']);
			$row['PROMOTION_TYPE_LANG'] = $this->get_promotion_type_lang((string) $row['promotion_type']);
			$row['PROMOTION_STATUS_LANG'] = $this->get_promotion_status_lang((int) $row['promotion_status']);
			$row['PAYMENT_VALIDATION_STATUS_LANG'] = $this->get_payment_validation_status_lang((string) $row['payment_validation_status']);
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
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
		if (!$purchase)
		{
			\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_FOUND'));
		}

		switch ($action)
		{
			case 'approve_sale':
				if (!in_array((int) $purchase['purchase_status'], [0, 3], true))
				{
					\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_FOUND'));
				}
				if (isset($purchase['ad_quantity']) && (int) $purchase['ad_quantity'] <= 0)
				{
					\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_AVAILABLE'));
				}

				$this->apply_purchase_stock_change($purchase);
				$this->update_purchase_status((int) $purchase_id, 1);
				$this->add_notification((int) $purchase['buyer_user_id'], (int) $purchase['ad_id'], 'purchase_approved', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_APPROVED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_APPROVED_MESSAGE', $purchase['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_APPROVED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			break;

			case 'reject_sale':
				if (!in_array((int) $purchase['purchase_status'], [0, 3], true))
				{
					\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_FOUND'));
				}
				$this->update_purchase_status((int) $purchase_id, 2);
				$this->add_notification((int) $purchase['buyer_user_id'], (int) $purchase['ad_id'], 'purchase_rejected', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_REJECTED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_REJECTED_MESSAGE', $purchase['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_REJECTED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			break;

			case 'complete_sale':
				if ((int) $purchase['purchase_status'] !== 1)
				{
					\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_FOUND'));
				}
				$this->update_purchase_status((int) $purchase_id, 5);
				$this->add_notification((int) $purchase['buyer_user_id'], (int) $purchase['ad_id'], 'purchase_completed', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_COMPLETED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_COMPLETED_MESSAGE', $purchase['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_COMPLETED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			break;
		}
	}

	private function handle_buyer_purchase_action($action, $purchase_id, $buyer_user_id)
	{
		$purchase = $this->get_buyer_purchase($purchase_id, $buyer_user_id);
		if (!$purchase)
		{
			\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_FOUND'));
		}

		switch ($action)
		{
			case 'cancel_purchase':
				if (!in_array((int) $purchase['purchase_status'], [0, 3], true))
				{
					\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_FOUND'));
				}
				$this->update_purchase_status((int) $purchase_id, 4);
				$this->add_notification((int) $purchase['seller_user_id'], (int) $purchase['ad_id'], 'purchase_cancelled', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_CANCELLED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_CANCELLED_MESSAGE', $purchase['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_CANCELLED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			break;

			case 'complete_purchase':
				if ((int) $purchase['purchase_status'] !== 1)
				{
					\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_NOT_FOUND'));
				}
				$this->update_purchase_status((int) $purchase_id, 5);
				$this->add_notification((int) $purchase['seller_user_id'], (int) $purchase['ad_id'], 'purchase_completed', $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_COMPLETED_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_PURCHASE_COMPLETED_SELLER_MESSAGE', $purchase['ad_title']));
				\trigger_error($this->language->lang('MARKETPLACE_PURCHASE_COMPLETED') . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
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


	private function get_buyer_purchase($purchase_id, $buyer_user_id)
	{
		$sql = 'SELECT p.*, a.ad_title, a.ad_quantity
			FROM ' . $this->table_purchases . ' p
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = p.ad_id
			WHERE p.purchase_id = ' . (int) $purchase_id . '
				AND p.buyer_user_id = ' . (int) $buyer_user_id;
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
			4 => 'MARKETPLACE_PURCHASE_STATUS_CANCELLED',
			5 => 'MARKETPLACE_PURCHASE_STATUS_COMPLETED',
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


	private function has_messages_table()
	{
		return !empty($this->table_messages) && $this->table_exists($this->table_messages) && $this->table_exists($this->table_conversations);
	}

	private function table_exists($table)
	{
		$sql = "SHOW TABLES LIKE '" . $this->db->sql_escape($table) . "'";
		$result = $this->db->sql_query($sql);
		$exists = (bool) $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $exists;
	}

	private function get_user_conversations($user_id, $filter = 'all')
	{
		$user_id = (int) $user_id;
		$where = '(c.buyer_user_id = ' . $user_id . ' OR c.seller_user_id = ' . $user_id . ')';
		if ($filter === 'unread')
		{
			$where .= ' AND EXISTS (SELECT 1 FROM ' . $this->table_messages . ' mu WHERE mu.conversation_id = c.conversation_id AND mu.recipient_user_id = ' . $user_id . ' AND mu.message_read = 0)';
		}
		else if ($filter === 'blocked')
		{
			$where .= ' AND c.conversation_status <> 0';
		}

		$sql = 'SELECT c.*, a.ad_title, a.ad_status, bu.username AS buyer_username, bu.user_colour AS buyer_colour, su.username AS seller_username, su.user_colour AS seller_colour
			FROM ' . $this->table_conversations . ' c
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = c.ad_id
			LEFT JOIN ' . USERS_TABLE . ' bu ON bu.user_id = c.buyer_user_id
			LEFT JOIN ' . USERS_TABLE . ' su ON su.user_id = c.seller_user_id
			WHERE ' . $where . '
			ORDER BY c.last_message_time DESC, c.conversation_updated DESC';
		$result = $this->db->sql_query_limit($sql, 30);
		$conversations = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['IS_SELLER'] = ((int) $row['seller_user_id'] === $user_id);
			$row['OTHER_USER_ID'] = $row['IS_SELLER'] ? (int) $row['buyer_user_id'] : (int) $row['seller_user_id'];
			$row['OTHER_USERNAME'] = $row['IS_SELLER'] ? (string) $row['buyer_username'] : (string) $row['seller_username'];
			$row['OTHER_COLOUR'] = $row['IS_SELLER'] ? (string) $row['buyer_colour'] : (string) $row['seller_colour'];
			$row['ROLE_LANG'] = $this->language->lang($row['IS_SELLER'] ? 'MARKETPLACE_CONVERSATION_AS_SELLER' : 'MARKETPLACE_CONVERSATION_AS_BUYER');
			$row['U_AD'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$row['LAST_MESSAGE_DISPLAY'] = !empty($row['last_message_time']) ? $this->user->format_date((int) $row['last_message_time']) : '';
			$row['UNREAD_COUNT'] = $this->count_conversation_unread((int) $row['conversation_id'], $user_id);
			$row['S_BLOCKED'] = ((int) $row['conversation_status'] !== 0);
			$row['MESSAGES'] = $this->get_conversation_messages((int) $row['conversation_id'], $user_id);
			$conversations[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $conversations;
	}

	private function get_conversation_for_user($conversation_id, $user_id)
	{
		$sql = 'SELECT c.*, a.ad_title
			FROM ' . $this->table_conversations . ' c
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = c.ad_id
			WHERE c.conversation_id = ' . (int) $conversation_id . '
				AND (c.buyer_user_id = ' . (int) $user_id . ' OR c.seller_user_id = ' . (int) $user_id . ')';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $row;
	}

	private function get_conversation_messages($conversation_id, $user_id)
	{
		$sql = 'SELECT m.*, u.username, u.user_colour
			FROM ' . $this->table_messages . ' m
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = m.sender_user_id
			WHERE m.conversation_id = ' . (int) $conversation_id . '
			ORDER BY m.message_time ASC, m.message_id ASC';
		$result = $this->db->sql_query_limit($sql, 80);
		$messages = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['S_OWN_MESSAGE'] = ((int) $row['sender_user_id'] === (int) $user_id);
			$row['MESSAGE_TIME_DISPLAY'] = $this->user->format_date((int) $row['message_time']);
			$row['MESSAGE_TEXT_DISPLAY'] = nl2br(htmlspecialchars((string) $row['message_text'], ENT_COMPAT, 'UTF-8'));
			$messages[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $messages;
	}

	private function count_conversation_unread($conversation_id, $user_id)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_messages . '
			WHERE conversation_id = ' . (int) $conversation_id . '
				AND recipient_user_id = ' . (int) $user_id . '
				AND message_read = 0';
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		return $total;
	}

	private function mark_conversation_read($conversation_id, $user_id)
	{
		$conversation = $this->get_conversation_for_user($conversation_id, $user_id);
		if (!$conversation)
		{
			\trigger_error($this->language->lang('MARKETPLACE_CONVERSATION_NOT_FOUND'));
		}
		$this->db->sql_query('UPDATE ' . $this->table_messages . ' SET message_read = 1 WHERE conversation_id = ' . (int) $conversation_id . ' AND recipient_user_id = ' . (int) $user_id);
	}

	private function reply_marketplace_conversation($conversation_id, $user_id)
	{
		$conversation = $this->get_conversation_for_user($conversation_id, $user_id);
		if (!$conversation || (int) $conversation['conversation_status'] !== 0)
		{
			\trigger_error($this->language->lang('MARKETPLACE_CONVERSATION_NOT_FOUND'));
		}
		$message = trim($this->request->variable('message_text', '', true));
		if ($message === '')
		{
			\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_EMPTY'));
		}
		$this->check_message_limit($user_id);
		$message = truncate_string($message, 4000, 4000, false, '');
		$recipient_id = ((int) $conversation['buyer_user_id'] === (int) $user_id) ? (int) $conversation['seller_user_id'] : (int) $conversation['buyer_user_id'];
		$now = time();
		$this->db->sql_query('INSERT INTO ' . $this->table_messages . ' ' . $this->db->sql_build_array('INSERT', [
			'conversation_id' => (int) $conversation_id,
			'ad_id' => (int) $conversation['ad_id'],
			'sender_user_id' => (int) $user_id,
			'recipient_user_id' => $recipient_id,
			'message_text' => $message,
			'message_ip' => (string) $this->user->ip,
			'message_time' => $now,
			'message_read' => 0,
			'message_reported' => 0,
		]));
		$this->db->sql_query('UPDATE ' . $this->table_conversations . ' SET conversation_updated = ' . $now . ', last_message_time = ' . $now . ' WHERE conversation_id = ' . (int) $conversation_id);
		$this->add_notification($recipient_id, (int) $conversation['ad_id'], 'message_reply', $this->language->lang('MARKETPLACE_NOTIFICATION_MESSAGE_REPLY_TITLE'), $this->language->lang('MARKETPLACE_NOTIFICATION_MESSAGE_REPLY_MESSAGE', $conversation['ad_title']));
	}

	private function check_message_limit($user_id)
	{
		$limit = isset($this->config['marketplace_message_limit_per_hour']) ? (int) $this->config['marketplace_message_limit_per_hour'] : 10;
		$limit = max(1, $limit);
		$since = time() - 3600;
		$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_messages . ' WHERE sender_user_id = ' . (int) $user_id . ' AND message_time >= ' . $since;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		if ($total >= $limit)
		{
			\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_LIMIT_REACHED'));
		}
	}

	private function block_conversation_contact($conversation_id, $user_id)
	{
		$conversation = $this->get_conversation_for_user($conversation_id, $user_id);
		if (!$conversation)
		{
			\trigger_error($this->language->lang('MARKETPLACE_CONVERSATION_NOT_FOUND'));
		}
		$blocked_id = ((int) $conversation['buyer_user_id'] === (int) $user_id) ? (int) $conversation['seller_user_id'] : (int) $conversation['buyer_user_id'];
		$now = time();
		$this->db->sql_query('UPDATE ' . $this->table_conversations . ' SET conversation_status = 1, conversation_updated = ' . $now . ' WHERE conversation_id = ' . (int) $conversation_id);
		$sql = 'SELECT block_id FROM ' . $this->table_message_blocks . ' WHERE blocker_user_id = ' . (int) $user_id . ' AND blocked_user_id = ' . $blocked_id . ' AND ad_id = ' . (int) $conversation['ad_id'];
		$result = $this->db->sql_query_limit($sql, 1);
		$exists = (int) $this->db->sql_fetchfield('block_id');
		$this->db->sql_freeresult($result);
		if (!$exists)
		{
			$this->db->sql_query('INSERT INTO ' . $this->table_message_blocks . ' ' . $this->db->sql_build_array('INSERT', [
				'blocker_user_id' => (int) $user_id,
				'blocked_user_id' => $blocked_id,
				'ad_id' => (int) $conversation['ad_id'],
				'block_reason' => '',
				'block_time' => $now,
			]));
		}
	}

	private function report_marketplace_message($message_id, $user_id)
	{
		$sql = 'SELECT m.*, c.seller_user_id, c.buyer_user_id, a.ad_title
			FROM ' . $this->table_messages . ' m
			LEFT JOIN ' . $this->table_conversations . ' c ON c.conversation_id = m.conversation_id
			LEFT JOIN ' . $this->table_ads . ' a ON a.ad_id = m.ad_id
			WHERE m.message_id = ' . (int) $message_id . '
				AND (m.sender_user_id = ' . (int) $user_id . ' OR m.recipient_user_id = ' . (int) $user_id . ')';
		$result = $this->db->sql_query_limit($sql, 1);
		$message = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$message || (int) $message['sender_user_id'] === (int) $user_id)
		{
			\trigger_error($this->language->lang('MARKETPLACE_MESSAGE_NOT_FOUND'));
		}
		$this->db->sql_query('UPDATE ' . $this->table_messages . ' SET message_reported = 1 WHERE message_id = ' . (int) $message_id);
		$reason = $this->request->variable('report_reason', '', true);
		if ($reason === '')
		{
			$reason = $this->language->lang('MARKETPLACE_MESSAGE_REPORTED_DEFAULT_REASON');
		}
		$report_table = preg_replace('/marketplace_ads$/', 'marketplace_reports', $this->table_ads);
		$this->db->sql_query('INSERT INTO ' . $report_table . ' ' . $this->db->sql_build_array('INSERT', [
			'ad_id' => (int) $message['ad_id'],
			'reporter_id' => (int) $user_id,
			'report_reason' => $reason,
			'report_type' => 'message',
			'target_user_id' => (int) $message['sender_user_id'],
			'review_id' => (int) $message_id,
			'report_status' => 0,
			'report_created' => time(),
			'report_closed' => 0,
			'report_closed_by' => 0,
			'report_note' => 'Marketplace message #' . (int) $message_id,
		]));
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
