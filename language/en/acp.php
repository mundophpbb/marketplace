<?php
/**
 *
 * Marketplace / Classificados Extension - ACP language (English)
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
	'MARKETPLACE_ACP_SETTINGS'          => 'Settings',
	'MARKETPLACE_ACP_CATEGORIES'        => 'Categories',
	'MARKETPLACE_ACP_CATEGORIES_EXPLAIN' => 'Manage marketplace categories, publishing rules, and allowed options.',

	'MARKETPLACE_ACP_ADS'               => 'Manage Ads',

	// Settings
	'MARKETPLACE_SETTINGS'              => 'Marketplace settings',
	'MARKETPLACE_ENABLE'                => 'Enable Marketplace',
	'MARKETPLACE_REQUIRE_APPROVAL'      => 'Require approval for new ads',
	'MARKETPLACE_REQUIRE_APPROVAL_EXPLAIN' => 'New ads will have status "Pending" until a moderator approves them.',
	'MARKETPLACE_MAX_ADS_PER_USER'      => 'Maximum active ads per user',
	'MARKETPLACE_EXPIRATION_DAYS'       => 'Ad expiration (days)',
	'MARKETPLACE_MAX_IMAGES'            => 'Maximum images per ad',
	'MARKETPLACE_ITEMS_PER_PAGE'        => 'Ads per page (front-end)',
	'MARKETPLACE_ALLOW_IMAGES'          => 'Allow image uploads',
	'MARKETPLACE_ENABLE_PRICE'          => 'Enable price field',
	'MARKETPLACE_CURRENCY_DEFAULT'      => 'Default currency symbol',

	'MARKETPLACE_SHOW_SOLD_ADS' => 'Show sold ads in public listings',
	'MARKETPLACE_SHOW_SOLD_ADS_EXPLAIN' => 'When enabled, ads marked as sold remain visible with the “Sold” badge. Contact remains disabled.',
	'MARKETPLACE_SOLD_VISIBLE_DAYS' => 'Keep sold ads visible for how many days',
	'MARKETPLACE_SOLD_VISIBLE_DAYS_EXPLAIN' => 'Use 0 to keep sold ads visible without a time limit.',

	'MARKETPLACE_SETTINGS_SAVED'        => 'Settings saved successfully.',
	'LOG_MARKETPLACE_SETTINGS'          => '<strong>Changed Marketplace settings</strong>',

	// Categories
	'MARKETPLACE_CATEGORIES'            => 'Categories management',
	'MARKETPLACE_ADD_CATEGORY'          => 'Add new category',
	'MARKETPLACE_EDIT_CATEGORY'         => 'Edit category',
	'MARKETPLACE_CAT_NAME'              => 'Category name',
	'MARKETPLACE_CAT_DESC'              => 'Description',
	'MARKETPLACE_CAT_ORDER'             => 'Display order',
	'MARKETPLACE_CAT_ENABLED'           => 'Enabled',
	'MARKETPLACE_CAT_ADS_COUNT'         => 'Ads',
	'MARKETPLACE_CAT_NAME_REQUIRED'     => 'Category name is required.',
	'MARKETPLACE_CAT_ADDED'             => 'Category added successfully.',
	'MARKETPLACE_CAT_UPDATED'           => 'Category updated successfully.',
	'MARKETPLACE_CAT_DELETED'           => 'Category deleted successfully.',
	'CONFIRM_DELETE_CAT'                => 'Delete this category? All ads in it will be moved to "uncategorized".',
	'LOG_MARKETPLACE_CAT_ADDED'         => '<strong>Added Marketplace category</strong>',
	'LOG_MARKETPLACE_CAT_EDITED'        => '<strong>Edited Marketplace category</strong> ID: %s',
	'LOG_MARKETPLACE_CAT_DELETED'       => '<strong>Deleted Marketplace category</strong>',

	// Manage ads (ACP)
	'MARKETPLACE_ACP_ADS_EXPLAIN' => 'Manage published, pending, hidden, sold and reported ads. Actions are grouped to make moderation clearer.',
	'MARKETPLACE_ACP_ADS_TOTAL' => 'Total found: %d ad(s).',
	'MARKETPLACE_ACP_AD_INFO' => 'Ad',
	'MARKETPLACE_ACP_QUICK_ACTIONS' => 'Quick actions',
	'MARKETPLACE_ACP_FEATURE_ACTIONS' => 'Featured',
	'MARKETPLACE_ACP_MODERATION_ACTIONS' => 'Moderation',
	'MARKETPLACE_ACP_VIEW_PUBLIC' => 'Public view',
	'MARKETPLACE_ADS'                   => 'All ads',
	'MARKETPLACE_FILTER_STATUS'         => 'Filter by status',
	'MARKETPLACE_APPROVE'               => 'Approve',
	'MARKETPLACE_REJECT'                => 'Reject / Hide',
	'MARKETPLACE_DELETE_AD'             => 'Delete ad',
	'MARKETPLACE_MARK_SOLD'             => 'Mark sold',
	'MARKETPLACE_AD_APPROVED'           => 'Ad approved successfully.',
	'MARKETPLACE_AD_REJECTED'           => 'Ad rejected/hidden.',
	'MARKETPLACE_AD_DELETED'            => 'Ad permanently deleted.',
	'MARKETPLACE_AD_MARKED_SOLD'        => 'Ad marked as sold.',
	'CONFIRM_DELETE_AD'                 => 'Are you sure you want to permanently delete this ad?',
	'LOG_MARKETPLACE_AD_APPROVED'       => '<strong>Approved Marketplace ad</strong> ID: %s',
	'LOG_MARKETPLACE_AD_REJECTED'       => '<strong>Rejected Marketplace ad</strong> ID: %s',
	'LOG_MARKETPLACE_AD_DELETED'        => '<strong>Deleted Marketplace ad</strong> ID: %s',

	// Package 3 - dashboard, category rules and reports
	'MARKETPLACE_ACP_DASHBOARD' => 'Dashboard',
	'MARKETPLACE_ACP_REPORTS' => 'Reports',
	'MARKETPLACE_ADVANCED_FEATURES' => 'Advanced features',
	'MARKETPLACE_ALLOW_REPORTS' => 'Allow ad reports',
	'MARKETPLACE_ALLOW_REPORTS_EXPLAIN' => 'Allows authenticated users to report public ads.',
	'MARKETPLACE_ALLOW_BUMP' => 'Allow ad bumping',
	'MARKETPLACE_ALLOW_BUMP_EXPLAIN' => 'Allows authors to bump active ads while respecting the configured interval.',
	'MARKETPLACE_BUMP_INTERVAL_DAYS' => 'Bump interval',
	'MARKETPLACE_BUMP_INTERVAL_DAYS_EXPLAIN' => 'Minimum number of days between author bumps. Moderators can bump at any time.',
	'MARKETPLACE_FEATURED_DAYS' => 'Default featured duration',
	'MARKETPLACE_FEATURED_DAYS_EXPLAIN' => 'Default number of days applied when featuring an ad.',
	'MARKETPLACE_CAT_RULES' => 'Category rules',
	'MARKETPLACE_CAT_EXPIRATION_DAYS' => 'Category-specific validity',
	'MARKETPLACE_CAT_EXPIRATION_DAYS_EXPLAIN' => 'Use 0 to apply the global Marketplace validity.',
	'MARKETPLACE_CAT_REQUIRE_PRICE' => 'Require price',
	'MARKETPLACE_CAT_REQUIRE_LOCATION' => 'Require location',
	'MARKETPLACE_CAT_REQUIRE_PHONE' => 'Require phone',
	'MARKETPLACE_CAT_ALLOW_IMAGES' => 'Allow images',
	'MARKETPLACE_GLOBAL_DEFAULT' => 'Global default',
	'MARKETPLACE_FEATURED' => 'Featured',
	'MARKETPLACE_FEATURE_AD' => 'Feature',
	'MARKETPLACE_UNFEATURE_AD' => 'Remove feature',
	'MARKETPLACE_AD_FEATURED' => 'Ad featured successfully.',
	'MARKETPLACE_AD_UNFEATURED' => 'Feature removed successfully.',
	'MARKETPLACE_BUMP_AD' => 'Bump',
	'MARKETPLACE_AD_BUMPED' => 'Ad bumped successfully.',
	'MARKETPLACE_LAST_BUMPED' => 'Bumped on',
	'MARKETPLACE_HIDDEN_REASON' => 'Reason',
	'MARKETPLACE_REPORTS' => 'Reports',
	'MARKETPLACE_REPORT' => 'Report',
	'MARKETPLACE_REPORTER' => 'Reporter',
	'MARKETPLACE_REPORT_REASON' => 'Reason',
	'MARKETPLACE_REPORT_NOTE' => 'Moderation note',
	'MARKETPLACE_REPORT_OPEN' => 'Open',
	'MARKETPLACE_REPORT_CLOSED' => 'Closed',
	'MARKETPLACE_REPORT_RESOLVE' => 'Resolve',
	'MARKETPLACE_REPORT_REOPEN' => 'Reopen',
	'MARKETPLACE_REPORT_RESOLVED' => 'Report resolved.',
	'MARKETPLACE_REPORT_REOPENED' => 'Report reopened.',
	'MARKETPLACE_REPORT_DELETED' => 'Report deleted.',
	'MARKETPLACE_REPORT_NOT_FOUND' => 'Report not found.',
	'MARKETPLACE_CREATED' => 'Created',
	'MARKETPLACE_RECENT_REPORTS' => 'Recent reports',
	'MARKETPLACE_STATS_TOTAL_ADS' => 'Total ads',
	'MARKETPLACE_STATS_ACTIVE_ADS' => 'Active',
	'MARKETPLACE_STATS_PENDING_ADS' => 'Pending',
	'MARKETPLACE_STATS_SOLD_ADS' => 'Sold',
	'MARKETPLACE_STATS_EXPIRED_ADS' => 'Expired',
	'MARKETPLACE_STATS_HIDDEN_ADS' => 'Hidden',
	'MARKETPLACE_STATS_FEATURED_ADS' => 'Featured',
	'MARKETPLACE_STATS_OPEN_REPORTS' => 'Open reports',
	'MARKETPLACE_STATS_TOTAL_REPORTS' => 'Total reports',
	'MARKETPLACE_STATS_TOTAL_IMAGES' => 'Images',
	'MARKETPLACE_STATS_DISK_USAGE' => 'Disk usage',

	// Pacote 3 - moderação avançada, denúncias, destaque e notificações
	'MARKETPLACE_ACP_STATS' => 'Statistics',
	'MARKETPLACE_FEATURED_DAYS_DEFAULT' => 'Default featured duration',
	'MARKETPLACE_FEATURED_DAYS_DEFAULT_EXPLAIN' => 'Default number of days to keep an ad featured.',
	'MARKETPLACE_CAT_ALLOWED_TYPES' => 'Allowed types',
	'MARKETPLACE_CAT_ALLOWED_TYPES_EXPLAIN' => 'Select the ad types accepted in this category. If none are selected, all will be used.',
	'MARKETPLACE_CAT_ALLOW_PRICE' => 'Allow price',
	'MARKETPLACE_STATS_OVERVIEW' => 'Overview',
	'MARKETPLACE_STATS_ADS_LAST_7_DAYS' => 'Ads in the last 7 days',
	'MARKETPLACE_STATS_TOP_CATEGORIES' => 'Top categories',

	'MARKETPLACE_REPORT_STATUS_OPEN' => 'Open',
	'MARKETPLACE_REPORT_STATUS_CLOSED' => 'Closed',


	'MARKETPLACE_ACP_DASHBOARD_EXPLAIN' => 'Quickly review pending ads, reports, and Marketplace indicators.',
	'MARKETPLACE_ACP_QUICK_LINKS' => 'Administrative shortcuts',
	'MARKETPLACE_REVIEW_PENDING_ADS' => 'Review pending ads',
	'MARKETPLACE_REVIEW_REPORTS' => 'Review reports',
	'MARKETPLACE_RECENT_PENDING_ADS' => 'Ads awaiting approval',
	'MARKETPLACE_VIEW_ALL_PENDING' => 'View all pending',
	'MARKETPLACE_VIEW_ALL_REPORTS' => 'View all reports',
	'MARKETPLACE_NO_PENDING_ADS' => 'There are no pending ads right now.',
	'MARKETPLACE_NO_RECENT_REPORTS' => 'There are no recent reports.',
	'MARKETPLACE_ACP_STORAGE_HEALTH' => 'Files and storage',
	'LOG_MARKETPLACE_CAT_ENABLED' => 'Marketplace category enabled',
	'LOG_MARKETPLACE_CAT_DISABLED' => 'Marketplace category disabled',
	'MARKETPLACE_CAT_ENABLED_MSG' => 'Category enabled successfully.',
	'MARKETPLACE_CAT_DISABLED_MSG' => 'Category disabled successfully.',
	'MARKETPLACE_CAT_ENABLED_EXPLAIN' => 'Inactive categories are hidden for new ads, but existing ads remain preserved.',
	'MARKETPLACE_ACP_CATEGORY_HINT' => 'Use category rules to guide the public form and reduce incomplete ads.',
	'MARKETPLACE_CATEGORY_EMPTY' => 'No ads',
	'MARKETPLACE_TOTAL' => 'total',
	'MARKETPLACE_DISABLE' => 'Disable',
	'MARKETPLACE_ENABLE_CATEGORY' => 'Enable',
	'MARKETPLACE_DISABLE_CATEGORY' => 'Disable',
]);
