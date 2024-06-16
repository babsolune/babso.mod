<?php
/**
 * @copyright   &copy; 2005-2024 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2024 06 12
 * @since       PHPBoost 6.0 - 2024 06 12
*/

class FootballRoundsGroupsController extends DefaultModuleController
{
    private $compet;
	protected function get_template_to_use()
	{
		return new FileTemplate('football/FootballRoundsGroupsController.tpl');
	}

	public function execute(HTTPRequestCustom $request)
	{
		$this->build_view();
		$this->check_authorizations();

        $this->view->put('C_ONE_DAY', FootballMatchService::one_day_compet($this->compet_id()));

		return $this->generate_response();
	}

	private function build_view()
	{
        $round = AppContext::get_request()->get_getint('round', 0);
        if($this->get_params()->get_hat_days())
        {
            $this->view->put_all(array(
                'C_HAT_DAYS' => true,
                'DAY' => $round
            ));
            $days = FootballGroupService::matches_list_from_group($this->compet_id(), 'G');

            $days_matches = FootballGroupService::matches_list_from_group($this->compet_id(), 'G', $round);
            foreach($days_matches as $match)
            {
                $item = new FootballMatch();
                $item->set_properties($match);
                $this->view->assign_block_vars('matches', $item->get_array_tpl_vars());
            }

            $ranks = [];
            foreach ($days as $day => $matches)
            {
                foreach ($matches as $match)
                {
                    $ranks[] = [
                        'team_id' => $match['match_home_id'],
                        'goals_for' => $match['match_home_score'],
                        'goals_against' => $match['match_away_score'],
                    ];
                    $ranks[] = [
                        'team_id' => $match['match_away_id'],
                        'goals_for' => $match['match_away_score'],
                        'goals_against' => $match['match_home_score'],
                    ];
                }
            }

            $teams = [];
            for($i = 0; $i < count($ranks); $i++)
            {
                $points = $played = $win = $draw = $loss = 0;
                if ($ranks[$i]['goals_for'] > $ranks[$i]['goals_against'])
                {
                    $points = $this->get_params()->get_victory_points();
                    $win = $played = 1;
                }
                elseif ($ranks[$i]['goals_for'] != '' && ($ranks[$i]['goals_for'] === $ranks[$i]['goals_against']))
                {
                    $points = $this->get_params()->get_draw_points();
                    $draw = $played = 1;
                }
                elseif (($ranks[$i]['goals_for'] < $ranks[$i]['goals_against']))
                {
                    $points = $this->get_params()->get_loss_points();
                    $loss = $played = 1;
                }
                if ($ranks[$i]['team_id'])
                $teams[] = [
                    'team_id' => $ranks[$i]['team_id'],
                    'points' => $points,
                    'played' => $played,
                    'win' => $win,
                    'draw' => $draw,
                    'loss' => $loss,
                    'goals_for' => (int)$ranks[$i]['goals_for'],
                    'goals_against' => (int)$ranks[$i]['goals_against'],
                    'goal_average' => (int)$ranks[$i]['goals_for'] - (int)$ranks[$i]['goals_against'],
                ];
            }

            $ranks = [];
            foreach ($teams as $team) {
                $team_id = $team['team_id'];

                if (!isset($ranks[$team_id])) {
                    $ranks[$team_id] = $team;
                } else {
                    $ranks[$team_id]['points'] += $team['points'];
                    $ranks[$team_id]['played'] += $team['played'];
                    $ranks[$team_id]['win'] += $team['win'];
                    $ranks[$team_id]['draw'] += $team['draw'];
                    $ranks[$team_id]['loss'] += $team['loss'];
                    $ranks[$team_id]['goals_for'] += $team['goals_for'];
                    $ranks[$team_id]['goals_against'] += $team['goals_against'];
                    $ranks[$team_id]['goal_average'] += $team['goals_for'] - $team['goals_against'];
                }
            }
            $ranks = array_values($ranks);
            usort($ranks, function($a, $b)
            {
                if ($a['points'] == $b['points']) {
                    if ($a['win'] == $b['win']) {
                        if ($a['goal_average'] == $b['goal_average']) {
                            if ($a['goals_for'] == $b['goals_for']) {
                                if ($a['goals_for'] == $b['goals_for']) {
                                    return 0;
                                }
                            }
                            return $b['goals_for'] - $a['goals_for'];
                        }
                        return $b['goal_average'] - $a['goal_average'];
                    }
                    return $b['win'] - $a['win'];
                }
                return $b['points'] - $a['points'];
            });

            $prom = $this->get_params()->get_promotion();
            $playoff = $this->get_params()->get_playoff();
            $releg = $this->get_params()->get_relegation();
            $prom_color = FootballConfig::load()->get_promotion_color();
            $playoff_color = FootballConfig::load()->get_playoff_color();
            $releg_color = FootballConfig::load()->get_relegation_color();
            $color_count = count($ranks);

            foreach ($ranks as $i => $team_rank)
            {
                if ($prom && $i < $prom) {
                    $rank_color = $prom_color;
                } elseif ($playoff && $i >= $prom && $i < ($prom + $playoff)) {
                    $rank_color = $playoff_color;
                } else if ($releg && $i >= $color_count - $releg) {
                    $rank_color = $releg_color;
                } else {
                    $rank_color = 'rgba(0,0,0,0)';
                }
                $this->view->assign_block_vars('ranks', array_merge(
                    Date::get_array_tpl_vars(new Date($match['match_date'], Timezone::SERVER_TIMEZONE), 'match_date'),
                    array(
                        'C_FAV' => FootballParamsService::check_fav($this->compet_id(), $team_rank['team_id']),
                        'RANK' => $i + 1,
                        'RANK_COLOR' => $rank_color,
                        'TEAM_NAME' => !empty($team_rank['team_id']) ? FootballTeamService::get_team_name($team_rank['team_id']) : '',
                        'TEAM_LOGO' => !empty($team_rank['team_id']) ? FootballTeamService::get_team_logo($team_rank['team_id']) : '',
                        'POINTS' => $team_rank['points'],
                        'PLAYED' => $team_rank['played'],
                        'WIN' => $team_rank['win'],
                        'DRAW' => $team_rank['draw'],
                        'LOSS' => $team_rank['loss'],
                        'GOALS_FOR' => $team_rank['goals_for'],
                        'GOALS_AGAINST' => $team_rank['goals_against'],
                        'GOAL_AVERAGE' => $team_rank['goal_average'],
                    )
                ));
            }
        }
        else
        {
            $round_matches = FootballGroupService::matches_list_from_group($this->compet_id(), 'G', $round);

            $this->view->put('GROUP', FootballGroupService::ntl($round));
            $ranks = [];
            foreach ($round_matches as $match)
            {
                $item = new FootballMatch();
                $item->set_properties($match);

                $this->view->assign_block_vars('matches', $item->get_array_tpl_vars());

                $ranks[] = [
                    'team_id' => $match['match_home_id'],
                    'goals_for' => $match['match_home_score'],
                    'goals_against' => $match['match_away_score'],
                ];
                $ranks[] = [
                    'team_id' => $match['match_away_id'],
                    'goals_for' => $match['match_away_score'],
                    'goals_against' => $match['match_home_score'],
                ];
            }

            $teams = [];
            for($i = 0; $i < count($ranks); $i++)
            {
                $points = $played = $win = $draw = $loss = 0;
                if ($ranks[$i]['goals_for'] > $ranks[$i]['goals_against'])
                {
                    $points = $this->get_params()->get_victory_points();
                    $win = $played = 1;
                }
                elseif ($ranks[$i]['goals_for'] != '' && ($ranks[$i]['goals_for'] === $ranks[$i]['goals_against']))
                {
                    $points = $this->get_params()->get_draw_points();
                    $draw = $played = 1;
                }
                elseif (($ranks[$i]['goals_for'] < $ranks[$i]['goals_against']))
                {
                    $points = $this->get_params()->get_loss_points();
                    $loss = $played = 1;
                }
                if ($ranks[$i]['team_id'])
                $teams[] = [
                    'team_id' => $ranks[$i]['team_id'],
                    'points' => $points,
                    'played' => $played,
                    'win' => $win,
                    'draw' => $draw,
                    'loss' => $loss,
                    'goals_for' => (int)$ranks[$i]['goals_for'],
                    'goals_against' => (int)$ranks[$i]['goals_against'],
                    'goal_average' => (int)$ranks[$i]['goals_for'] - (int)$ranks[$i]['goals_against'],
                ];
            }

            $ranks = [];
            foreach ($teams as $team) {
                $team_id = $team['team_id'];

                if (!isset($ranks[$team_id])) {
                    $ranks[$team_id] = $team;
                } else {
                    $ranks[$team_id]['points'] += $team['points'];
                    $ranks[$team_id]['played'] += $team['played'];
                    $ranks[$team_id]['win'] += $team['win'];
                    $ranks[$team_id]['draw'] += $team['draw'];
                    $ranks[$team_id]['loss'] += $team['loss'];
                    $ranks[$team_id]['goals_for'] += $team['goals_for'];
                    $ranks[$team_id]['goals_against'] += $team['goals_against'];
                    $ranks[$team_id]['goal_average'] += $team['goals_for'] - $team['goals_against'];
                }
            }
            $ranks = array_values($ranks);

            usort($ranks, function($a, $b)
            {
                if ($a['points'] == $b['points']) {
                    if ($a['win'] == $b['win']) {
                        if ($a['goal_average'] == $b['goal_average']) {
                            if ($a['goals_for'] == $b['goals_for']) {
                                if ($a['goals_for'] == $b['goals_for']) {
                                    return 0;
                                }
                            }
                            return $b['goals_for'] - $a['goals_for'];
                        }
                        return $b['goal_average'] - $a['goal_average'];
                    }
                    return $b['win'] - $a['win'];
                }
                return $b['points'] - $a['points'];
            });

            $prom = $this->get_params()->get_promotion();
            $playoff = $this->get_params()->get_playoff();
            $releg = $this->get_params()->get_relegation();
            $prom_color = FootballConfig::load()->get_promotion_color();
            $playoff_color = FootballConfig::load()->get_playoff_color();
            $releg_color = FootballConfig::load()->get_relegation_color();
            $color_count = count($ranks);

            foreach ($ranks as $i => $team_rank)
            {
                if ($prom && $i < $prom) {
                    $rank_color = $prom_color;
                } elseif ($playoff && $i >= $prom && $i < ($prom + $playoff)) {
                    $rank_color = $playoff_color;
                } else if ($releg && $i >= $color_count - $releg) {
                    $rank_color = $releg_color;
                } else {
                    $rank_color = 'rgba(0,0,0,0)';
                }
                $this->view->assign_block_vars('ranks', array_merge(
                    Date::get_array_tpl_vars(new Date($match['match_date'], Timezone::SERVER_TIMEZONE), 'match_date'),
                    array(
                        'C_FAV' => FootballParamsService::check_fav($this->compet_id(), $team_rank['team_id']),
                        'RANK' => $i + 1,
                        'RANK_COLOR' => $rank_color,
                        'TEAM_NAME' => !empty($team_rank['team_id']) ? FootballTeamService::get_team_name($team_rank['team_id']) : '',
                        'TEAM_LOGO' => !empty($team_rank['team_id']) ? FootballTeamService::get_team_logo($team_rank['team_id']) : '',
                        'POINTS' => $team_rank['points'],
                        'PLAYED' => $team_rank['played'],
                        'WIN' => $team_rank['win'],
                        'DRAW' => $team_rank['draw'],
                        'LOSS' => $team_rank['loss'],
                        'GOALS_FOR' => $team_rank['goals_for'],
                        'GOALS_AGAINST' => $team_rank['goals_against'],
                        'GOAL_AVERAGE' => $team_rank['goal_average'],
                    )
                ));
            }

        }

        $this->view->put_all(array(
            'MENU' => FootballMenuService::build_compet_menu($this->compet_id()),
            'C_HAS_MATCHES' => FootballMatchService::has_matches($this->compet_id())
        ));
	}

