<?php
/**
 * @copyright   &copy; 2005-2023 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2023 03 27
 * @since       PHPBoost 6.0 - 2022 11 18
 */

class GuideService
{
	private static $db_querier;
	protected static $module_id = 'guide';

	public static function __static()
	{
		self::$db_querier = PersistenceContext::get_querier();
	}

	/**
	 * @desc Count items number.
	 * @param string $condition (optional) : Restriction to apply to the list of items
	 */
	public static function count($condition = '', $parameters = array())
	{
		return self::$db_querier->count(GuideSetup::$guide_table, $condition, $parameters);
	}

	/**
	 * @desc Create a new entry in the database table.
	 * @param string[] $item : new GuideItem
	 */
	public static function add(GuideItem $item)
	{
		$result = self::$db_querier->insert(GuideSetup::$guide_table, $item->get_properties());

		return $result->get_last_inserted_id();
	}

	public static function get_last_content_id()
	{
		$result = self::$db_querier->select_single_row_query('SELECT MAX(content_id) FROM ' . GuideSetup::$guide_contents_table);
		return $result;
	}

	/**
	 * @desc Create a new item content.
	 * @param string[] $content new GuideItemContent
	 */
	public static function add_content(GuideItemContent $content)
	{
		$result = self::$db_querier->insert(GuideSetup::$guide_contents_table, $content->get_properties());

		return $result->get_last_inserted_id();
	}

	/**
	 * @desc Update an entry.
	 * @param string[] $item : GuideItem to update
	 */
	public static function update(GuideItem $item)
	{
		self::$db_querier->update(GuideSetup::$guide_table, $item->get_properties(), 'WHERE id=:id', array('id' => $item->get_id()));
	}

	/**
	 * @desc Update an entry.
	 * @param string[] $item : GuideItem to update
	 */
	public static function update_content(GuideItemContent $item_content)
	{
		self::$db_querier->update(GuideSetup::$guide_contents_table, $item_content->get_properties(), 'WHERE content_id=:id', array('id' => $item_content->get_content_id()));
	}

	/**
	 * @desc Update the position of an item.
	 * @param string[] $id : id of the item to update
	 * @param string[] $position : new item position
	 */
	public static function update_position($id, $position)
	{
		self::$db_querier->update(GuideSetup::$guide_table, array('i_order' => $position), 'WHERE id=:id', array('id' => $id));
	}

	public static function update_views_number(GuideItem $item)
	{
		self::$db_querier->update(GuideSetup::$guide_table, array('views_number' => $item->get_views_number()), 'WHERE id=:id', array('id' => $item->get_id()));
	}

	/**
	 * @desc Delete an entry with all ite contents.
	 * @param string $condition : Restriction to apply to the list
	 * @param string[] $parameters : Parameters of the condition
	 */
	public static function delete(int $id, $content_id = '')
	{
		if (AppContext::get_current_user()->is_readonly())
        {
            $controller = PHPBoostErrors::user_in_read_only();
            DispatchManager::redirect($controller);
        }

		if ($content_id == 0)
			self::$db_querier->delete(GuideSetup::$guide_table, 'WHERE id=:id', array('id' => $id));
		else
			self::$db_querier->delete(GuideSetup::$guide_contents_table, 'WHERE item_id=:id AND content_id = :content_id', array('id' => $id, 'content_id' => $content_id));

		self::$db_querier->delete(DB_TABLE_EVENTS, 'WHERE module=:module AND id_in_module=:id', array('module' => 'guide', 'id' => $id));

        self::delete_tracked_item($id);

		CommentsService::delete_comments_topic_module('guide', $id);
		KeywordsService::get_keywords_manager()->delete_relations($id);
		NotationService::delete_notes_id_in_module('guide', $id);
	}

