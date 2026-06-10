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
			&& $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_follows');
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
						'ad_last_bumped'    => ['TIMESTAMP', 0],
						'ad_featured_until' => ['TIMESTAMP', 0],
						'ad_featured_by'    => ['UINT', 0],
						'ad_boosted_until'  => ['TIMESTAMP', 0],
						'ad_boosted_by'     => ['UINT', 0],
						'ad_views'          => ['UINT', 0],
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
						'status_expires' => ['INDEX', ['ad_status', 'ad_expires']],
					],
				],

				$this->table_prefix . 'marketplace_reports' => [
					'COLUMNS' => [
						'report_id'        => ['UINT', null, 'auto_increment'],
						'ad_id'            => ['UINT', 0],
						'reporter_id'      => ['UINT', 0],
						'report_reason'    => ['TEXT_UNI', ''],
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
						'package_amount_cents'=> ['BINT', 0],
						'package_currency'    => ['VCHAR:10', ''],
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
				$this->table_prefix . 'marketplace_purchases',
				$this->table_prefix . 'marketplace_follows',
			],
		];
	}
}