	private function get_compet()
	{
		if ($this->compet === null)
		{
			$id = AppContext::get_request()->get_getint('compet_id', 0);
			if (!empty($id))
			{
				try {
					$this->compet = FootballCompetService::get_compet($id);
				} catch (RowNotFoundException $e) {
					$error_controller = PHPBoostErrors::unexisting_page();
					DispatchManager::redirect($error_controller);
				}
			}
			else
				$this->compet = new FootballCompet();
		}
		return $this->compet;
	}

    private function compet_id()
    {
        return $this->get_compet()->get_id_compet();
    }

    private function get_params()
    {
        return FootballParamsService::get_params($this->compet_id());
    }

	private function check_authorizations()
	{
		$compet = $this->get_compet();

		$current_user = AppContext::get_current_user();
		$not_authorized = !FootballAuthorizationsService::check_authorizations($compet->get_id_category())->moderation() && !FootballAuthorizationsService::check_authorizations($compet->get_id_category())->write() && (!FootballAuthorizationsService::check_authorizations($compet->get_id_category())->contribution() || $compet->get_author_user()->get_id() != $current_user->get_id());

		switch ($compet->get_publishing_state()) {
			case FootballCompet::PUBLISHED:
				if (!FootballAuthorizationsService::check_authorizations($compet->get_id_category())->read())
				{
					$error_controller = PHPBoostErrors::user_not_authorized();
					DispatchManager::redirect($error_controller);
				}
			break;
			case FootballCompet::NOT_PUBLISHED:
				if ($not_authorized || ($current_user->get_id() == User::VISITOR_LEVEL))
				{
					$error_controller = PHPBoostErrors::user_not_authorized();
					DispatchManager::redirect($error_controller);
				}
			break;
			case FootballCompet::DEFERRED_PUBLICATION:
				if (!$compet->is_published() && ($not_authorized || ($current_user->get_id() == User::VISITOR_LEVEL)))
				{
					$error_controller = PHPBoostErrors::user_not_authorized();
					DispatchManager::redirect($error_controller);
				}
			break;
			default:
				$error_controller = PHPBoostErrors::unexisting_page();
				DispatchManager::redirect($error_controller);
			break;
		}
	}