	/**
	 * @desc Restore a content of an entry.
	 * @param string $condition : Restriction to apply to the list
	 * @param string[] $parameters : Parameters of the condition
	 */
	public static function restore_content($id, $content_id)
	{
		if (AppContext::get_current_user()->is_readonly())
        {
            $controller = PHPBoostErrors::user_in_read_only();
            DispatchManager::redirect($controller);
        }
		self::$db_querier->update(GuideSetup::$guide_contents_table, array('active_content' => '1'), 'WHERE item_id = :id AND content_id = :content_id', array('id' => $id, 'content_id' => $content_id));
	}

	/**
	 * @desc Return the item with all its properties from its id.
	 * @param int $id Item identifier
	 */
	public static function get_item(int $id)
	{
		$row = self::$db_querier->select_single_row_query('SELECT i.*, c.*, member.*, notes.average_notes, notes.notes_number, note.note
		FROM ' . GuideSetup::$guide_table .' i
		LEFT JOIN ' . GuideSetup::$guide_contents_table . ' c ON c.item_id = i.id
		LEFT JOIN ' . DB_TABLE_MEMBER . ' member ON member.user_id = c.author_user_id
		LEFT JOIN ' . DB_TABLE_AVERAGE_NOTES . ' notes ON notes.id_in_module = i.id AND notes.module_name = :module_id
		LEFT JOIN ' . DB_TABLE_NOTE . ' note ON note.id_in_module = i.id AND note.module_name = :module_id AND note.user_id = :current_user_id
		WHERE i.id = :id AND i.id = c.item_id AND c.active_content = 1', array(
			'module_id'       => self::$module_id,
			'id'              => $id,
			'current_user_id' => AppContext::get_current_user()->get_id()
		));

