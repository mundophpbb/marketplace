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
	'UCP_MARKETPLACE_OVERVIEW'=> 'My classified ads',
]);
