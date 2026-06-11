<?php
/**
 *
 * Marketplace UCP language (Português Brasil)
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
	'UCP_MARKETPLACE_TITLE'   => 'Classificados',
	'UCP_MARKETPLACE_OVERVIEW'=> 'Meus anúncios classificados',
	'UCP_MARKETPLACE_NOTIFICATIONS' => 'Notificações do Marketplace',
]);
