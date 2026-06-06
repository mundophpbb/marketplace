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
		if ($this->config['marketplace_enabled'])
		{
			$unread_notifications = 0;
			$has_notifications_table = isset($this->config['marketplace_version']) && version_compare((string) $this->config['marketplace_version'], '1.3.0', '>=');
			if ($has_notifications_table && (int) $this->user->data['user_id'] !== ANONYMOUS && $this->auth->acl_get('u_marketplace_view'))
			{
				$sql = 'SELECT COUNT(*) AS total
					FROM ' . $this->table_notifications . '
					WHERE user_id = ' . (int) $this->user->data['user_id'] . '
						AND notification_read = 0';
				$result = $this->db->sql_query($sql);
				$unread_notifications = (int) $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);
			}

			$this->template->assign_vars([
				'U_MARKETPLACE' => $this->helper->route('mundophpbb_marketplace_index'),
				'S_MARKETPLACE_ENABLED' => true,
				'MARKETPLACE_UNREAD_NOTIFICATIONS' => $unread_notifications,
			]);
		}
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
