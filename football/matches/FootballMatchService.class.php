<?php
/**
 * @copyright   &copy; 2005-2022 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2022 12 22
 * @since       PHPBoost 6.0 - 2022 12 22
*/

class FootballMatchService
{
	private static $db_querier;
	protected static $module_id = 'football';

	public static function __static()
	{
		self::$db_querier = PersistenceContext::get_querier();
	}

	/**
	 * @desc Count items number.
	 * @param string $condition (optional) : Restriction to apply to the list of items
	 */
	public static function count_matches($condition = '', $params = array())
	{
		return self::$db_querier->count(FootballSetup::$football_match_table, $condition, $params);
	}

	/**
	 * @desc Create a new entry in the database match table.
	 * @param FootballMatch $match : new FootballMatch
	 */
	public static function add_match(FootballMatch $match)
	{
		$result = self::$db_querier->insert(FootballSetup::$football_match_table, $match->get_properties());

		return $result->get_last_inserted_id();
	}

	/**
	 * @desc Update a match entry.
	 * @param FootballMatch $match : FootballMatch to update
	 */
	public static function update_match(FootballMatch $match, int $id)
	{
		self::$db_querier->update(FootballSetup::$football_match_table, $match->get_properties(), 'WHERE id_match = :id', array('id' => $id));
	}

	/**
	 * @desc Update a match entry.
	 * @param FootballMatch $match : FootballMatch to update
	 */
	public static function update_score(int $id, string $home_score, string $away_score)
	{
		self::$db_querier->update(FootballSetup::$football_match_table, array('match_home_score' => $home_score, 'match_away_score' => $away_score), 'WHERE id_match = :id', array('id' => $id));
	}

	/** Delete a match entry. */
	public static function delete_matches(int $id)
	{
        $matches = self::get_matches($id);
        foreach ($matches as $match)
        {
            self::$db_querier->delete(FootballSetup::$football_match_table, 'WHERE match_compet_id = :id', array('id' => $id));
        }
    }

	/** the match with all its properties from its id and group. */
	public static function get_match(int $id, string $type, string $group, string $order) : FootballMatch
	{
		$row = self::$db_querier->select_single_row_query('SELECT matches.*
		FROM ' . FootballSetup::$football_match_table . ' matches
		WHERE matches.id_match = :id
        AND matches.match_type = :type
        AND matches.match_group = :group
        AND matches.match_order = :order', array(
            'id' => $id,
            'type' => $type,
            'group' => $group,
            'order' => $order
        ));

		$match = new FootballMatch();
		$match->set_properties($row);

		return $match;
	}

    /** Get all matches from a compet */
	public static function get_matches(int $compet_id) : array
	{
		$results = self::$db_querier->select('SELECT matches.*, compet.*
            FROM ' . FootballSetup::$football_match_table . ' matches
            LEFT JOIN ' . FootballSetup::$football_compet_table . ' compet ON compet.id_compet = matches.match_compet_id
            WHERE matches.match_compet_id = :id
            ORDER BY matches.match_group ASC, matches.match_order ASC', array(
                'id' => $compet_id
            )
        );

        $matches = [];
        while($row = $results->fetch())
        {
            $matches[] = $row;
        }
        return $matches;
	}

    public static function has_matches($compet_id)
    {
        return count(self::get_matches($compet_id)) > 0;
    }

	public static function get_match_in_group(int $compet_id, string $type, int $group, int $order)
	{
        $match_numbers = [];
        foreach (FootballMatchService::get_matches($compet_id) as $match)
        {
            $match_numbers[] = $match['match_type'].$match['match_group'].$match['match_order'];
        }

        if (in_array($type.$group.$order, $match_numbers))
        {
            $row = self::$db_querier->select_single_row_query('SELECT matches.*
                FROM ' . FootballSetup::$football_match_table . ' matches
                WHERE matches.match_compet_id = :compet_id
                AND matches.match_type = :type
                AND matches.match_group = :group
                AND matches.match_order = :order', array(
                    'compet_id' => $compet_id,
                    'type' => $type,
                    'group' => $group,
                    'order' => $order,
                )
            );
            $match = new FootballMatch();
            $match->set_properties($row);

            return $match;
        }
        else 
            return null;
	}

    public static function one_day_compet(int $compet_id) : bool
    {
        $matches = self::get_matches($compet_id);

        if(count($matches) > 0)
        {
            $first_day = date('j', $matches[0]['match_date']);
            $first_month = date('n', $matches[0]['match_date']);
            $first_year = date('Y', $matches[0]['match_date']);

            $same_day = true;
            for ($i = 1; $i < count($matches); $i++) {
                $day = date('j', $matches[$i]['match_date']);
                $month = date('n', $matches[$i]['match_date']);
                $year = date('Y', $matches[$i]['match_date']);

                if ($day != $first_day || $month != $first_month || $year != $first_year) {
                    $same_day = false;
                    break;
                }
            }
            return $same_day;
        }
        else
            return false;
    }
}
?>
