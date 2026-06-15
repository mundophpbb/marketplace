<?php
/**
 * Marketplace 1.4.20 - Reputation and trust reviews.
 */
namespace mundophpbb\marketplace\migrations;

class v_1_4_20 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\mundophpbb\\marketplace\\migrations\\v_1_4_19'];
	}

	public function effectively_installed()
	{
		return isset($this->config['marketplace_version']) && version_compare($this->config['marketplace_version'], '1.4.20', '>=') && $this->db_tools->sql_table_exists($this->table_prefix . 'marketplace_reviews');
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
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
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['marketplace_version', '1.4.20']],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [$this->table_prefix . 'marketplace_reviews'],
		];
	}
}
