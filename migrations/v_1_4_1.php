<?php
/**
 * Marketplace / Classificados Extension for phpBB.
 *
 * Converts bundled default categories to language keys so they can be translated
 * according to each user's selected board language.
 */

namespace mundophpbb\marketplace\migrations;

class v_1_4_1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['marketplace_i18n_categories']);
	}

	public static function depends_on()
	{
		return ['\mundophpbb\marketplace\migrations\install_data'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'convert_default_categories_to_language_keys']]],
			['config.add', ['marketplace_i18n_categories', 1]],
		];
	}

	public function revert_data()
	{
		return [];
	}

	public function convert_default_categories_to_language_keys()
	{
		$table = $this->table_prefix . 'marketplace_categories';

		if (!$this->db_tools->sql_table_exists($table))
		{
			return null;
		}

		$map = [
			'Veículos' => ['MARKETPLACE_CAT_VEHICLES', 'MARKETPLACE_CAT_VEHICLES_DESC'],
			'Veiculos' => ['MARKETPLACE_CAT_VEHICLES', 'MARKETPLACE_CAT_VEHICLES_DESC'],
			'Vehicles' => ['MARKETPLACE_CAT_VEHICLES', 'MARKETPLACE_CAT_VEHICLES_DESC'],
			'Imóveis' => ['MARKETPLACE_CAT_REAL_ESTATE', 'MARKETPLACE_CAT_REAL_ESTATE_DESC'],
			'Imoveis' => ['MARKETPLACE_CAT_REAL_ESTATE', 'MARKETPLACE_CAT_REAL_ESTATE_DESC'],
			'Real estate' => ['MARKETPLACE_CAT_REAL_ESTATE', 'MARKETPLACE_CAT_REAL_ESTATE_DESC'],
			'Eletrônicos e Celulares' => ['MARKETPLACE_CAT_ELECTRONICS', 'MARKETPLACE_CAT_ELECTRONICS_DESC'],
			'Eletronicos e Celulares' => ['MARKETPLACE_CAT_ELECTRONICS', 'MARKETPLACE_CAT_ELECTRONICS_DESC'],
			'Electronics and phones' => ['MARKETPLACE_CAT_ELECTRONICS', 'MARKETPLACE_CAT_ELECTRONICS_DESC'],
			'Casa e Jardim' => ['MARKETPLACE_CAT_HOME_GARDEN', 'MARKETPLACE_CAT_HOME_GARDEN_DESC'],
			'Home and garden' => ['MARKETPLACE_CAT_HOME_GARDEN', 'MARKETPLACE_CAT_HOME_GARDEN_DESC'],
			'Moda e Beleza' => ['MARKETPLACE_CAT_FASHION_BEAUTY', 'MARKETPLACE_CAT_FASHION_BEAUTY_DESC'],
			'Fashion and beauty' => ['MARKETPLACE_CAT_FASHION_BEAUTY', 'MARKETPLACE_CAT_FASHION_BEAUTY_DESC'],
			'Serviços' => ['MARKETPLACE_CAT_SERVICES', 'MARKETPLACE_CAT_SERVICES_DESC'],
			'Servicos' => ['MARKETPLACE_CAT_SERVICES', 'MARKETPLACE_CAT_SERVICES_DESC'],
			'Services' => ['MARKETPLACE_CAT_SERVICES', 'MARKETPLACE_CAT_SERVICES_DESC'],
			'Empregos e Oportunidades' => ['MARKETPLACE_CAT_JOBS_OPPORTUNITIES', 'MARKETPLACE_CAT_JOBS_OPPORTUNITIES_DESC'],
			'Jobs and opportunities' => ['MARKETPLACE_CAT_JOBS_OPPORTUNITIES', 'MARKETPLACE_CAT_JOBS_OPPORTUNITIES_DESC'],
			'Esportes e Lazer' => ['MARKETPLACE_CAT_SPORTS_LEISURE', 'MARKETPLACE_CAT_SPORTS_LEISURE_DESC'],
			'Sports and leisure' => ['MARKETPLACE_CAT_SPORTS_LEISURE', 'MARKETPLACE_CAT_SPORTS_LEISURE_DESC'],
			'Animais e Pets' => ['MARKETPLACE_CAT_PETS', 'MARKETPLACE_CAT_PETS_DESC'],
			'Animals and pets' => ['MARKETPLACE_CAT_PETS', 'MARKETPLACE_CAT_PETS_DESC'],
			'Outros' => ['MARKETPLACE_CAT_OTHER', 'MARKETPLACE_CAT_OTHER_DESC'],
			'Other' => ['MARKETPLACE_CAT_OTHER', 'MARKETPLACE_CAT_OTHER_DESC'],
		];

		foreach ($map as $old_name => $replacement)
		{
			$sql_ary = [
				'cat_name' => $replacement[0],
				'cat_desc' => $replacement[1],
			];

			$sql = 'UPDATE ' . $table . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE cat_name = '" . $this->db->sql_escape($old_name) . "'";
			$this->db->sql_query($sql);
		}

		return null;
	}
}
