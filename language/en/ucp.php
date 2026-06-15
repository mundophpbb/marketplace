<?php
/**
 *
 * Marketplace UCP language (English)
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'UCP_MARKETPLACE_TITLE'   => 'Marketplace',
	'UCP_MARKETPLACE_OVERVIEW' => 'Marketplace summary',
	'UCP_MARKETPLACE_ADS' => 'My ads',
	'UCP_MARKETPLACE_PROMOTIONS' => 'My promotions',
	'UCP_MARKETPLACE_FAVORITES' => 'Favorites',
	'UCP_MARKETPLACE_PAYMENTS' => 'Payment history',
	'UCP_MARKETPLACE_NOTIFICATIONS' => 'Marketplace notifications',

	// Marketplace sales/orders v1.4.19
	'UCP_MARKETPLACE_PURCHASES' => 'Purchase history',
	'UCP_MARKETPLACE_SALES' => 'Sales history',
	'UCP_MARKETPLACE_CONVERSATIONS' => 'Marketplace conversations',
]);
