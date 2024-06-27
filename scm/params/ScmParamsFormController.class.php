<?php
/**
 * @copyright   &copy; 2005-2024 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2024 06 12
 * @since       PHPBoost 6.0 - 2024 06 12
*/

class ScmParamsFormController extends DefaultModuleController
{
	private $params;
	private $is_new_params;
	private $event;
	private $division;
	private $is_championship;
	private $is_cup;
	private $is_tournament;
	private $event_type;

	public function execute(HTTPRequestCustom $request)
	{
		$this->init();
		$this->check_authorizations();

		$this->build_form();

		if ($this->submit_button->has_been_submited() && $this->form->validate())
		{
			$this->save();
            $event_name = $this->get_event()->get_event_name();
            $this->view->put('MESSAGE_HELPER', MessageHelper::display(StringVars::replace_vars($this->lang['scm.warning.params.update'], array('event_name' => $event_name)), MessageHelper::SUCCESS, 4));
        }

		$this->view->put_all(array(
            'MENU' => ScmMenuService::build_event_menu($this->event_id()),
            'CONTENT' => $this->form->display()
        ));

		return $this->generate_response($this->view);
	}

	private function init()
	{
		$this->division = ScmDivisionCache::load()->get_division($this->get_event()->get_division_id());
		$this->is_championship = $this->division['event_type'] == ScmDivision::CHAMPIONSHIP;
		$this->is_cup = $this->division['event_type'] == ScmDivision::CUP;
		$this->is_tournament = $this->division['event_type'] == ScmDivision::TOURNAMENT;
	}

