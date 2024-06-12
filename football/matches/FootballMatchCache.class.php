<?php
/**
 * @copyright   &copy; 2005-2022 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2022 12 22
 * @since       PHPBoost 6.0 - 2022 12 22
*/

class FootballMatchCache implements CacheData
{
	private $matches = array();

	/**
	 * {@inheritdoc}
	 */
	public function synchronize()
	{
		$this->matches = array();

		$result = PersistenceContext::get_querier()->select('SELECT match.*
			FROM ' . FootballSetup::$football_match_table . ' match
			ORDER BY match.id_match DESC'
		);

		while ($row = $result->fetch())
		{
			$this->matches[$row['id_match']] = $row;
		}
		$result->dispose();
	}

	public function get_matches() : array
	{
		return $this->matches;
	}

	public function match_exists(int $id) : bool
	{
		return array_key_exists($id, $this->matches);
	}

	public function get_match(int $id)
	{
		if ($this->match_exists($id))
		{
			return $this->matches[$id];
		}
		return null;
	}

	public function get_matches_number() : int
	{
		return count($this->matches);
	}

	/**
	 * Loads and returns the football cached data.
	 * @return FootballCache The cached data
	 */
	public static function load()
	{
		return CacheManager::load(__CLASS__, 'football', 'matches');
	}

	/**
	 * Invalidates the current football cached data.
	 */
	public static function invalidate()
	{
		CacheManager::invalidate('football', 'matches');
	}
}
?>
