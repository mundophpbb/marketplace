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
	 * Purge extension data.
	 *
	 * @return void
	 */
	public function purge_step($old_state)
	{
		return parent::purge_step($old_state);
	}
}
