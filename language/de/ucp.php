<?php
/**
 *
 * Marketplace UCP language (German)
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
	'UCP_MARKETPLACE_OVERVIEW' => 'Marketplace-Uebersicht',
	'UCP_MARKETPLACE_ADS' => 'Meine Anzeigen',
	'UCP_MARKETPLACE_PROMOTIONS' => 'Meine Promotions',
	'UCP_MARKETPLACE_FAVORITES' => 'Favoriten',
	'UCP_MARKETPLACE_PAYMENTS' => 'Zahlungsverlauf',
	'UCP_MARKETPLACE_NOTIFICATIONS' => 'Marketplace-Benachrichtigungen',

	// Marketplace sales/orders v1.4.19
	'UCP_MARKETPLACE_PURCHASES' => 'Kaufverlauf',
	'UCP_MARKETPLACE_SALES' => 'Verkaufsverlauf',
	'UCP_MARKETPLACE_CONVERSATIONS' => 'Marketplace-Unterhaltungen',
]);
