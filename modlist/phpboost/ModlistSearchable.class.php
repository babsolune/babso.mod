<?php
/*##################################################
 *                      ModlistSearchable.class.php
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

class ModlistSearchable extends AbstractSearchableExtensionPoint
{
	public function get_search_request($args)
	{
		$now = new Date();
		$authorized_categories = ModlistService::get_authorized_categories(Category::ROOT_CATEGORY);
		$weight = isset($args['weight']) && is_numeric($args['weight']) ? $args['weight'] : 1;

		return "SELECT " . $args['id_search'] . " AS id_search,
			modlist.id AS id_content,
			modlist.title AS title,
			(2 * FT_SEARCH_RELEVANCE(modlist.title, '" . $args['search'] . "') + (FT_SEARCH_RELEVANCE(modlist.contents, '" . $args['search'] . "') +
			FT_SEARCH_RELEVANCE(modlist.description, '" . $args['search'] . "')) / 2 ) / 3 * " . $weight . " AS relevance,
			CONCAT('" . PATH_TO_ROOT . "/modlist/" . (!ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? "index.php?url=/" : "") . "', category_id, '-', IF(category_id != 0, cat.rewrited_name, 'root'), '/', modlist.id, '-', modlist.rewrited_title) AS link
			FROM " . ModlistSetup::$modlist_table . " modlist
			LEFT JOIN ". ModlistSetup::$modlist_cats_table ." cat ON cat.id = modlist.category_id
			WHERE ( FT_SEARCH(modlist.title, '" . $args['search'] . "') OR FT_SEARCH(modlist.contents, '" . $args['search'] . "') OR FT_SEARCH_RELEVANCE(modlist.description, '" . $args['search'] . "') )
			AND category_id IN(" . implode(", ", $authorized_categories) . ")
			AND (published = 1 OR (published = 2 AND publication_start_date < '" . $now->get_timestamp() . "' AND (publication_end_date > '" . $now->get_timestamp() . "' OR publication_end_date = 0)))
			ORDER BY relevance DESC
			LIMIT 100 OFFSET 0";
	}
}
?>
