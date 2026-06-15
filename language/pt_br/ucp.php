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
	'UCP_MARKETPLACE_OVERVIEW' => 'Resumo do Marketplace',
	'UCP_MARKETPLACE_ADS' => 'Meus anúncios',
	'UCP_MARKETPLACE_PROMOTIONS' => 'Minhas promoções',
	'UCP_MARKETPLACE_FAVORITES' => 'Favoritos',
	'UCP_MARKETPLACE_PAYMENTS' => 'Histórico de pagamentos',
	'UCP_MARKETPLACE_NOTIFICATIONS' => 'Notificações do Marketplace',

	// Marketplace sales/orders v1.4.19
	'UCP_MARKETPLACE_PURCHASES' => 'Histórico de compras',
	'UCP_MARKETPLACE_SALES' => 'Histórico de vendas',
	'UCP_MARKETPLACE_CONVERSATIONS' => 'Conversas do Marketplace',
]);