	private function build_form()
	{
		$form = new HTMLForm(__CLASS__);
        $form->set_css_class('params-form cell-flex cell-columns-2');
		$form->set_layout_title('<div class="align-center small">' . $this->lang['scm.params.event'] . '</div>');

		if ($this->is_tournament)
		{
            $tournament_fieldset = new FormFieldsetHTML('tournament', $this->lang['scm.params.tournament']);
            $form->add_fieldset($tournament_fieldset);

            $tournament_fieldset->add_field(new FormFieldNumberEditor('groups_number', $this->lang['scm.groups.number'], $this->get_params()->get_groups_number(),
                array('min' => 0, 'required' => true)
            ));
            $tournament_fieldset->add_field(new FormFieldNumberEditor('teams_per_group', $this->lang['scm.teams.per.group'], $this->get_params()->get_teams_per_group(),
                array('min' => 0, 'required' => true)
            ));
            $tournament_fieldset->add_field(new FormFieldCheckbox('hat_ranking', $this->lang['scm.hat.ranking'], $this->get_params()->get_hat_ranking(),
                array(
                    'description' => '<span aria-label="' . $this->lang['scm.hat.ranking.clue'] . '"><i class="far fa-circle-question"></i></span>',
                    'events' => array('click' => '
                    if (HTMLForms.getField("hat_ranking").getValue()) {
                        HTMLForms.getField("hat_days").enable();
                        HTMLForms.getField("fill_games").disable();
                    } else {
                        HTMLForms.getField("hat_days").disable();
                        HTMLForms.getField("fill_games").enable();
                    }
                ')
                )
            ));
            $tournament_fieldset->add_field(new FormFieldNumberEditor('hat_days', $this->lang['scm.hat.days'], $this->get_params()->get_hat_days(),
                array(
                    'description' => $this->lang['scm.hat.days.clue'],
                    'min' => 0, 'required' => true,
                    'hidden' => !$this->get_params()->get_hat_ranking()
                )
            ));
            $tournament_fieldset->add_field(new FormFieldCheckbox('fill_games', $this->lang['scm.fill.games'], $this->get_params()->get_fill_games(),
                array(
                    'description' => '<span aria-label="' . $this->lang['scm.fill.games.clue'] . '"><i class="far fa-circle-question"></i></span>',
                    'hidden' => $this->get_params()->get_hat_ranking()
                )
            ));
            $tournament_fieldset->add_field(new FormFieldCheckbox('looser_bracket', $this->lang['scm.looser.bracket'], $this->get_params()->get_looser_bracket(),
                array('events' => array('click' => '
                    if (HTMLForms.getField("looser_bracket").getValue()) {
                        HTMLForms.getField("third_place").disable();
                    } else {
                        HTMLForms.getField("third_place").enable();
                    }
                '))
            ));
            $tournament_fieldset->add_field(new FormFieldCheckbox('display_playgrounds', $this->lang['scm.display.playgrounds'], $this->get_params()->get_display_playgrounds()));
        }

		if ($this->is_cup || $this->is_tournament)
		{
			$bracket_fieldset = new FormFieldsetHTML('bracket', $this->lang['scm.params.bracket']);
            $form->add_fieldset($bracket_fieldset);

            $bracket_fieldset->add_field(new FormFieldNumberEditor('rounds_number', $this->lang['scm.rounds.number'], $this->get_params()->get_rounds_number(),
                array(
                    'description' => '<span aria-label="' . $this->lang['scm.rounds.number.clue'] . '"><i class="far fa-circle-question"></i></span>', 
                    'min' => 0, 'max' => 7, 'required' => true
                )
            ));

            $bracket_fieldset->add_field(new FormFieldCheckbox('draw_games', $this->lang['scm.draw.games'], $this->get_params()->get_draw_games()));

            $bracket_fieldset->add_field(new FormFieldCheckbox('has_overtime', $this->lang['scm.has.overtime'], $this->get_params()->get_has_overtime(),
				array('events' => array('click' => '
                    if (HTMLForms.getField("has_overtime").getValue()) {
                        HTMLForms.getField("overtime_duration").enable();
                    } else {
                        HTMLForms.getField("overtime_duration").disable();
                    }
                '))
			));
			$bracket_fieldset->add_field(new FormFieldNumberEditor('overtime_duration', $this->lang['scm.overtime.duration'], $this->get_params()->get_overtime_duration(),
				array(
                    'min' => 0,
                    'description' => $this->lang['scm.minutes.clue'],
                    'hidden' => !$this->get_params()->get_has_overtime()
                )
			));
			$bracket_fieldset->add_field(new FormFieldCheckbox('golden_goal', $this->lang['scm.golden.goal'], $this->get_params()->get_golden_goal()));
			$bracket_fieldset->add_field(new FormFieldCheckbox('silver_goal', $this->lang['scm.silver.goal'], $this->get_params()->get_silver_goal()));
			$bracket_fieldset->add_field(new FormFieldCheckbox('third_place', $this->lang['scm.third.place'], $this->get_params()->get_third_place(),
                array('hidden' => $this->is_tournament && $this->get_params()->get_looser_bracket())
            ));
		}

		if ($this->is_championship || $this->is_tournament)
		{
			$ranking_fieldset = new FormFieldsetHTML('ranking', $this->lang['scm.params.ranking']);
            $form->add_fieldset($ranking_fieldset);

            $ranking_fieldset->add_field(new FormFieldNumberEditor('victory_points', $this->lang['scm.victory.points'], $this->get_params()->get_victory_points(), array('min' => 0)));
			$ranking_fieldset->add_field(new FormFieldNumberEditor('draw_points', $this->lang['scm.draw.points'], $this->get_params()->get_draw_points(), array('min' => 0)));
			$ranking_fieldset->add_field(new FormFieldNumberEditor('loss_points', $this->lang['scm.loss.points'], $this->get_params()->get_loss_points(), array('min' => 0)));

			$ranking_fieldset->add_field(new FormFieldNumberEditor('promotion', $this->lang['scm.promotion'], $this->get_params()->get_promotion(), array('min' => 0)));
			$ranking_fieldset->add_field(new FormFieldNumberEditor('playoff', $this->lang['scm.playoff'], $this->get_params()->get_playoff(), array('min' => 0)));
			$ranking_fieldset->add_field(new FormFieldNumberEditor('relegation', $this->lang['scm.relegation'], $this->get_params()->get_relegation(), array('min' => 0)));

			$ranking_fieldset->add_field(new FormFieldSimpleSelectChoice('ranking_type', $this->lang['scm.ranking.type'], $this->get_params()->get_ranking_type(), $this->ranking_mode_list()));
		}

		if ($this->is_championship)
		{
			$penalties_fieldset = new FormFieldsetHTML('penalties', $this->lang['scm.params.penalties']);
            $form->add_fieldset($penalties_fieldset);

            $teams = ScmTeamService::get_teams($this->event_id());

            foreach ($teams as $team)
            {
                if ($this->is_championship)
                    $penalties_fieldset->add_field(new FormFieldNumberEditor('penalties_' . $team['id_team'], $team['club_name'], $team['team_penalty'],
                        array('max' => 0)
                    ));
            }
        }

		$option_fieldset = new FormFieldsetHTML('options', $this->lang['scm.params.options']);
		$form->add_fieldset($option_fieldset);
		$option_fieldset->add_field(new FormFieldNumberEditor('game_duration', $this->lang['scm.game.duration'], $this->get_params()->get_game_duration(),
			array('description' => $this->lang['scm.game.duration.clue'], 'min' => 0)
		));

		$option_fieldset->add_field(new FormFieldSimpleSelectChoice('favorite_team_id', $this->lang['scm.favorite.team'], $this->get_params()->get_favorite_team_id(), $this->fav_teams_list()));

		$this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);
		$form->add_button(new FormButtonReset());

		$this->form = $form;
	}

	private function save()
	{
		$params = $this->get_params();
        $params->set_params_event_id($this->event_id());

        if ($this->is_tournament)
        {
            $params->set_groups_number($this->form->get_value('groups_number'));
            $params->set_teams_per_group($this->form->get_value('teams_per_group'));
            $params->set_hat_ranking($this->form->get_value('hat_ranking'));
            $params->set_hat_days($this->form->get_value('hat_days'));
            $params->set_fill_games($this->form->get_value('fill_games'));
            $params->set_looser_bracket($this->form->get_value('looser_bracket'));
            $params->set_display_playgrounds($this->form->get_value('display_playgrounds'));
        }

        if ($this->is_cup || $this->is_tournament)
        {
            $params->set_third_place($this->form->get_value('third_place'));
            $params->set_rounds_number($this->form->get_value('rounds_number'));
            $params->set_draw_games($this->form->get_value('draw_games'));
            $params->set_has_overtime($this->form->get_value('has_overtime'));
            $params->set_overtime_duration($this->form->get_value('overtime_duration'));
        }

        if ($this->is_championship || $this->is_tournament)
        {
            $params->set_victory_points($this->form->get_value('victory_points'));
            $params->set_draw_points($this->form->get_value('draw_points'));
            $params->set_loss_points($this->form->get_value('loss_points'));

            $params->set_promotion($this->form->get_value('promotion'));
            $params->set_playoff($this->form->get_value('playoff'));
            $params->set_relegation($this->form->get_value('relegation'));
            $params->set_ranking_type($this->form->get_value('ranking_type'));
        }

        if ($this->is_championship)
        {
            $teams = ScmTeamService::get_teams($this->event_id());

            foreach ($teams as $team)
            {
                if ($this->is_championship)
                    ScmTeamService::update_team_penalty($team['id_team'], $this->form->get_value('penalties_' . $team['id_team']));
            }
        }

        $params->set_game_duration($this->form->get_value('game_duration'));
        $params->set_favorite_team_id($this->form->get_value('favorite_team_id')->get_raw_value());

		if ($this->is_new_params)
		{
            $id = ScmParamsService::add_params($params);
            $params->set_id_params($id);
        }
		else
		{
			ScmParamsService::update_params($params);
        }

		ScmEventService::clear_cache();
	}

	private function ranking_mode_list()
	{
		$options = array();
		// $cache = ScmSeasonCache::load();
		// $seasons_list = $cache->get_seasons();

		// $i = 1;
		// foreach($seasons_list as $season)
		// {
		// 	$options[] = new FormFieldSelectChoiceOption($season['season_name'], $season['id_season']);
		// 	$i++;
		// }

		return $options;
	}

	private function fav_teams_list()
	{
		$options = array();

        $options[] = new FormFieldSelectChoiceOption('', 0);
		foreach(ScmTeamService::get_teams($this->event_id()) as $team)
		{
            $options[] = new FormFieldSelectChoiceOption($team['club_name'], $team['id_team']);
		}

		return $options;
	}

	private function get_params()
	{
		if ($this->params === null)
		{
			$id = AppContext::get_request()->get_getint('event_id', 0);
			if (!empty($id))
			{
				try {
					$this->params = ScmParamsService::get_params($id);
				} catch (RowNotFoundException $e) {
					$error_controller = PHPBoostErrors::unexisting_page();
					DispatchManager::redirect($error_controller);
				}
			}
			else
			{
				$this->is_new_params = true;
				$this->params = new ScmParams();
				$this->params->init_default_properties();
			}
		}
		return $this->params;
	}

	private function get_event()
	{
		if ($this->event === null)
		{
			$id = AppContext::get_request()->get_getint('event_id', 0);
            try {
                $this->event = ScmEventService::get_event($id);
            } catch (RowNotFoundException $e) {
                $error_controller = PHPBoostErrors::unexisting_page();
                DispatchManager::redirect($error_controller);
            }
		}
		return $this->event;
	}

    private function event_id()
    {
        return $this->get_event()->get_id();
    }

	private function check_authorizations()
	{
		if (!$this->get_event()->is_authorized_to_manage_events())
        {
            $error_controller = PHPBoostErrors::user_not_authorized();
            DispatchManager::redirect($error_controller);
        }

		if (AppContext::get_current_user()->is_readonly())
		{
			$controller = PHPBoostErrors::user_in_read_only();
			DispatchManager::redirect($controller);
		}
	}

	protected function get_template_string_content()
	{
		return '
            # INCLUDE MESSAGE_HELPER #
            # INCLUDE MENU #
            # INCLUDE CONTENT #
        ';
	}

	private function generate_response(View $view)
	{
		$event = $this->get_event();
		$category = $event->get_category();
		$params = $this->get_params();

		$location_id = $params->get_params_event_id() ? 'param-edit-'. $params->get_params_event_id() : '';

		$response = new SiteDisplayResponse($view, $location_id);
		$graphical_environment = $response->get_graphical_environment();

		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['scm.module.title'], ScmUrlBuilder::home());

		if (!AppContext::get_session()->location_id_already_exists($location_id))
			$graphical_environment->set_location_id($location_id);

		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($event->get_event_name(), ($category->get_id() != Category::ROOT_CATEGORY ? $category->get_name() . ' - ' : '') . $this->lang['scm.module.title']);
		// $graphical_environment->get_seo_meta_data()->set_description($event->get_real_summary());
		$graphical_environment->get_seo_meta_data()->set_canonical_url(ScmUrlBuilder::event_home($event->get_id(), $event->get_event_slug()));

		$categories = array_reverse(CategoriesService::get_categories_manager()->get_parents($event->get_id_category(), true));
		foreach ($categories as $id => $category)
		{
			if ($category->get_id() != Category::ROOT_CATEGORY)
				$breadcrumb->add($category->get_name(), ScmUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name()));
		}
		$breadcrumb->add($event->get_event_name(), ScmUrlBuilder::event_home($event->get_id(), $event->get_event_slug()));
		$breadcrumb->add($this->lang['scm.params.event'], ScmUrlBuilder::edit_params($params->get_params_event_id(), $event->get_event_slug()));


		return $response;
	}
}
?>