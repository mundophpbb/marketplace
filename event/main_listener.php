<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Marketplace Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var string */
	protected $table_notifications;

	/** @var string */
	protected $table_ads;

	/** @var string */
	protected $table_cats;

	/** @var string */
	protected $table_images;

	/** @var string */
	protected $table_conversations;

	/** @var string */
	protected $table_messages;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\language\language $language,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\auth\auth $auth,
		$table_notifications,
		$table_ads,
		$table_cats,
		$table_images,
		$root_path,
		$php_ext
	)
	{
		$this->language = $language;
		$this->helper   = $helper;
		$this->template = $template;
		$this->user     = $user;
		$this->config   = $config;
		$this->db       = $db;
		$this->auth     = $auth;
		$this->table_notifications = $table_notifications;
		$this->table_ads = $table_ads;
		$this->table_cats = $table_cats;
		$this->table_images = $table_images;
		$this->table_conversations = preg_replace('/marketplace_ads$/', 'marketplace_conversations', $table_ads);
		$this->table_messages = preg_replace('/marketplace_ads$/', 'marketplace_messages', $table_ads);
		$this->root_path = $root_path;
		$this->php_ext  = $php_ext;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'                  => 'load_language_on_setup',
			'core.page_header'                 => 'add_page_header_link',
			'core.viewonline_overwrite_location' => 'viewonline_page',
			'core.permissions'                 => 'add_permissions',
		];
	}

	/**
	 * Load language files
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];

		foreach (['common', 'acp', 'ucp'] as $lang_set)
		{
			$lang_set_ext[] = [
				'ext_name' => 'mundophpbb/marketplace',
				'lang_set' => $lang_set,
			];
		}

		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Add Marketplace link to navbar
	 */
	public function add_page_header_link()
	{
		// Ensure extension language is available for event templates rendered on the forum index/header.
		$this->language->add_lang('common', 'mundophpbb/marketplace');

		if (!$this->config['marketplace_enabled'])
		{
			return;
		}

		$unread_notifications = 0;
		$unread_conversations = 0;
		$u_notifications = '';
		$u_conversations = '';
		$can_view = $this->auth->acl_get('u_marketplace_view');
		$user_id = (int) $this->user->data['user_id'];

		if ($user_id !== ANONYMOUS && $can_view)
		{
			$u_notifications = \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'notifications']);
			$u_conversations = \append_sid("{$this->root_path}ucp.{$this->php_ext}", ['i' => '\\mundophpbb\\marketplace\\ucp\\main_module', 'mode' => 'conversations']);

			if ($this->table_exists($this->table_notifications))
			{
				$sql = 'SELECT COUNT(*) AS total
					FROM ' . $this->table_notifications . '
					WHERE user_id = ' . $user_id . '
						AND notification_read = 0';
				$result = $this->db->sql_query($sql);
				$unread_notifications = (int) $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);
			}

			if ($this->table_exists($this->table_messages))
			{
				$sql = 'SELECT COUNT(*) AS total
					FROM ' . $this->table_messages . '
					WHERE recipient_user_id = ' . $user_id . '
						AND message_read = 0';
				$result = $this->db->sql_query($sql);
				$unread_conversations = (int) $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);
			}
		}

		$this->template->assign_vars([
			'U_MARKETPLACE' => $this->helper->route('mundophpbb_marketplace_index'),
			'U_MARKETPLACE_NOTIFICATIONS' => $u_notifications,
			'U_MARKETPLACE_CONVERSATIONS' => $u_conversations,
			'S_MARKETPLACE_ENABLED' => true,
			'MARKETPLACE_UNREAD_NOTIFICATIONS' => $unread_notifications,
			'MARKETPLACE_UNREAD_CONVERSATIONS' => $unread_conversations,
			'MARKETPLACE_TOTAL_ALERTS' => $unread_notifications + $unread_conversations,
			'MP_LATEST_ADS_TITLE' => $this->language->lang('MARKETPLACE_LATEST_ADS'),
			'MP_VIEW_ALL_ADS_TEXT' => $this->language->lang('MARKETPLACE_VIEW_ALL_ADS'),
			'MP_ALL_CATEGORIES_TEXT' => $this->language->lang('MARKETPLACE_ALL_CATEGORIES'),
			'MP_NO_IMAGE_TEXT' => $this->language->lang('MARKETPLACE_NO_IMAGE'),
			'MP_INDEX_CATEGORIES' => $can_view ? $this->get_categories_for_index_filter() : [],
			'MP_LATEST_ADS' => $can_view ? $this->get_latest_ads_for_index() : [],
		]);
	}

	private function get_categories_for_index_filter()
	{
		if (!$this->table_exists($this->table_cats))
		{
			return [];
		}

		$sql = 'SELECT cat_id, cat_name FROM ' . $this->table_cats . ' WHERE cat_enabled = 1 ORDER BY cat_order, cat_id';
		$result = $this->db->sql_query($sql);
		$categories = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$categories[] = [
				'CAT_ID' => (int) $row['cat_id'],
				'CAT_NAME' => $this->translate_category_text(isset($row['cat_name']) ? $row['cat_name'] : ''),
				'U_CATEGORY' => $this->helper->route('mundophpbb_marketplace_category', ['cat_id' => (int) $row['cat_id']]),
			];
		}

		$this->db->sql_freeresult($result);

		return $categories;
	}

	private function get_latest_ads_for_index()
	{
		if (!$this->table_exists($this->table_ads))
		{
			return [];
		}

		$sql = 'SELECT a.ad_id, a.ad_title, a.ad_price, a.ad_price_type, a.ad_price_cents, a.ad_currency, a.ad_city, a.ad_region, a.ad_created, c.cat_name
			FROM ' . $this->table_ads . ' a
			LEFT JOIN ' . $this->table_cats . ' c ON c.cat_id = a.cat_id
			WHERE a.ad_status = 1
			ORDER BY a.ad_created DESC';
		$result = $this->db->sql_query_limit($sql, 5);
		$ads = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['cat_name'] = $this->translate_category_text(isset($row['cat_name']) ? $row['cat_name'] : '');
			$row['U_VIEW'] = $this->helper->route('mundophpbb_marketplace_view', ['ad_id' => (int) $row['ad_id']]);
			$row['MAIN_IMAGE'] = $this->get_main_image((int) $row['ad_id']);
			$row['AD_PRICE_DISPLAY'] = $this->format_ad_price($row);
			$row['LOCATION_SHORT'] = trim((string) $row['ad_city'] . (((string) $row['ad_city'] !== '' && (string) $row['ad_region'] !== '') ? ' / ' : '') . (string) $row['ad_region']);
			$row['AD_CREATED_DISPLAY'] = !empty($row['ad_created']) ? $this->user->format_date((int) $row['ad_created']) : '';
			$ads[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $ads;
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

	private function get_main_image($ad_id)
	{
		if (!$this->table_exists($this->table_images))
		{
			return '';
		}

		$sql = 'SELECT image_id FROM ' . $this->table_images . ' WHERE ad_id = ' . (int) $ad_id . ' ORDER BY image_is_main DESC, image_order ASC';
		$result = $this->db->sql_query_limit($sql, 1);
		$image_id = (int) $this->db->sql_fetchfield('image_id');
		$this->db->sql_freeresult($result);

		return $image_id ? $this->helper->route('mundophpbb_marketplace_image', ['image_id' => $image_id]) : '';
	}

	private function format_ad_price($ad)
	{
		$price_type = isset($ad['ad_price_type']) ? (int) $ad['ad_price_type'] : 0;
		if ($price_type === 3)
		{
			return $this->language->lang('MARKETPLACE_PRICE_FREE');
		}
		if ($price_type === 4)
		{
			return $this->language->lang('MARKETPLACE_PRICE_ON_REQUEST');
		}

		$currency = !empty($ad['ad_currency']) ? (string) $ad['ad_currency'] : (isset($this->config['marketplace_currency_default']) ? (string) $this->config['marketplace_currency_default'] : 'R$');
		if (isset($ad['ad_price_cents']) && (int) $ad['ad_price_cents'] > 0)
		{
			return trim($currency . ' ' . number_format(((int) $ad['ad_price_cents']) / 100, 2, ',', '.'));
		}
		if (!empty($ad['ad_price']) && (string) $ad['ad_price'] !== '0')
		{
			return trim($currency . ' ' . (string) $ad['ad_price']);
		}

		return '';
	}

	private function table_exists($table)
	{
		if ($table === '')
		{
			return false;
		}

		$sql = "SHOW TABLES LIKE '" . $this->db->sql_escape($table) . "'";
		$result = $this->db->sql_query($sql);
		$exists = (bool) $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $exists;
	}


	/**
	 * Show who is viewing Marketplace on viewonline page
	 */
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] === 'app' && strpos($event['row']['session_page'], 'app.' . $this->php_ext . '/marketplace') === 0)
		{
			$event['location'] = $this->language->lang('VIEWING_MARKETPLACE');
			$event['location_url'] = $this->helper->route('mundophpbb_marketplace_index');
		}
	}

	/**
	 * Add custom permissions
	 */
	public function add_permissions($event)
	{
		$permissions = $event['permissions'];

		$permissions['u_marketplace_view']       = ['lang' => 'ACL_U_MARKETPLACE_VIEW', 'cat' => 'misc'];
		$permissions['u_marketplace_post']       = ['lang' => 'ACL_U_MARKETPLACE_POST', 'cat' => 'misc'];
		$permissions['u_marketplace_edit_own']   = ['lang' => 'ACL_U_MARKETPLACE_EDIT_OWN', 'cat' => 'misc'];
		$permissions['u_marketplace_delete_own'] = ['lang' => 'ACL_U_MARKETPLACE_DELETE_OWN', 'cat' => 'misc'];
		$permissions['u_marketplace_report']     = ['lang' => 'ACL_U_MARKETPLACE_REPORT', 'cat' => 'misc'];
		$permissions['u_marketplace_bump_own']   = ['lang' => 'ACL_U_MARKETPLACE_BUMP_OWN', 'cat' => 'misc'];

		$permissions['m_marketplace_approve']    = ['lang' => 'ACL_M_MARKETPLACE_APPROVE', 'cat' => 'misc'];
		$permissions['m_marketplace_edit']       = ['lang' => 'ACL_M_MARKETPLACE_EDIT', 'cat' => 'misc'];
		$permissions['m_marketplace_delete']     = ['lang' => 'ACL_M_MARKETPLACE_DELETE', 'cat' => 'misc'];
		$permissions['m_marketplace_feature']    = ['lang' => 'ACL_M_MARKETPLACE_FEATURE', 'cat' => 'misc'];
		$permissions['m_marketplace_reports']    = ['lang' => 'ACL_M_MARKETPLACE_REPORTS', 'cat' => 'misc'];

		$event['permissions'] = $permissions;
	}
}
