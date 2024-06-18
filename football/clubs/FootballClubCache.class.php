<?php
/**
 * @copyright   &copy; 2005-2024 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2024 06 12
 * @since       PHPBoost 6.0 - 2024 06 12
*/

class FootballClubCache implements CacheData
{
	private $clubs = array();

	/**
	 * {@inheritdoc}
	 */
	public function synchronize()
	{
		$this->clubs = array();

		$result = PersistenceContext::get_querier()->select('SELECT club.*
			FROM ' . FootballSetup::$football_club_table . ' club
			ORDER BY club.club_name ASC'
		);

		while ($row = $result->fetch())
		{
			$this->clubs[$row['id_club']] = $row;
		}
		$result->dispose();
	}

	public function get_clubs()
	{
		return $this->clubs;
	}

	public function club_exists($id)
	{
		return array_key_exists($id, $this->clubs);
	}

	public function get_club($id)
	{
		if ($this->club_exists($id))
		{
			return $this->clubs[$id];
		}
		return null;
	}

	public function get_club_name($id)
	{
		if ($this->club_exists($id))
		{
			return $this->get_club($id)['club_name'];
		}
		return null;
	}

	public function get_club_full_name($id)
	{
		if ($this->club_exists($id))
		{
			return $this->get_club($id)['club_full_name'] ? $this->get_club($id)['club_full_name'] : $this->get_club($id)['club_name'];
		}
		return null;
	}

	public function get_club_logo($id)
	{
		if ($this->club_exists($id))
		{
			return $this->get_club($id)['club_logo'];
		}
		return null;
	}

	public function get_clubs_number()
	{
		return count($this->clubs);
	}

	/**
	 * Loads and returns the football cached data.
	 * @return FootballCache The cached data
	 */
	public static function load()
	{
		return CacheManager::load(__CLASS__, 'football', 'clubs');
	}

	/**
	 * Invalidates the current football cached data.
	 */
	public static function invalidate()
	{
		CacheManager::invalidate('football', 'clubs');
	}
}
?>
