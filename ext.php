<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 * Permite que usuários do fórum postem anúncios classificados (venda/compra/serviços).
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace;

/**
 * Marketplace Extension base
 */
class ext extends \phpbb\extension\base
{
	/**
	 * Check whether the extension can be enabled.
	 * Requires phpBB 3.3.0 or newer.
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		return \phpbb_version_compare(PHPBB_VERSION, '3.3.0', '>=');
	}

	/**
	 * Enable extension specific actions.
	 *
	 * @return void
	 */
	public function enable_step($old_state)
	{
		switch ($old_state)
		{
				case '':
				// Fresh enable after purge/failed purge may leave orphan module rows behind.
				// Clean them only when the extension config is absent, so normal re-enable after disable is not affected.
				$this->remove_marketplace_modules_before_fresh_install();

				// Enable the first migration
				return 'migrations';

			case 'migrations':
				// Nothing special
				return parent::enable_step($old_state);

			default:
				return parent::enable_step($old_state);
		}
	}

	/**
	 * Disable the extension.
	 *
	 * @return void
	 */
	public function disable_step($old_state)
	{
		return parent::disable_step($old_state);
	}

	/**
	 * Remove stale Marketplace module rows before a fresh installation.
	 *
	 * This prevents activation failures after an interrupted purge/rollback where
	 * phpBB kept a child module such as MARKETPLACE_ACP_NOTIFICATIONS in the
	 * modules table while extension config was already removed.
	 *
	 * @return void
	 */
	private function remove_marketplace_modules_before_fresh_install()
	{
		if (!defined('MODULES_TABLE') || !$this->container)
		{
			return;
		}

		try
		{
			$config = $this->container->get('config');
			if (isset($config['marketplace_enabled']) || isset($config['marketplace_version']))
			{
				return;
			}
		}
		catch (\Exception $e)
		{
			// If config is not available, avoid interfering with enable.
			return;
		}

		$this->remove_marketplace_modules_for_purge();
	}

	/**
	 * Purge extension data.
	 *
	 * The phpBB migration module tool can try to remove a parent ACP/UCP module
	 * before removing all child modes when old module.add instructions live in the
	 * same migration. On purge this may throw CANNOT_REMOVE_MODULE. Remove this
	 * extension's ACP/UCP modules in a safe child-first pass before the standard
	 * migration revert runs.
	 *
	 * @return void
	 */
	public function purge_step($old_state)
	{
		$this->remove_marketplace_modules_for_purge();

		return parent::purge_step($old_state);
	}

	/**
	 * Remove Marketplace ACP/UCP modules safely before migration rollback.
	 *
	 * @return void
	 */
	private function remove_marketplace_modules_for_purge()
	{
		if (!defined('MODULES_TABLE') || !$this->container)
		{
			return;
		}

		try
		{
			$db = $this->container->get('dbal.conn');
			$cache = $this->container->get('cache.driver');
			$module_manager = $this->container->get('module.manager');

			$module_basenames = [
				'\\mundophpbb\\marketplace\\acp\\main_module',
				'\\mundophpbb\\marketplace\\ucp\\main_module',
				'mundophpbb\\marketplace\\acp\\main_module',
				'mundophpbb\\marketplace\\ucp\\main_module',
			];

			$module_langnames = [
				'MARKETPLACE_TITLE',
				'MARKETPLACE_ACP_DASHBOARD',
				'MARKETPLACE_ACP_SETTINGS',
				'MARKETPLACE_ACP_CATEGORIES',
				'MARKETPLACE_ACP_ADS',
				'MARKETPLACE_ACP_NOTIFICATIONS',
				'MARKETPLACE_ACP_PACKAGES',
				'MARKETPLACE_ACP_REPORTS',
				'UCP_MARKETPLACE_TITLE',
				'UCP_MARKETPLACE_OVERVIEW',
				'UCP_MARKETPLACE_NOTIFICATIONS',
			];

			// A few passes make the cleanup tolerant of duplicate or partially removed modules.
			for ($i = 0; $i < 5; $i++)
			{
				$sql = 'SELECT module_id, module_class, left_id, right_id
					FROM ' . MODULES_TABLE . '
					WHERE ' . $db->sql_in_set('module_class', ['acp', 'ucp']) . '
						AND (' . $db->sql_in_set('module_basename', $module_basenames) . '
							OR ' . $db->sql_in_set('module_langname', $module_langnames) . ')
					ORDER BY (right_id - left_id) ASC, right_id DESC';
				$result = $db->sql_query($sql);

				$modules = [];
				while ($row = $db->sql_fetchrow($result))
				{
					$modules[] = $row;
				}
				$db->sql_freeresult($result);

				if (empty($modules))
				{
					break;
				}

				$removed_any = false;
				foreach ($modules as $module)
				{
					try
					{
						$module_manager->delete_module((int) $module['module_id'], $module['module_class']);
						$removed_any = true;
					}
					catch (\Exception $e)
					{
						// Parent may still have a child. It will be retried in the next pass.
					}
				}

				if (!$removed_any)
				{
					break;
				}
			}

			// Last-resort cleanup for duplicated/stale module rows left by interrupted purge attempts.
			$sql = 'DELETE FROM ' . MODULES_TABLE . '
				WHERE ' . $db->sql_in_set('module_class', ['acp', 'ucp']) . '
					AND (' . $db->sql_in_set('module_basename', $module_basenames) . '
						OR ' . $db->sql_in_set('module_langname', $module_langnames) . ')';
			$db->sql_query($sql);

			$cache->destroy('_modules_acp');
			$cache->destroy('_modules_ucp');
		}
		catch (\Exception $e)
		{
			// Never block purge because of pre-cleanup. Let phpBB continue its normal process.
		}
	}
}
