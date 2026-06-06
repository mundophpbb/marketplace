<?php
/**
 *
 * Marketplace / Classificados Extension - ACP language (Português Brasil)
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
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
	'MARKETPLACE_TITLE'                 => 'Marketplace / Classificados',
	'MARKETPLACE_ACP_SETTINGS'          => 'Configurações',
	'MARKETPLACE_ACP_CATEGORIES'        => 'Categorias',
	'MARKETPLACE_ACP_CATEGORIES_EXPLAIN' => 'Gerencie as categorias exibidas no marketplace, suas regras de publicação e opções permitidas.',

	'MARKETPLACE_ACP_ADS'               => 'Gerenciar Anúncios',

	// Settings
	'MARKETPLACE_SETTINGS'              => 'Configurações do Marketplace',
	'MARKETPLACE_ENABLE'                => 'Ativar Marketplace',
	'MARKETPLACE_REQUIRE_APPROVAL'      => 'Exigir aprovação para novos anúncios',
	'MARKETPLACE_REQUIRE_APPROVAL_EXPLAIN' => 'Novos anúncios ficarão com status "Aguardando aprovação" até serem aprovados por um moderador.',
	'MARKETPLACE_MAX_ADS_PER_USER'      => 'Máximo de anúncios ativos por usuário',
	'MARKETPLACE_EXPIRATION_DAYS'       => 'Validade do anúncio (dias)',
	'MARKETPLACE_MAX_IMAGES'            => 'Máximo de imagens por anúncio',
	'MARKETPLACE_ITEMS_PER_PAGE'        => 'Anúncios por página (público)',
	'MARKETPLACE_ALLOW_IMAGES'          => 'Permitir upload de imagens',
	'MARKETPLACE_ENABLE_PRICE'          => 'Habilitar campo de preço',
	'MARKETPLACE_CURRENCY_DEFAULT'      => 'Símbolo de moeda padrão',

	'MARKETPLACE_SHOW_SOLD_ADS' => 'Mostrar anúncios vendidos na listagem pública',
	'MARKETPLACE_SHOW_SOLD_ADS_EXPLAIN' => 'Quando ativo, anúncios marcados como vendidos continuam visíveis com o selo “Vendido”. O contato permanece desativado.',
	'MARKETPLACE_SOLD_VISIBLE_DAYS' => 'Manter vendidos visíveis por quantos dias',
	'MARKETPLACE_SOLD_VISIBLE_DAYS_EXPLAIN' => 'Use 0 para manter anúncios vendidos visíveis sem limite de tempo.',

	'MARKETPLACE_SETTINGS_SAVED'        => 'Configurações salvas com sucesso.',
	'LOG_MARKETPLACE_SETTINGS'          => '<strong>Alterou configurações do Marketplace</strong>',

	// Categories
	'MARKETPLACE_CATEGORIES'            => 'Gerenciamento de categorias',
	'MARKETPLACE_ADD_CATEGORY'          => 'Adicionar nova categoria',
	'MARKETPLACE_EDIT_CATEGORY'         => 'Editar categoria',
	'MARKETPLACE_CAT_NAME'              => 'Nome da categoria',
	'MARKETPLACE_CAT_DESC'              => 'Descrição',
	'MARKETPLACE_CAT_ORDER'             => 'Ordem de exibição',
	'MARKETPLACE_CAT_ENABLED'           => 'Ativada',
	'MARKETPLACE_CAT_ADS_COUNT'         => 'Anúncios',
	'MARKETPLACE_CAT_NAME_REQUIRED'     => 'O nome da categoria é obrigatório.',
	'MARKETPLACE_CAT_ADDED'             => 'Categoria adicionada com sucesso.',
	'MARKETPLACE_CAT_UPDATED'           => 'Categoria atualizada com sucesso.',
	'MARKETPLACE_CAT_DELETED'           => 'Categoria excluída com sucesso.',
	'CONFIRM_DELETE_CAT'                => 'Excluir esta categoria? Os anúncios dela ficarão sem categoria.',
	'LOG_MARKETPLACE_CAT_ADDED'         => '<strong>Adicionou categoria no Marketplace</strong>',
	'LOG_MARKETPLACE_CAT_EDITED'        => '<strong>Editou categoria do Marketplace</strong> ID: %s',
	'LOG_MARKETPLACE_CAT_DELETED'       => '<strong>Excluiu categoria do Marketplace</strong>',

	// Manage ads (ACP)
	'MARKETPLACE_ACP_ADS_EXPLAIN' => 'Gerencie anúncios publicados, pendentes, ocultos, vendidos e denunciados. As ações foram agrupadas para facilitar a moderação.',
	'MARKETPLACE_ACP_ADS_TOTAL' => 'Total encontrado: %d anúncio(s).',
	'MARKETPLACE_ACP_AD_INFO' => 'Anúncio',
	'MARKETPLACE_ACP_QUICK_ACTIONS' => 'Ações rápidas',
	'MARKETPLACE_ACP_FEATURE_ACTIONS' => 'Destaque',
	'MARKETPLACE_ACP_MODERATION_ACTIONS' => 'Moderação',
	'MARKETPLACE_ACP_VIEW_PUBLIC' => 'Ver público',
	'MARKETPLACE_ADS'                   => 'Todos os anúncios',
	'MARKETPLACE_FILTER_STATUS'         => 'Filtrar por status',
	'MARKETPLACE_APPROVE'               => 'Aprovar',
	'MARKETPLACE_REJECT'                => 'Rejeitar / Ocultar',
	'MARKETPLACE_DELETE_AD'             => 'Excluir anúncio',
	'MARKETPLACE_MARK_SOLD'             => 'Marcar como vendido',
	'MARKETPLACE_AD_APPROVED'           => 'Anúncio aprovado com sucesso.',
	'MARKETPLACE_AD_REJECTED'           => 'Anúncio rejeitado/oculto.',
	'MARKETPLACE_AD_DELETED'            => 'Anúncio excluído permanentemente.',
	'MARKETPLACE_AD_MARKED_SOLD'        => 'Anúncio marcado como vendido.',
	'CONFIRM_DELETE_AD'                 => 'Tem certeza de que deseja excluir permanentemente este anúncio?',
	'LOG_MARKETPLACE_AD_APPROVED'       => '<strong>Aprovou anúncio do Marketplace</strong> ID: %s',
	'LOG_MARKETPLACE_AD_REJECTED'       => '<strong>Rejeitou anúncio do Marketplace</strong> ID: %s',
	'LOG_MARKETPLACE_AD_DELETED'        => '<strong>Excluiu anúncio do Marketplace</strong> ID: %s',

	// Pacote 3 - painel, regras por categoria e denúncias
	'MARKETPLACE_ACP_DASHBOARD' => 'Painel',
	'MARKETPLACE_ACP_REPORTS' => 'Denúncias',
	'MARKETPLACE_ADVANCED_FEATURES' => 'Recursos avançados',
	'MARKETPLACE_ALLOW_REPORTS' => 'Permitir denúncias de anúncios',
	'MARKETPLACE_ALLOW_REPORTS_EXPLAIN' => 'Permite que usuários autenticados denunciem anúncios públicos.',
	'MARKETPLACE_ALLOW_BUMP' => 'Permitir subir anúncios',
	'MARKETPLACE_ALLOW_BUMP_EXPLAIN' => 'Permite que autores subam anúncios ativos respeitando o intervalo configurado.',
	'MARKETPLACE_BUMP_INTERVAL_DAYS' => 'Intervalo para subir anúncio',
	'MARKETPLACE_BUMP_INTERVAL_DAYS_EXPLAIN' => 'Número mínimo de dias entre duas subidas feitas pelo autor. Moderadores podem subir a qualquer momento.',
	'MARKETPLACE_FEATURED_DAYS' => 'Duração padrão do destaque',
	'MARKETPLACE_FEATURED_DAYS_EXPLAIN' => 'Quantidade padrão de dias aplicada ao destacar um anúncio.',
	'MARKETPLACE_CAT_RULES' => 'Regras da categoria',
	'MARKETPLACE_CAT_EXPIRATION_DAYS' => 'Validade própria da categoria',
	'MARKETPLACE_CAT_EXPIRATION_DAYS_EXPLAIN' => 'Use 0 para aplicar a validade global configurada no Marketplace.',
	'MARKETPLACE_CAT_REQUIRE_PRICE' => 'Exigir preço',
	'MARKETPLACE_CAT_REQUIRE_LOCATION' => 'Exigir localização',
	'MARKETPLACE_CAT_REQUIRE_PHONE' => 'Exigir telefone',
	'MARKETPLACE_CAT_ALLOW_IMAGES' => 'Permitir imagens',
	'MARKETPLACE_GLOBAL_DEFAULT' => 'Padrão global',
	'MARKETPLACE_FEATURED' => 'Destaque',
	'MARKETPLACE_FEATURE_AD' => 'Destacar',
	'MARKETPLACE_UNFEATURE_AD' => 'Remover destaque',
	'MARKETPLACE_AD_FEATURED' => 'Anúncio destacado com sucesso.',
	'MARKETPLACE_AD_UNFEATURED' => 'Destaque removido com sucesso.',
	'MARKETPLACE_BUMP_AD' => 'Subir',
	'MARKETPLACE_AD_BUMPED' => 'Anúncio subido com sucesso.',
	'MARKETPLACE_LAST_BUMPED' => 'Subido em',
	'MARKETPLACE_HIDDEN_REASON' => 'Motivo',
	'MARKETPLACE_REPORTS' => 'Denúncias',
	'MARKETPLACE_REPORT' => 'Denúncia',
	'MARKETPLACE_REPORTER' => 'Denunciante',
	'MARKETPLACE_REPORT_REASON' => 'Motivo',
	'MARKETPLACE_REPORT_NOTE' => 'Nota da moderação',
	'MARKETPLACE_REPORT_OPEN' => 'Aberta',
	'MARKETPLACE_REPORT_CLOSED' => 'Fechada',
	'MARKETPLACE_REPORT_RESOLVE' => 'Resolver',
	'MARKETPLACE_REPORT_REOPEN' => 'Reabrir',
	'MARKETPLACE_REPORT_RESOLVED' => 'Denúncia resolvida.',
	'MARKETPLACE_REPORT_REOPENED' => 'Denúncia reaberta.',
	'MARKETPLACE_REPORT_DELETED' => 'Denúncia excluída.',
	'MARKETPLACE_REPORT_NOT_FOUND' => 'Denúncia não encontrada.',
	'MARKETPLACE_CREATED' => 'Criado em',
	'MARKETPLACE_RECENT_REPORTS' => 'Denúncias recentes',
	'MARKETPLACE_STATS_TOTAL_ADS' => 'Total de anúncios',
	'MARKETPLACE_STATS_ACTIVE_ADS' => 'Ativos',
	'MARKETPLACE_STATS_PENDING_ADS' => 'Pendentes',
	'MARKETPLACE_STATS_SOLD_ADS' => 'Vendidos',
	'MARKETPLACE_STATS_EXPIRED_ADS' => 'Expirados',
	'MARKETPLACE_STATS_HIDDEN_ADS' => 'Ocultos',
	'MARKETPLACE_STATS_FEATURED_ADS' => 'Destacados',
	'MARKETPLACE_STATS_OPEN_REPORTS' => 'Denúncias abertas',
	'MARKETPLACE_STATS_TOTAL_REPORTS' => 'Total de denúncias',
	'MARKETPLACE_STATS_TOTAL_IMAGES' => 'Imagens',
	'MARKETPLACE_STATS_DISK_USAGE' => 'Uso em disco',

	// Pacote 3 - moderação avançada, denúncias, destaque e notificações
	'MARKETPLACE_ACP_STATS' => 'Estatísticas',
	'MARKETPLACE_FEATURED_DAYS_DEFAULT' => 'Duração padrão do destaque',
	'MARKETPLACE_FEATURED_DAYS_DEFAULT_EXPLAIN' => 'Número padrão de dias para manter um anúncio destacado.',
	'MARKETPLACE_CAT_ALLOWED_TYPES' => 'Tipos permitidos',
	'MARKETPLACE_CAT_ALLOWED_TYPES_EXPLAIN' => 'Selecione os tipos de anúncio aceitos nesta categoria. Se nenhum for selecionado, todos serão usados.',
	'MARKETPLACE_CAT_ALLOW_PRICE' => 'Permitir preço',
	'MARKETPLACE_STATS_OVERVIEW' => 'Resumo geral',
	'MARKETPLACE_STATS_ADS_LAST_7_DAYS' => 'Anúncios nos últimos 7 dias',
	'MARKETPLACE_STATS_TOP_CATEGORIES' => 'Categorias com mais anúncios',

	'MARKETPLACE_REPORT_STATUS_OPEN' => 'Aberta',
	'MARKETPLACE_REPORT_STATUS_CLOSED' => 'Fechada',


	'MARKETPLACE_ACP_DASHBOARD_EXPLAIN' => 'Acompanhe rapidamente pendências, denúncias e indicadores do Marketplace.',
	'MARKETPLACE_ACP_QUICK_LINKS' => 'Atalhos administrativos',
	'MARKETPLACE_REVIEW_PENDING_ADS' => 'Revisar anúncios pendentes',
	'MARKETPLACE_REVIEW_REPORTS' => 'Revisar denúncias',
	'MARKETPLACE_RECENT_PENDING_ADS' => 'Anúncios aguardando aprovação',
	'MARKETPLACE_VIEW_ALL_PENDING' => 'Ver todos os pendentes',
	'MARKETPLACE_VIEW_ALL_REPORTS' => 'Ver todas as denúncias',
	'MARKETPLACE_NO_PENDING_ADS' => 'Não há anúncios pendentes no momento.',
	'MARKETPLACE_NO_RECENT_REPORTS' => 'Não há denúncias recentes.',
	'MARKETPLACE_ACP_STORAGE_HEALTH' => 'Arquivos e armazenamento',
	'LOG_MARKETPLACE_CAT_ENABLED' => 'Categoria do Marketplace ativada',
	'LOG_MARKETPLACE_CAT_DISABLED' => 'Categoria do Marketplace desativada',
	'MARKETPLACE_CAT_ENABLED_MSG' => 'Categoria ativada com sucesso.',
	'MARKETPLACE_CAT_DISABLED_MSG' => 'Categoria desativada com sucesso.',
	'MARKETPLACE_CAT_ENABLED_EXPLAIN' => 'Categorias inativas não aparecem para novos anúncios, mas os anúncios existentes continuam preservados.',
	'MARKETPLACE_ACP_CATEGORY_HINT' => 'Use as regras para orientar o formulário público e reduzir anúncios incompletos.',
	'MARKETPLACE_CATEGORY_EMPTY' => 'Sem anúncios',
	'MARKETPLACE_TOTAL' => 'total',
	'MARKETPLACE_DISABLE' => 'Desativar',
	'MARKETPLACE_ENABLE_CATEGORY' => 'Ativar',
	'MARKETPLACE_DISABLE_CATEGORY' => 'Desativar',
]);