		$item = new GuideItem();
		$item->set_properties($row);
		return $item;
	}

	public static function get_item_content($item_id)
	{
		$content_items = array();

		$result = self::$db_querier->select('SELECT *
		FROM ' . GuideSetup::$guide_table .' i
		LEFT JOIN ' . GuideSetup::$guide_contents_table . ' c ON c.item_id = i.id
		LEFT JOIN ' . DB_TABLE_MEMBER . ' member ON member.user_id = c.author_user_id
		LEFT JOIN ' . DB_TABLE_AVERAGE_NOTES . ' notes ON notes.id_in_module = i.id AND notes.module_name = :module_id
		LEFT JOIN ' . DB_TABLE_NOTE . ' note ON note.id_in_module = i.id AND note.module_name = :module_id AND note.user_id = :current_user_id
		WHERE c.item_id = :id', array(
			'module_id'       => self::$module_id,
			'id'              => $item_id,
			'current_user_id' => AppContext::get_current_user()->get_id()
		));

		while ($row = $result->fetch()) {
			$content_item = new GuideItemContent();
			$content_item->set_properties($row);
			$content_items[$content_item->get_content_id()] = $content_item;
		}
		$result->dispose();

		return $content_items;
	}

	/**
	 * @desc Return the item with all its properties from its id.
	 * @param int $id Item identifier
	 */
	public static function get_item_archive(int $id, int $content_id)
	{
		$row = self::$db_querier->select_single_row_query('SELECT i.*, c.*, member.*, notes.average_notes, notes.notes_number, note.note
		FROM ' . GuideSetup::$guide_table .' i
		LEFT JOIN ' . GuideSetup::$guide_contents_table . ' c ON c.item_id = i.id
		LEFT JOIN ' . DB_TABLE_MEMBER . ' member ON member.user_id = c.author_user_id
		LEFT JOIN ' . DB_TABLE_AVERAGE_NOTES . ' notes ON notes.id_in_module = i.id AND notes.module_name = :module_id
		LEFT JOIN ' . DB_TABLE_NOTE . ' note ON note.id_in_module = i.id AND note.module_name = :module_id AND note.user_id = :current_user_id
		WHERE i.id = :id AND i.id = c.item_id AND c.content_id = :content_id', array(
			'module_id'       => self::$module_id,
			'id'              => $id,
			'content_id'      => $content_id,
			'current_user_id' => AppContext::get_current_user()->get_id()
		));

		$item = new GuideItem();
		$item->set_properties($row);
		return $item;
	}

    public static function get_initial_content(int $id)
    {
		$result = self::$db_querier->select('SELECT i.*, c.*, member.*, notes.average_notes, notes.notes_number, note.note
		FROM ' . GuideSetup::$guide_table .' i
		LEFT JOIN ' . GuideSetup::$guide_contents_table . ' c ON c.item_id = i.id
		LEFT JOIN ' . DB_TABLE_MEMBER . ' member ON member.user_id = c.author_user_id
		LEFT JOIN ' . DB_TABLE_AVERAGE_NOTES . ' notes ON notes.id_in_module = i.id AND notes.module_name = :module_id
		LEFT JOIN ' . DB_TABLE_NOTE . ' note ON note.id_in_module = i.id AND note.module_name = :module_id AND note.user_id = :current_user_id
		WHERE i.id = :id AND i.id = c.item_id', array(
			'module_id'       => self::$module_id,
			'id'              => $id,
			'current_user_id' => AppContext::get_current_user()->get_id()
		));

        $all_content_ids = array();
		while ($row = $result->fetch())
		{
            $all_content_ids[] = $row['content_id'];
        }

        $row = self::$db_querier->select_single_row_query('SELECT i.*, c.*, member.*, notes.average_notes, notes.notes_number, note.note
		FROM ' . GuideSetup::$guide_table .' i
		LEFT JOIN ' . GuideSetup::$guide_contents_table . ' c ON c.item_id = i.id
		LEFT JOIN ' . DB_TABLE_MEMBER . ' member ON member.user_id = c.author_user_id
		LEFT JOIN ' . DB_TABLE_AVERAGE_NOTES . ' notes ON notes.id_in_module = i.id AND notes.module_name = :module_id
		LEFT JOIN ' . DB_TABLE_NOTE . ' note ON note.id_in_module = i.id AND note.module_name = :module_id AND note.user_id = :current_user_id
		WHERE i.id = :id AND i.id = c.item_id AND c.content_id = :content_id', array(
			'module_id'       => self::$module_id,
			'id'              => $id,
			'content_id'      => min($all_content_ids),
			'current_user_id' => AppContext::get_current_user()->get_id()
		));

		$content_item = new GuideItemContent();
        $content_item->set_properties($row);
		return $content_item;
    }

	/**
	 * @desc track an item.
	 * @param int $item_id id of the item
	 * @param int $user_id id of the user who track the item
	 */
	public static function track_item($item_id, $user_id)
	{
		$result = self::$db_querier->insert(GuideSetup::$guide_track_table, array(
			'track_item_id' => $item_id,
			'track_user_id' => $user_id
		));
        return $result->get_last_inserted_id();
	}

	/**
	 * @desc untrack an item.
	 * @param int $item_id id of the item
	 * @param int $user_id id of the user who untrack the item
	 */
	public static function untrack_item($item_id, $user_id)
	{
		self::$db_querier->delete(GuideSetup::$guide_track_table, 'WHERE track_item_id = :item_id AND track_user_id = :user_id', array(
			'item_id' => $item_id,
			'user_id' => $user_id
		));
	}

	/**
	 * @desc delete all tracked item when delete main item.
	 * @param int $item_id id of the item
	 */
	public static function delete_tracked_item($item_id)
	{
		self::$db_querier->delete(GuideSetup::$guide_track_table, 'WHERE track_item_id = :item_id', array(
			'track_item_id' => $item_id
		));
	}

    public static function get_tracked_items($id)
    {
        $result = self::$db_querier->select('SELECT f.*
            FROM ' . GuideSetup::$guide_track_table . ' f'
        );

        $all_tracked_ids = array();
		while ($row = $result->fetch())
		{
            if($row['track_item_id'] == $id)
                $all_tracked_ids[] = array($row['track_item_id'], $row['track_user_id']);
        }
        return $all_tracked_ids;
    }

	public static function clear_cache()
	{
		Feed::clear_cache('guide');
		KeywordsCache::invalidate();
		GuideCache::invalidate();
        CategoriesService::get_categories_manager()->regenerate_cache();
	}
}
?>