	private function generate_response()
	{
		$compet = $this->get_compet();
		$category = $compet->get_category();
		$response = new SiteDisplayResponse($this->view);

		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($compet->get_compet_name(), ($category->get_id() != Category::ROOT_CATEGORY ? $category->get_name() . ' - ' : '') . $this->lang['football.module.title']);
		$graphical_environment->get_seo_meta_data()->set_description('');
		$graphical_environment->get_seo_meta_data()->set_canonical_url(FootballUrlBuilder::compet_home($compet->get_id_compet()));

		// if ($compet->has_thumbnail())
		// 	$graphical_environment->get_seo_meta_data()->set_picture_url($compet->get_thumbnail());

		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['football.module.title'],FootballUrlBuilder::home());

		$categories = array_reverse(CategoriesService::get_categories_manager()->get_parents($compet->get_id_category(), true));
		foreach ($categories as $id => $category)
		{
			if ($category->get_id() != Category::ROOT_CATEGORY)
				$breadcrumb->add($category->get_name(), FootballUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name()));
		}
		$breadcrumb->add($compet->get_compet_name(), FootballUrlBuilder::compet_home($compet->get_id_compet()));
		$breadcrumb->add($this->lang['football.matches.groups.stage'], FootballUrlBuilder::display_groups_rounds($compet->get_id_compet()));

		return $response;
	}
}
?>