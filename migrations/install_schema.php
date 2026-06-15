<?php
/**
 *
 * Marketplace / Classificados Extension for phpBB.
 *
 * @copyright (c) 2026, Mundo phpBB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace mundophpbb\marketplace\migrations;

class install_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_categories')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_ads')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_images')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_reports')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_notifications')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_promotions')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_promotion_packages')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_purchases')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_reviews')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_forbidden_terms')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_moderation_logs')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_coupons')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_promo_periods')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_group_freebies')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_follows')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_category_fields')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_ad_field_values')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_conversations')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_messages')
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_message_blocks');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'marketplace_categories' => [
					'COLUMNS' => [
						'cat_id'        => ['UINT', null, 'auto_increment'],
						'cat_name'      => ['VCHAR:255', ''],
						'cat_desc'      => ['TEXT_UNI', ''],
						'cat_order'     => ['UINT', 0],
						'cat_enabled'   => ['BOOL', 1],
						'cat_expiration_days'  => ['UINT', 0],
						'cat_require_price'    => ['BOOL', 0],
						'cat_require_location' => ['BOOL', 0],
						'cat_require_phone'    => ['BOOL', 0],
						'cat_allow_price'      => ['BOOL', 1],
						'cat_allow_images'     => ['BOOL', 1],
						'cat_allowed_types'    => ['VCHAR:50', '1,2,3,4,5,6'],
						'cat_require_approval' => ['BOOL', 0],
					],
					'PRIMARY_KEY' => 'cat_id',
					'KEYS' => [
						'cat_order' => ['INDEX', 'cat_order'],
					],
				],
				$this->table_prefix . 'marketplace_ads' => [
					'COLUMNS' => [
						'ad_id'             => ['UINT', null, 'auto_increment'],
						'user_id'           => ['UINT', 0],
						'cat_id'            => ['UINT', 0],
						'ad_title'          => ['VCHAR:255', ''],
						'ad_desc'           => ['TEXT_UNI', ''],
						'ad_price'          => ['VCHAR:50', '0'],
						'ad_price_type'     => ['TINT:1', 2], // 1=fixed, 2=negotiable, 3=free, 4=on_request
						'ad_price_cents'    => ['BINT', 0],
						'ad_type'           => ['TINT:1', 1], // 1=sell, 2=buy, 3=trade, 4=service, 5=rent, 6=wanted
						'ad_condition'      => ['TINT:1', 0], // 0=n/a, 1=new, 2=used, 3=refurbished
						'ad_quantity'       => ['UINT', 1], // simple stock quantity; 0 = out of stock/sold out
						'ad_currency'       => ['VCHAR:10', 'R$'],
						'ad_location'       => ['VCHAR:255', ''],
						'ad_city'           => ['VCHAR:120', ''],
						'ad_region'         => ['VCHAR:120', ''],
						'ad_country'        => ['VCHAR:120', ''],
						'ad_postal_code'    => ['VCHAR:30', ''],
						'ad_location_approx'=> ['BOOL', 0],
						'ad_latitude'       => ['VCHAR:32', ''],
						'ad_longitude'      => ['VCHAR:32', ''],
						'ad_conservation'   => ['VCHAR:255', ''],
						'ad_delivery_options' => ['VCHAR:50', ''],
						'ad_phone'          => ['VCHAR:50', ''],
						'ad_paypal_email'   => ['VCHAR:255', ''],
						'ad_status'         => ['TINT:3', 0], // 0=pending, 1=active, 2=sold, 3=expired, 4=hidden
						'ad_created'        => ['TIMESTAMP', 0],
						'ad_updated'        => ['TIMESTAMP', 0],
						'ad_expires'        => ['TIMESTAMP', 0],
						'ad_sold_at'        => ['TIMESTAMP', 0],
						'ad_expired_at'     => ['TIMESTAMP', 0],
						'ad_last_renewed'   => ['TIMESTAMP', 0],
						'ad_approved_at'    => ['TIMESTAMP', 0],
						'ad_approved_by'    => ['UINT', 0],
						'ad_hidden_at'      => ['TIMESTAMP', 0],
						'ad_hidden_by'      => ['UINT', 0],
						'ad_hidden_reason'  => ['TEXT_UNI', ''],
						'ad_refusal_reason' => ['TEXT_UNI', ''],
						'ad_suspicious'     => ['BOOL', 0],
						'ad_removed_at'     => ['TIMESTAMP', 0],
						'ad_removed_by'     => ['UINT', 0],
						'ad_last_bumped'    => ['TIMESTAMP', 0],
						'ad_featured_until' => ['TIMESTAMP', 0],
						'ad_featured_by'    => ['UINT', 0],
						'ad_boosted_until'  => ['TIMESTAMP', 0],
						'ad_boosted_by'     => ['UINT', 0],
						'ad_views'          => ['UINT', 0],
						'ad_contact_count'  => ['UINT', 0],
						'ad_contact_method' => ['TINT:1', 1], // 1=pm, 2=phone, 3=both
					],
					'PRIMARY_KEY' => 'ad_id',
					'KEYS' => [
						'user_id'   => ['INDEX', 'user_id'],
						'cat_id'    => ['INDEX', 'cat_id'],
						'status'    => ['INDEX', 'ad_status'],
						'created'   => ['INDEX', 'ad_created'],
						'type'      => ['INDEX', 'ad_type'],
						'ad_condition' => ['INDEX', 'ad_condition'],
						'price'     => ['INDEX', 'ad_price_cents'],
						'quantity'  => ['INDEX', 'ad_quantity'],
						'featured'  => ['INDEX', 'ad_featured_until'],
						'boosted'   => ['INDEX', 'ad_boosted_until'],
						'bumped'    => ['INDEX', 'ad_last_bumped'],
						'expires'   => ['INDEX', 'ad_expires'],
						'updated'   => ['INDEX', 'ad_updated'],
						'views'     => ['INDEX', 'ad_views'],
						'contacts'  => ['INDEX', 'ad_contact_count'],
						'city'      => ['INDEX', 'ad_city'],
						'region'    => ['INDEX', 'ad_region'],
						'country'   => ['INDEX', 'ad_country'],
						'geo'       => ['INDEX', ['ad_latitude', 'ad_longitude']],
						'status_expires' => ['INDEX', ['ad_status', 'ad_expires']],
					],
				],


				$this->table_prefix . 'marketplace_favorites' => [
					'COLUMNS' => [
						'favorite_id' => ['UINT', null, 'auto_increment'],
						'user_id' => ['UINT', 0],
						'ad_id' => ['UINT', 0],
						'favorite_time' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'favorite_id',
					'KEYS' => [
						'user_ad' => ['UNIQUE', ['user_id', 'ad_id']],
						'user_id' => ['INDEX', 'user_id'],
						'ad_id' => ['INDEX', 'ad_id'],
					],
				],
				$this->table_prefix . 'marketplace_compare' => [
					'COLUMNS' => [
						'compare_id' => ['UINT', null, 'auto_increment'],
						'user_id' => ['UINT', 0],
						'ad_id' => ['UINT', 0],
						'compare_time' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'compare_id',
					'KEYS' => [
						'user_ad' => ['UNIQUE', ['user_id', 'ad_id']],
						'user_id' => ['INDEX', 'user_id'],
						'ad_id' => ['INDEX', 'ad_id'],
					],
				],
				$this->table_prefix . 'marketplace_category_fields' => [
					'COLUMNS' => [
						'field_id' => ['UINT', null, 'auto_increment'],
						'cat_id' => ['UINT', 0],
						'field_label' => ['VCHAR:255', ''],
						'field_type' => ['VCHAR:20', 'text'],
						'field_required' => ['BOOL', 0],
						'field_order' => ['UINT', 0],
					],
					'PRIMARY_KEY' => 'field_id',
					'KEYS' => [
						'cat_id' => ['INDEX', 'cat_id'],
					],
				],
				$this->table_prefix . 'marketplace_ad_field_values' => [
					'COLUMNS' => [
						'value_id' => ['UINT', null, 'auto_increment'],
						'ad_id' => ['UINT', 0],
						'field_id' => ['UINT', 0],
						'field_value' => ['TEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'value_id',
					'KEYS' => [
						'ad_field' => ['UNIQUE', ['ad_id', 'field_id']],
						'ad_id' => ['INDEX', 'ad_id'],
						'field_id' => ['INDEX', 'field_id'],
					],
				],
				$this->table_prefix . 'marketplace_conversations' => [
					'COLUMNS' => [
						'conversation_id' => ['UINT', null, 'auto_increment'],
						'ad_id' => ['UINT', 0],
						'buyer_user_id' => ['UINT', 0],
						'seller_user_id' => ['UINT', 0],
						'conversation_status' => ['TINT:1', 0],
						'conversation_created' => ['TIMESTAMP', 0],
						'conversation_updated' => ['TIMESTAMP', 0],
						'last_message_time' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'conversation_id',
					'KEYS' => [
						'ad_buyer_seller' => ['UNIQUE', ['ad_id', 'buyer_user_id', 'seller_user_id']],
						'buyer' => ['INDEX', 'buyer_user_id'],
						'seller' => ['INDEX', 'seller_user_id'],
						'updated' => ['INDEX', 'last_message_time'],
					],
				],
				$this->table_prefix . 'marketplace_messages' => [
					'COLUMNS' => [
						'message_id' => ['UINT', null, 'auto_increment'],
						'conversation_id' => ['UINT', 0],
						'ad_id' => ['UINT', 0],
						'sender_user_id' => ['UINT', 0],
						'recipient_user_id' => ['UINT', 0],
						'message_text' => ['TEXT_UNI', ''],
						'message_ip' => ['VCHAR:40', ''],
						'message_time' => ['TIMESTAMP', 0],
						'message_read' => ['BOOL', 0],
						'message_reported' => ['BOOL', 0],
					],
					'PRIMARY_KEY' => 'message_id',
					'KEYS' => [
						'conversation' => ['INDEX', 'conversation_id'],
						'ad_id' => ['INDEX', 'ad_id'],
						'sender' => ['INDEX', 'sender_user_id'],
						'recipient_read' => ['INDEX', ['recipient_user_id', 'message_read']],
						'time' => ['INDEX', 'message_time'],
					],
				],
				$this->table_prefix . 'marketplace_message_blocks' => [
					'COLUMNS' => [
						'block_id' => ['UINT', null, 'auto_increment'],
						'blocker_user_id' => ['UINT', 0],
						'blocked_user_id' => ['UINT', 0],
						'ad_id' => ['UINT', 0],
						'block_reason' => ['TEXT_UNI', ''],
						'block_time' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'block_id',
					'KEYS' => [
						'block_pair_ad' => ['UNIQUE', ['blocker_user_id', 'blocked_user_id', 'ad_id']],
						'blocked_user' => ['INDEX', 'blocked_user_id'],
					],
				],
				$this->table_prefix . 'marketplace_reports' => [
					'COLUMNS' => [
						'report_id'        => ['UINT', null, 'auto_increment'],
						'ad_id'            => ['UINT', 0],
						'reporter_id'      => ['UINT', 0],
						'report_reason'    => ['TEXT_UNI', ''],
						'report_type'      => ['VCHAR:20', 'ad'],
						'target_user_id'   => ['UINT', 0],
						'review_id'        => ['UINT', 0],
						'report_status'    => ['TINT:1', 0],
						'report_created'   => ['TIMESTAMP', 0],
						'report_closed'    => ['TIMESTAMP', 0],
						'report_closed_by' => ['UINT', 0],
						'report_note'      => ['TEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'report_id',
					'KEYS' => [
						'ad_id'       => ['INDEX', 'ad_id'],
						'reporter_id' => ['INDEX', 'reporter_id'],
						'status'      => ['INDEX', 'report_status'],
					],
				],
				$this->table_prefix . 'marketplace_notifications' => [
					'COLUMNS' => [
						'notification_id'      => ['UINT', null, 'auto_increment'],
						'user_id'              => ['UINT', 0],
						'ad_id'                => ['UINT', 0],
						'notification_type'    => ['VCHAR:50', ''],
						'notification_title'   => ['VCHAR:255', ''],
						'notification_message' => ['TEXT_UNI', ''],
						'notification_read'    => ['BOOL', 0],
						'notification_time'    => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'notification_id',
					'KEYS' => [
						'user_id' => ['INDEX', 'user_id'],
						'ad_id'   => ['INDEX', 'ad_id'],
						'unread'  => ['INDEX', ['user_id', 'notification_read']],
					],
				],
				$this->table_prefix . 'marketplace_promotions' => [
					'COLUMNS' => [
						'promotion_id'        => ['UINT', null, 'auto_increment'],
						'ad_id'               => ['UINT', 0],
						'user_id'             => ['UINT', 0],
						'promotion_type'      => ['VCHAR:20', ''],
						'package_id'          => ['UINT', 0],
						'promotion_status'    => ['TINT:1', 0],
						'promotion_days'      => ['UINT', 0],
						'promotion_amount_cents' => ['BINT', 0],
						'promotion_currency'  => ['VCHAR:10', ''],
						'payment_provider'    => ['VCHAR:50', 'manual'],
						'payment_reference'   => ['VCHAR:255', ''],
						'promotion_requested'=> ['TIMESTAMP', 0],
						'promotion_decided'  => ['TIMESTAMP', 0],
						'promotion_decided_by' => ['UINT', 0],
						'promotion_note'     => ['TEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'promotion_id',
					'KEYS' => [
						'ad_id' => ['INDEX', 'ad_id'],
						'user_id' => ['INDEX', 'user_id'],
						'status' => ['INDEX', 'promotion_status'],
						'type_status' => ['INDEX', ['promotion_type', 'promotion_status']],
						'package_id' => ['INDEX', 'package_id'],
					],
				],

				$this->table_prefix . 'marketplace_promotion_packages' => [
					'COLUMNS' => [
						'package_id'          => ['UINT', null, 'auto_increment'],
						'package_title'       => ['VCHAR:255', ''],
						'package_desc'        => ['TEXT_UNI', ''],
						'package_type'        => ['VCHAR:20', 'featured'],
						'package_days'        => ['UINT', 7],
						'package_boosts'      => ['UINT', 0],
						'package_ad_limit'    => ['UINT', 0],
						'package_amount_cents'=> ['BINT', 0],
						'package_currency'    => ['VCHAR:10', ''],
						'package_billing_cycle' => ['VCHAR:20', 'none'],
						'package_auto_renew'  => ['BOOL', 0],
						'package_is_professional' => ['BOOL', 0],
						'package_enabled'     => ['BOOL', 1],
						'package_order'       => ['UINT', 0],
						'package_created'     => ['TIMESTAMP', 0],
						'package_updated'     => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'package_id',
					'KEYS' => [
						'type_enabled' => ['INDEX', ['package_type', 'package_enabled']],
						'package_order' => ['INDEX', 'package_order'],
					],
				],
				$this->table_prefix . 'marketplace_coupons' => [
					'COLUMNS' => [
						'coupon_id' => ['UINT', null, 'auto_increment'],
						'coupon_code' => ['VCHAR:50', ''],
						'coupon_desc' => ['TEXT_UNI', ''],
						'discount_type' => ['VCHAR:20', 'percent'],
						'discount_value' => ['BINT', 0],
						'coupon_currency' => ['VCHAR:10', 'BRL'],
						'coupon_starts' => ['TIMESTAMP', 0],
						'coupon_ends' => ['TIMESTAMP', 0],
						'coupon_usage_limit' => ['UINT', 0],
						'coupon_used_count' => ['UINT', 0],
						'coupon_enabled' => ['BOOL', 1],
						'coupon_created' => ['TIMESTAMP', 0],
						'coupon_updated' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'coupon_id',
					'KEYS' => [
						'coupon_code' => ['UNIQUE', 'coupon_code'],
						'enabled' => ['INDEX', 'coupon_enabled'],
					],
				],
				$this->table_prefix . 'marketplace_promo_periods' => [
					'COLUMNS' => [
						'period_id' => ['UINT', null, 'auto_increment'],
						'period_title' => ['VCHAR:255', ''],
						'period_package_type' => ['VCHAR:20', 'all'],
						'discount_type' => ['VCHAR:20', 'percent'],
						'discount_value' => ['BINT', 0],
						'period_starts' => ['TIMESTAMP', 0],
						'period_ends' => ['TIMESTAMP', 0],
						'period_enabled' => ['BOOL', 1],
					],
					'PRIMARY_KEY' => 'period_id',
					'KEYS' => [
						'type_enabled' => ['INDEX', ['period_package_type', 'period_enabled']],
					],
				],
				$this->table_prefix . 'marketplace_group_freebies' => [
					'COLUMNS' => [
						'free_id' => ['UINT', null, 'auto_increment'],
						'group_id' => ['UINT', 0],
						'free_featured' => ['BOOL', 0],
						'free_boosted' => ['BOOL', 0],
						'free_seller_plan' => ['BOOL', 0],
					],
					'PRIMARY_KEY' => 'free_id',
					'KEYS' => [
						'group_id' => ['UNIQUE', 'group_id'],
					],
				],

				$this->table_prefix . 'marketplace_payment_logs' => [
					'COLUMNS' => [
						'payment_log_id' => ['UINT', null, 'auto_increment'],
						'promotion_id' => ['UINT', 0],
						'payment_provider' => ['VCHAR:50', 'paypal'],
						'payment_reference' => ['VCHAR:255', ''],
						'payment_transaction_id' => ['VCHAR:255', ''],
						'payment_status' => ['VCHAR:50', ''],
						'payment_verification_status' => ['VCHAR:50', ''],
						'payment_validation_status' => ['VCHAR:100', ''],
						'payment_amount_cents' => ['BINT', 0],
						'payment_currency' => ['VCHAR:10', ''],
						'payment_receiver' => ['VCHAR:255', ''],
						'payment_raw' => ['MTEXT_UNI', ''],
						'payment_created' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'payment_log_id',
					'KEYS' => [
						'promotion_id' => ['INDEX', 'promotion_id'],
						'payment_reference' => ['INDEX', 'payment_reference'],
						'payment_transaction_id' => ['INDEX', 'payment_transaction_id'],
						'payment_created' => ['INDEX', 'payment_created'],
					],
				],

				$this->table_prefix . 'marketplace_purchases' => [
					'COLUMNS' => [
						'purchase_id'           => ['UINT', null, 'auto_increment'],
						'ad_id'                 => ['UINT', 0],
						'buyer_user_id'         => ['UINT', 0],
						'seller_user_id'        => ['UINT', 0],
						'purchase_status'       => ['TINT:1', 3],
						'purchase_amount_cents' => ['BINT', 0],
						'purchase_currency'     => ['VCHAR:10', ''],
						'payment_provider'      => ['VCHAR:50', 'paypal'],
						'payment_reference'     => ['VCHAR:255', ''],
						'purchase_created'      => ['TIMESTAMP', 0],
						'purchase_decided'      => ['TIMESTAMP', 0],
						'purchase_decided_by'   => ['UINT', 0],
						'purchase_note'         => ['TEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'purchase_id',
					'KEYS' => [
						'ad_id' => ['INDEX', 'ad_id'],
						'buyer_user_id' => ['INDEX', 'buyer_user_id'],
						'seller_user_id' => ['INDEX', 'seller_user_id'],
						'status' => ['INDEX', 'purchase_status'],
						'payment_reference' => ['INDEX', 'payment_reference'],
					],
				],

				$this->table_prefix . 'marketplace_reviews' => [
					'COLUMNS' => [
						'review_id'             => ['UINT', null, 'auto_increment'],
						'purchase_id'           => ['UINT', 0],
						'ad_id'                 => ['UINT', 0],
						'reviewer_user_id'      => ['UINT', 0],
						'reviewed_user_id'      => ['UINT', 0],
						'reviewer_role'         => ['VCHAR:10', 'buyer'],
						'review_score'          => ['TINT:1', 0],
						'review_comment'        => ['TEXT_UNI', ''],
						'review_time'           => ['TIMESTAMP', 0],
						'review_reported'       => ['BOOL', 0],
						'review_report_reason'  => ['TEXT_UNI', ''],
						'review_reported_by'    => ['UINT', 0],
						'review_reported_time'  => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'review_id',
					'KEYS' => [
						'purchase_id' => ['INDEX', 'purchase_id'],
						'ad_id' => ['INDEX', 'ad_id'],
						'reviewer' => ['INDEX', 'reviewer_user_id'],
						'reviewed' => ['INDEX', 'reviewed_user_id'],
						'purchase_reviewer' => ['UNIQUE', ['purchase_id', 'reviewer_user_id', 'reviewer_role']],
					],
				],
				$this->table_prefix . 'marketplace_follows' => [
					'COLUMNS' => [
						'follow_id'        => ['UINT', null, 'auto_increment'],
						'follower_user_id' => ['UINT', 0],
						'followed_user_id' => ['UINT', 0],
						'follow_created'   => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'follow_id',
					'KEYS' => [
						'follower' => ['INDEX', 'follower_user_id'],
						'followed' => ['INDEX', 'followed_user_id'],
						'pair'     => ['INDEX', ['follower_user_id', 'followed_user_id']],
					],
				],

				$this->table_prefix . 'marketplace_forbidden_terms' => [
					'COLUMNS' => [
						'term_id'      => ['UINT', null, 'auto_increment'],
						'term_text'    => ['VCHAR:255', ''],
						'term_enabled' => ['BOOL', 1],
						'term_created' => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'term_id',
					'KEYS' => ['enabled' => ['INDEX', 'term_enabled']],
				],
				$this->table_prefix . 'marketplace_user_limits' => [
					'COLUMNS' => [
						'limit_id' => ['UINT', null, 'auto_increment'],
						'user_id'  => ['UINT', 0],
						'max_ads'  => ['UINT', 0],
					],
					'PRIMARY_KEY' => 'limit_id',
					'KEYS' => ['user_id' => ['UNIQUE', 'user_id']],
				],
				$this->table_prefix . 'marketplace_group_limits' => [
					'COLUMNS' => [
						'limit_id' => ['UINT', null, 'auto_increment'],
						'group_id' => ['UINT', 0],
						'max_ads'  => ['UINT', 0],
					],
					'PRIMARY_KEY' => 'limit_id',
					'KEYS' => ['group_id' => ['UNIQUE', 'group_id']],
				],
				$this->table_prefix . 'marketplace_user_security' => [
					'COLUMNS' => [
						'user_id'           => ['UINT', 0],
						'seller_suspended'  => ['BOOL', 0],
						'publish_blocked'   => ['BOOL', 0],
						'verified_seller'   => ['BOOL', 0],
						'security_note'     => ['TEXT_UNI', ''],
						'updated_at'        => ['TIMESTAMP', 0],
						'updated_by'        => ['UINT', 0],
					],
					'PRIMARY_KEY' => 'user_id',
					'KEYS' => [
						'seller_suspended' => ['INDEX', 'seller_suspended'],
						'publish_blocked'  => ['INDEX', 'publish_blocked'],
					],
				],
				$this->table_prefix . 'marketplace_ad_edit_history' => [
					'COLUMNS' => [
						'history_id'   => ['UINT', null, 'auto_increment'],
						'ad_id'        => ['UINT', 0],
						'user_id'      => ['UINT', 0],
						'edit_time'    => ['TIMESTAMP', 0],
						'edit_summary' => ['TEXT_UNI', ''],
						'old_data'     => ['MTEXT_UNI', ''],
						'new_data'     => ['MTEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'history_id',
					'KEYS' => [
						'ad_id' => ['INDEX', 'ad_id'],
						'user_id' => ['INDEX', 'user_id'],
						'edit_time' => ['INDEX', 'edit_time'],
					],
				],
				$this->table_prefix . 'marketplace_moderation_logs' => [
					'COLUMNS' => [
						'log_id'         => ['UINT', null, 'auto_increment'],
						'ad_id'          => ['UINT', 0],
						'target_user_id' => ['UINT', 0],
						'admin_user_id'  => ['UINT', 0],
						'log_action'     => ['VCHAR:50', ''],
						'log_note'       => ['TEXT_UNI', ''],
						'log_time'       => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'log_id',
					'KEYS' => [
						'ad_id' => ['INDEX', 'ad_id'],
						'target_user_id' => ['INDEX', 'target_user_id'],
						'log_time' => ['INDEX', 'log_time'],
					],
				],
				$this->table_prefix . 'marketplace_images' => [
					'COLUMNS' => [
						'image_id'      => ['UINT', null, 'auto_increment'],
						'ad_id'         => ['UINT', 0],
						'image_filename'=> ['VCHAR:255', ''],
						'image_order'   => ['UINT', 0],
						'image_is_main' => ['BOOL', 0],
					],
					'PRIMARY_KEY' => 'image_id',
					'KEYS' => [
						'ad_id' => ['INDEX', 'ad_id'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'marketplace_categories',
				$this->table_prefix . 'marketplace_ads',
				$this->table_prefix . 'marketplace_images',
				$this->table_prefix . 'marketplace_reports',
				$this->table_prefix . 'marketplace_notifications',
				$this->table_prefix . 'marketplace_promotions',
				$this->table_prefix . 'marketplace_promotion_packages',
				$this->table_prefix . 'marketplace_coupons',
				$this->table_prefix . 'marketplace_promo_periods',
				$this->table_prefix . 'marketplace_group_freebies',
				$this->table_prefix . 'marketplace_payment_logs',
				$this->table_prefix . 'marketplace_purchases',
				$this->table_prefix . 'marketplace_reviews',
				$this->table_prefix . 'marketplace_forbidden_terms',
				$this->table_prefix . 'marketplace_user_limits',
				$this->table_prefix . 'marketplace_group_limits',
				$this->table_prefix . 'marketplace_user_security',
				$this->table_prefix . 'marketplace_ad_edit_history',
				$this->table_prefix . 'marketplace_moderation_logs',
				$this->table_prefix . 'marketplace_follows',
			],
		];
	}
}
