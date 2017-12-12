<?php
/*##################################################
 *                             ModcatfullSetup.class.php
 *                            -------------------
 *   begin                : Month XX, 2017
 *   copyright            : (C) 2017 Firstname LASTNAME
 *   email                : nickname@phpboost.com
 *
 *
 ###################################################
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/

/**
 * @author Firstname LASTNAME <nickname@phpboost.com>
 */

class ModcatfullSetup extends DefaultModuleSetup
{
	public static $modcatfull_table;
	public static $modcatfull_cats_table;

	/**
	 * @var string[string] localized messages
	*/
	private $messages;

	public static function __static()
	{
		self::$modcatfull_table = PREFIX . 'modcatfull';
		self::$modcatfull_cats_table = PREFIX . 'modcatfull_cats';
	}

	public function upgrade($installed_version)
	{
		return '5.1.0';
	}

	public function install()
	{
		$this->drop_tables();
		$this->create_tables();
		$this->insert_data();
	}

	public function uninstall()
	{
		$this->drop_tables();
		ConfigManager::delete('modcatfull', 'config');
		ModcatfullService::get_keywords_manager()->delete_module_relations();
	}

	private function drop_tables()
	{
		PersistenceContext::get_dbms_utils()->drop(array(self::$modcatfull_table, self::$modcatfull_cats_table));
	}

	private function create_tables()
	{
		$this->create_modcatfull_table();
		$this->create_modcatfull_cats_table();
	}

	private function create_modcatfull_table()
	{
		$fields = array(
			'id' => array('type' => 'integer', 'length' => 11, 'autoincrement' => true, 'notnull' => 1),
			'category_id' => array('type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0),
			'thumbnail_url' => array('type' => 'string', 'length' => 255, 'notnull' => 1, 'default' => "''"),
			'title' => array('type' => 'string', 'length' => 250, 'notnull' => 1, 'default' => "''"),
			'rewrited_title' => array('type' => 'string', 'length' => 250, 'default' => "''"),
			'description' => array('type' => 'text', 'length' => 65000),
			'contents' => array('type' => 'text', 'length' => 65000),
			'views_number' => array('type' => 'integer', 'length' => 11, 'default' => 0),
			'custom_author_name' => array('type' =>  'string', 'length' => 255, 'default' => "''"),
			'author_user_id' => array('type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0),
			'displayed_author_name' => array('type' => 'boolean', 'notnull' => 1, 'default' => 1),
			'published' => array('type' => 'integer', 'length' => 1, 'notnull' => 1, 'default' => 0),
			'publication_start_date' => array('type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0),
			'publication_end_date' => array('type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0),
			'creation_date' => array('type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0),
			'updated_date' => array('type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0),
			'links_visibility' => array('type' => 'boolean', 'notnull' => 1, 'default' => 1),
			'file_url' => array('type' => 'string', 'length' => 255, 'default' => "''"),
			'file_size' => array('type' => 'bigint', 'length' => 18, 'default' => 0),
			'downloads_number' => array('type' => 'integer', 'length' => 11, 'default' => 0),
			'website_url' => array('type' => 'string', 'length' => 255, 'default' => "''"),
			'visits_number' => array('type' => 'integer', 'length' => 11, 'default' => 0),
			'sources' => array('type' => 'text', 'length' => 65000),
			'carousel' => array('type' => 'text', 'length' => 65000),
		);
		$options = array(
			'primary' => array('id'),
			'indexes' => array(
				'category_id' => array('type' => 'key', 'fields' => 'category_id'),
				'title' => array('type' => 'fulltext', 'fields' => 'title'),
				'description' => array('type' => 'fulltext', 'fields' => 'description'),
				'contents' => array('type' => 'fulltext', 'fields' => 'contents')
		));
		PersistenceContext::get_dbms_utils()->create_table(self::$modcatfull_table, $fields, $options);
	}

	private function create_modcatfull_cats_table()
	{
		RichCategory::create_categories_table(self::$modcatfull_cats_table);
	}

	private function insert_data()
	{
		$this->messages = LangLoader::get('install', 'modcatfull');
		$this->insert_modcatfull_cats_data();
		$this->insert_modcatfull_data();
	}

	private function insert_modcatfull_cats_data()
	{
		PersistenceContext::get_querier()->insert(self::$modcatfull_cats_table, array(
			'id' => 1,
			'id_parent' => 0,
			'c_order' => 1,
			'auth' => '',
			'rewrited_name' => Url::encode_rewrite($this->messages['default.category.name']),
			'name' => $this->messages['default.category.name'],
			'description' => $this->messages['default.category.description'],
			'image' => '/modcatfull/modcatfull.png'
		));
	}

	private function insert_modcatfull_data()
	{
		PersistenceContext::get_querier()->insert(self::$modcatfull_table, array(
			'id' => 1,
			'category_id' => 1,
			'thumbnail_url' => '/modcatfull/templates/images/default.png',
			'title' => $this->messages['default.itemcatfull.title'],
			'rewrited_title' => Url::encode_rewrite($this->messages['default.itemcatfull.title']),
			'description' => $this->messages['default.itemcatfull.description'],
			'contents' => $this->messages['default.itemcatfull.contents'],
			'views_number' => 0,
			'author_user_id' => 1,
			'custom_author_name' => '',
			'displayed_author_name' => Itemcatfull::DISPLAYED_AUTHOR_NAME,
			'published' => Itemcatfull::PUBLISHED_NOW,
			'publication_start_date' => 0,
			'publication_end_date' => 0,
			'creation_date' => time(),
			'updated_date' => 0,
			'file_url' => '',
			'file_size' => 0,
			'downloads_number' => 0,
			'website_url'=> '',
			'visits_number' => 0,
			'sources' => TextHelper::serialize(array()),
			'carousel' => TextHelper::serialize(array())
		));
	}
}
?>
