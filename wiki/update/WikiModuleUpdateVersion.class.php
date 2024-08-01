<?php
/**
 * @copyright   &copy; 2005-2023 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2023 03 30
 * @since       PHPBoost 6.0 - 2023 03 30
 */

class WikiModuleUpdateVersion extends ModuleUpdateVersion
{
	protected $articles = [];

	protected $categories = [];

	protected $articles_encoded_title = [];

	protected $actual_article = 0;

	public function __construct()
	{
		parent::__construct('wiki');

		$this->content_tables = [['name' => PREFIX . 'wiki', 'content_field' => 'content']];
		$this->modify_content_before_change_tables();
		
		self::$delete_old_files_list = [
			'/phpboost/WikiCategoriesCache.class.php',
			'/phpboost/WikiHomePageExtensionPoint.class.php',
			'/phpboost/WikiSitemapExtensionPoint.class.php',
			'/templates/js/bbcode.wiki.js',
			'/templates/js/bbcode.wiki.min.js',
			'/templates/admin_wiki.tpl',
			'/templates/admin_wiki_groups.tpl',
			'/templates/explorer.tpl',
			'/templates/favorites.tpl',
			'/templates/history.tpl',
			'/templates/index.tpl',
			'/templates/post.tpl',
			'/templates/property.tpl',
			'/templates/wiki.tpl',
			'/templates/wiki_js_tool.tpl',
			'/templates/wiki_search_form.tpl',
			'/templates/wiki_tools.tpl',
			'/action.php',
			'/admin_wiki_groups.php',
			'/admin_wiki.php',
			'/explorer.php',
			'/favorites.php',
			'/history.php',
			'/post_js_tools.php',
			'/post.php',
			'/print.php',
			'/property.php',
			'/wiki_auth.php',
			'/wiki_bread_crumb.php',
			'/wiki_functions.php',
			'/wiki_tools.php',
			'/wiki.php',
			'/xmlhttprequest.php',
		];

		$this->database_columns_to_modify = [
			[
				'table_name' => PREFIX . 'wiki_articles',
				'columns' => [
					'id_cat' 		=> 'id_category INT(11) NOT NULL DEFAULT 0',
					'encoded_title' => 'rewrited_title VARCHAR(255) NOT NULL DEFAULT ""',
					'hits' 			=> 'views_number INT(11) NOT NULL DEFAULT 0'
				]
			],
			[
				'table_name' => PREFIX . 'wiki_contents',
				'columns' => [
					'id_contents'	=> 'content_id INT(11) AUTO_INCREMENT NOT NULL',
					'id_article' 	=> 'item_id INT(11) NOT NULL DEFAULT 0',
					'timestamp'		=> 'update_date INT(11) NOT NULL DEFAULT 0',
					'activ' 		=> 'active_content INT(11) NOT NULL DEFAULT 0',
					'user_id' 		=> 'author_user_id INT(11) NOT NULL DEFAULT 0'
				]
			],
			[
				'table_name' => PREFIX . 'wiki_favorites',
				'columns' => [
					'id'		 => 'track_id INT(11) AUTO_INCREMENT NOT NULL',
					'id_article' => 'track_item_id INT(11) NOT NULL DEFAULT 0',
					'user_id'	 => 'track_user_id INT(11) NOT NULL DEFAULT 0'
				]
			]
		];

		$this->database_columns_to_add = [
			[
				'table_name' => PREFIX . 'wiki_articles',
				'columns' => [
					'i_order' 				=> ['type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0],
					'creation_date' 		=> ['type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0],
					'published' 			=> ['type' => 'integer', 'length' => 1, 'notnull' => 1, 'default' => 0],
					'publishing_start_date' => ['type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0],
					'publishing_end_date'	=> ['type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0]
				]
			],
			[
				'table_name' => PREFIX . 'wiki_contents',
				'columns' => [
					'title' 		=> ['type' => 'string', 'length' => 255, 'notnull' => 1, 'default' => "''"],
					'summary' 		=> ['type' => 'text', 'length' => 65000],
					'thumbnail' 	=> ['type' => 'string', 'length' => 255, 'notnull' => 1, 'default' => "''"],
					'content_level' => ['type' => 'integer', 'length' => 1, 'default' => 0],
					'custom_level' 	=> ['type' => 'text', 'length' => 65000],
					'sources' 		=> ['type' => 'text', 'length' => 65000]
				]
			],
			[
				'table_name' => PREFIX . 'wiki_cats',
				'columns' => [
					'name' 						=> ['type' => 'string', 'length' => 255, 'notnull' => 1, 'default' => "''"],
					'rewrited_name' 			=> ['type' => 'string', 'length' => 255, 'notnull' => 1, 'default' => "''"],
					'c_order'					=> ['type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0],
					'special_authorizations' 	=> ['type' => 'integer', 'length' => 11, 'notnull' => 1, 'default' => 0],
					'auth' 						=> ['type' => 'text', 'length' => 65000],
					'description' 				=> ['type' => 'text', 'length' => 65000],
					'thumbnail' 				=> ['type' => 'string', 'length' => 255, 'notnull' => 1, 'default' => "''"]
				]
			]
		];

		// TODO : modify keys/indexes

		$this->database_columns_to_delete = [
			[
				'table_name' => PREFIX . 'wiki_articles',
				'columns' 	 => ['id_contents', 'title', 'is_cat', 'defined_status', 'redirect', 'auth']
			],
			[
				'table_name' => PREFIX . 'wiki_contents',
				'columns'	 => ['menu', 'user_ip']
			],
			[
				'table_name' => PREFIX . 'wiki_cats',
				'columns' 	 => ['article_id']
			]
		];
	}

	protected function modify_content_before_change_tables()
	{
		// get articles
		$result = PersistenceContext::get_querier()->select("SELECT * FROM " . PREFIX . "wiki_articles");
		while ($row = $result->fetch()) {
			$this->articles[$row['id']] = $row;
			$this->articles_encoded_title[$row['id']] = $row['encoded_title'];
		}
		$result->dispose();
		
		// get categories
		$result = PersistenceContext::get_querier()->select("SELECT * FROM " . PREFIX . "wiki_cats");
		while ($row = $result->fetch()) {
			$this->categories[$row['id']] = $row;
		}
		$result->dispose();

		// get contents
		$result = PersistenceContext::get_querier()->select("SELECT * FROM " . PREFIX . "wiki_contents");
		$contents = [];
		while ($row = $result->fetch()) {
			$contents[$row['id_contents']] = $row;
		}
		$result->dispose();
		
		foreach ($contents as $id => &$row) {
			$this->actual_article = $id;
			// replace wiki links
			$contents[$id]['content'] = preg_replace_callback('#href="\/wiki\/(.+)"#isU', [$this, 'replace_wiki_link'], $row['content']);
			for ($i = 2; $i < 7; $i++) {
				$regex = sprintf('#<h%d class="formatter-title wiki-paragraph-%d" id="paragraph-(.+)">(.+)<\/h%d>#mi', $i, $i, $i);
				$replacement = sprintf('<h%d class="formatter-title">$2</h%d>', $i, $i);
				// replace wiki titles
				$contents[$id]['content'] = preg_replace($regex, $replacement, $row['content'],-1,$count);
			}
		}
		
		// Save contents
		foreach ($contents as $row) {
			PersistenceContext::get_querier()->update(PREFIX . "wiki_contents", $row, "WHERE id_contents = " . $row['id_contents']);
		}

	}

	protected function replace_wiki_link($matches)
	{
		$parts = explode('#', $matches[1]);
		$encoded_title_requested = $parts[0];
		$paragraph = $parts[1] ?? false;
		$target = array_search($encoded_title_requested, $this->articles_encoded_title);
		if ($target !== false) {
			// article founded
			$id_cat = $this->articles[$target]['id_cat'];
				$link = 'href="/wiki/' . $id_cat . '-' . $this->articles_encoded_title[$this->categories[$id_cat]['article_id']] . '/';
				$link .= $this->articles[$target]['id'] . '-' . $this->articles[$target]['encoded_title'];
				if ($paragraph !== false)
				{
					$link .= "#$paragraph";
				}
				$link .= '"';
				return $link;
		}
		// If title not found or id_cat is not a category, report a 404 link error
		$id_cat = $this->articles[$this->actual_article]['id_cat'] ?? 0;
		$page_url = $id_cat . '-' . $this->articles_encoded_title[$this->categories[$id_cat]['article_id']] . '/';
		$page_url .= $this->articles[$this->actual_article]['id'] . '-' . $this->articles[$this->actual_article]['encoded_title'];
		$error_404 = new AdminError404($matches[1], $page_url);
		AdminError404DAO::instance()->save($error_404, 'times');
		// We leave the original link (in error) intact
		return $matches[0];
	}

	protected function execute_module_specific_changes()
	{
		// disable menus for old wiki
		$wikitreemenu_id = 0;
		try {
			$wikitreemenu_id = PersistenceContext::get_querier()->get_column_value(DB_TABLE_MENUS, 'id', 'WHERE title = "wikitree/WikitreeModuleMiniMenu"');
		} catch (RowNotFoundException $e) {
		}
		if ($wikitreemenu_id) {
			$menu = MenuService::load($wikitreemenu_id);
			MenuService::delete($menu);
			MenuService::generate_cache();
		}
		
		$wikistatusmenu_id = 0;
		try {
			$wikistatusmenu_id = PersistenceContext::get_querier()->get_column_value(DB_TABLE_MENUS, 'id', 'WHERE title = "WikiStatus/WikiStatusModuleMiniMenu"');
		} catch (RowNotFoundException $e) {
		}
		if ($wikistatusmenu_id) {
			$menu = MenuService::load($wikistatusmenu_id);
			MenuService::delete($menu);
			MenuService::generate_cache();
		}
		
		// Set category from old `ìs_cat`
		$result = $this->querier->select('SELECT i.id, i.title, i.rewrited_title, i.is_cat, i.defined_status, cat.id as cat_id
            FROM ' . PREFIX . 'wiki_articles i
            LEFT JOIN ' . PREFIX . 'wiki_cats cat ON cat.article_id = i.id
            LEFT JOIN ' . PREFIX . 'wiki_contents c ON c.item_id = i.id
            WHERE i.is_cat = 1'
		);
		
		while ($row = $result->fetch()) {
			$this->querier->update(PREFIX . 'wiki_cats', ['name' => $row['title'], 'rewrited_name' => $row['rewrited_title']], 'WHERE id = :id', ['id' => $row['cat_id']]);
		}
		$result->dispose();
		
		// Set content from old article
		$result = $this->querier->select('SELECT i.id, i.title, i.rewrited_title, i.defined_status, cat.id as cat_id
            FROM ' . PREFIX . 'wiki_articles i
            LEFT JOIN ' . PREFIX . 'wiki_contents c ON c.item_id = i.id
			LEFT JOIN ' . PREFIX . 'wiki_cats cat ON cat.article_id = i.id
            WHERE i.id = c.item_id'
		);

		while ($row = $result->fetch()) {
			$this->querier->update(PREFIX . 'wiki_contents', ['title' => $row['title'], 'custom_level' => $row['defined_status']], 'WHERE item_id = :id', ['id' => $row['id']]);
			$this->querier->update(PREFIX . 'wiki_cats', ['name' => $row['title'], 'rewrited_name' => $row['rewrited_title']], 'WHERE id = :id', ['id' => $row['cat_id']]);
		}
		$result->dispose();
		
		// Set categories auth from old `ìs_cat`
		$result = $this->querier->select('SELECT i.id, i.auth, i.is_cat, i.defined_status, cat.id as cat_id, cat.name as cat_name, cat.rewrited_name as cat_rewrited_name
            FROM ' . PREFIX . 'wiki_articles i
            LEFT JOIN ' . PREFIX . 'wiki_cats cat ON cat.article_id = i.id
            WHERE i.is_cat = 1'
		);

		while ($row = $result->fetch()) {
			if ($row['auth'] == null || $row['auth'] == '')
				$row['auth'] = [];
			$this->querier->update(PREFIX . 'wiki_cats', ['name' => $row['cat_name'], 'rewrited_name' => $row['cat_rewrited_name'], 'auth' => $row['auth']], 'WHERE id = :id', ['id' => $row['cat_id']]);
		}
		$result->dispose();
	}
}