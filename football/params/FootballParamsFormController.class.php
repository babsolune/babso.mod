<?php
/**
 * @copyright   &copy; 2005-2022 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2022 12 27
 * @since       PHPBoost 6.0 - 2022 12 27
*/

class FootballParamsFormController extends DefaultModuleController
{
	private $params;
	private $is_new_params;
	private $compet;
	private $division;
	private $is_championship;
	private $is_cup;
	private $is_tournament;
	private $compet_type;

	public function execute(HTTPRequestCustom $request)
	{
		$this->init();
		$this->check_authorizations();

		$this->build_form($request);

		if ($this->submit_button->has_been_submited() && $this->form->validate())
		{
			$this->save();
			$this->redirect($request);
		}

		$this->view->put_all(array(
            'MENU' => FootballCompetMenuService::build_compet_menu($this->id_compet()),
            'CONTENT' => $this->form->display()
        ));

		return $this->generate_response($this->view);
	}

	private function init()
	{
		$this->division = FootballDivisionCache::load()->get_division($this->get_compet()->get_compet_division_id());
		$this->is_championship = $this->division['division_compet_type'] == FootballDivision::CHAMPIONSHIP;
		$this->is_cup = $this->division['division_compet_type'] == FootballDivision::CUP;
		$this->is_tournament = $this->division['division_compet_type'] == FootballDivision::TOURNAMENT;
	}

	private function build_form(HTTPRequestCustom $request)
	{
		$form = new HTMLForm(__CLASS__);
        $form->set_css_class('params-form');
		$form->set_layout_title('<div class="align-center small">' . $this->lang['football.params'] . '</div>');

		$fieldset = new FormFieldsetHTML('compet', '');
		$form->add_fieldset($fieldset);

		if ($this->is_tournament)
		{
            $fieldset->add_field(new FormFieldNumberEditor('teams_per_group', $this->lang['football.group.teams.number'], $this->get_params()->get_teams_per_group(), array('required' => true)));
        }

		if ($this->is_championship || $this->is_tournament)
		{
			$fieldset->add_field(new FormFieldNumberEditor('victory_points', $this->lang['football.victory.points'], $this->get_params()->get_victory_points(), array('required' => true)));
			$fieldset->add_field(new FormFieldNumberEditor('draw_points', $this->lang['football.draw.points'], $this->get_params()->get_draw_points(), array('required' => true)));
			$fieldset->add_field(new FormFieldNumberEditor('loss_points', $this->lang['football.loss.points'], $this->get_params()->get_loss_points(), array('required' => true)));

			$fieldset->add_field(new FormFieldNumberEditor('promotion', $this->lang['football.promotion'], $this->get_params()->get_promotion()));
			$fieldset->add_field(new FormFieldColorPicker('promotion_color', $this->lang['football.promotion.color'], $this->get_params()->get_promotion_color()));
			$fieldset->add_field(new FormFieldNumberEditor('play_off', $this->lang['football.play.off'], $this->get_params()->get_play_off()));
			$fieldset->add_field(new FormFieldColorPicker('play_off_color', $this->lang['football.play.off.color'], $this->get_params()->get_play_off_color()));
			$fieldset->add_field(new FormFieldNumberEditor('relegation', $this->lang['football.relegation'], $this->get_params()->get_relegation()));
			$fieldset->add_field(new FormFieldColorPicker('relegation_color', $this->lang['football.relegation.color'], $this->get_params()->get_relegation_color()));

			$fieldset->add_field(new FormFieldSimpleSelectChoice('ranking_type', $this->lang['football.ranking.type'], $this->get_params()->get_ranking_type(), $this->ranking_list()));
		}
		else
		{
			$fieldset->add_field(new FormFieldNumberEditor('rounds_number', $this->lang['football.rounds.number'], $this->get_params()->get_rounds_number()));
			$fieldset->add_field(new FormFieldNumberEditor('overtime', $this->lang['football.overtime'], $this->get_params()->get_overtime(),
				array('description' => $this->lang['football.minutes.clue'])
			));
			$fieldset->add_field(new FormFieldCheckbox('golden_goal', $this->lang['football.golden.goal'], $this->get_params()->get_golden_goal()));
			$fieldset->add_field(new FormFieldCheckbox('silver_goal', $this->lang['football.silver.goal'], $this->get_params()->get_silver_goal()));
			$fieldset->add_field(new FormFieldCheckbox('third_place', $this->lang['football.third.place'], $this->get_params()->get_third_place()));
		}

		$fieldset->add_field(new FormFieldNumberEditor('match_duration', $this->lang['football.match.duration'], $this->get_params()->get_match_duration(),
			array('description' => $this->lang['football.minutes.clue'])
		));

		$fieldset->add_field(new FormFieldCheckbox('set_mode', $this->lang['football.set.mode'], $this->get_params()->get_set_mode(),
			array(
				'events' => array('click' => '
					if (HTMLForms.getField("set_mode").getValue()) {
						HTMLForms.getField("sets_number").enable();
					} else {
						HTMLForms.getField("sets_number").disable();
					}'
				)
			)
		));

		$fieldset->add_field(new FormFieldNumberEditor('sets_number', $this->lang['football.sets.number'], $this->get_params()->get_sets_number(),
			array('hidden' => !$this->get_params()->get_set_mode())
		));

		$fieldset->add_field(new FormFieldCheckbox('bonus', $this->lang['football.bonus'], $this->get_params()->get_set_mode(),
			array('description' => $this->lang['football.bonus.clue'])
		));

		$fieldset->add_field(new FormFieldSimpleSelectChoice('favorite_team_id', $this->lang['football.favorite.team'], $this->get_params()->get_favorite_team_id(), $this->teams_list()));

		$fieldset->add_field(new FormFieldCheckbox('is_sub_compet', $this->lang['football.is.sub'], $this->get_params()->get_is_sub_compet(),
			array(
				'events' => array('click' => '
					if (HTMLForms.getField("is_sub_compet").getValue()) {
						HTMLForms.getField("compet_master").enable();
						HTMLForms.getField("sub_compet_rank").enable();
					} else {
						HTMLForms.getField("compet_master").disable();
						HTMLForms.getField("sub_compet_rank").disable();
					}'
				)
			)
		));

		$fieldset->add_field(new FormFieldSimpleSelectChoice('compet_master', $this->lang['football.master'], $this->get_params()->get_compet_master_id(), $this->teams_list(),
			array('hidden' => !$this->get_params()->get_is_sub_compet())
		));

		$fieldset->add_field(new FormFieldNumberEditor('sub_compet_rank', $this->lang['football.sub.rank'], $this->get_params()->get_sub_compet_rank(),
			array('hidden' => !$this->get_params()->get_is_sub_compet())
		));


		$fieldset->add_field(new FormFieldHidden('referrer', $request->get_url_referrer()));

		$this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);
		$form->add_button(new FormButtonReset());

		$this->form = $form;
	}

	private function save()
	{
		$params = $this->get_params();
        $params->set_params_compet_id($this->id_compet());

        if ($this->is_tournament)
        {
            $params->set_teams_per_group($this->form->get_value('teams_per_group'));
        }

        if ($this->is_championship || $this->is_tournament)
        {
            $params->set_victory_points($this->form->get_value('victory_points'));
            $params->set_draw_points($this->form->get_value('draw_points'));
            $params->set_loss_points($this->form->get_value('loss_points'));

            $params->set_promotion($this->form->get_value('promotion'));
            $params->set_promotion_color($this->form->get_value('promotion_color'));
            $params->set_play_off($this->form->get_value('play_off'));
            $params->set_play_off_color($this->form->get_value('play_off_color'));
            $params->set_relegation($this->form->get_value('relegation'));
            $params->set_relegation_color($this->form->get_value('relegation_color'));
            $params->set_ranking_type($this->form->get_value('ranking_type'));
            $params->set_favorite_team_id($this->form->get_value('favorite_team_id')->get_raw_value());
        }
        else
        {

        }

        $params->set_match_duration($this->form->get_value('match_duration'));

		if ($this->is_new_params)
		{
			// $id = AppContext::get_request()->get_getint('id', 0);
            $id = FootballParamsService::add_params($params);
                $params->set_id_params($id);
        }
		else
		{
			FootballParamsService::update_params($params);
        }

		FootballCompetService::clear_cache();
	}

	private function ranking_list()
	{
		$options = array();
		// $cache = FootballSeasonCache::load();
		// $seasons_list = $cache->get_seasons();

		// $i = 1;
		// foreach($seasons_list as $season)
		// {
		// 	$options[] = new FormFieldSelectChoiceOption($season['season_name'], $season['id_season']);
		// 	$i++;
		// }

		return $options;
	}

	private function teams_list()
	{
		$options = array();

        $options[] = new FormFieldSelectChoiceOption('', 0);
		foreach(FootballTeamService::get_teams($this->id_compet()) as $team)
		{
			$options[] = new FormFieldSelectChoiceOption($team['team_club_name'], $team['id_team']);
		}

		return $options;
	}

	private function get_params()
	{
		if ($this->params === null)
		{
			$id = AppContext::get_request()->get_getint('id', 0);
			if (!empty($id))
			{
				try {
					$this->params = FootballParamsService::get_params($id);
				} catch (RowNotFoundException $e) {
					$error_controller = PHPBoostErrors::unexisting_page();
					DispatchManager::redirect($error_controller);
				}
			}
			else
			{
				$this->is_new_params = true;
				$this->params = new FootballParams();
				$this->params->init_default_properties();
			}
		}
		return $this->params;
	}

	private function get_compet()
	{
		if ($this->compet === null)
		{
			$id = AppContext::get_request()->get_getint('id', 0);
            try {
                $this->compet = FootballCompetService::get_compet($id);
            } catch (RowNotFoundException $e) {
                $error_controller = PHPBoostErrors::unexisting_page();
                DispatchManager::redirect($error_controller);
            }
		}
		return $this->compet;
	}

    private function id_compet()
    {
        return $this->get_compet()->get_id_compet();
    }

	private function check_authorizations()
	{
		$compet = $this->get_compet();

		if ($compet->get_id_compet() === null)
		{
			if (!$compet->is_authorized_to_set_up())
			{
				$error_controller = PHPBoostErrors::user_not_authorized();
				DispatchManager::redirect($error_controller);
			}
		}
		else
		{
			if (!$compet->is_authorized_to_set_up())
			{
				$error_controller = PHPBoostErrors::user_not_authorized();
				DispatchManager::redirect($error_controller);
			}
		}
		if (AppContext::get_current_user()->is_readonly())
		{
			$controller = PHPBoostErrors::user_in_read_only();
			DispatchManager::redirect($controller);
		}
	}

	private function redirect()
	{
		AppContext::get_response()->redirect(FootballUrlBuilder::params($this->id_compet()));
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
		$compet = $this->get_compet();
		$category = $compet->get_category();
		$params = $this->get_params();

		$location_id = $params->get_params_compet_id() ? 'param-edit-'. $params->get_params_compet_id() : '';

		$response = new SiteDisplayResponse($view, $location_id);
		$graphical_environment = $response->get_graphical_environment();

		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['football.module.title'], FootballUrlBuilder::home());

		if (!AppContext::get_session()->location_id_already_exists($location_id))
			$graphical_environment->set_location_id($location_id);

		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($compet->get_compet_name(), ($category->get_id() != Category::ROOT_CATEGORY ? $category->get_name() . ' - ' : '') . $this->lang['football.module.title']);
		// $graphical_environment->get_seo_meta_data()->set_description($compet->get_real_summary());
		$graphical_environment->get_seo_meta_data()->set_canonical_url(FootballUrlBuilder::display($category->get_id(), $category->get_rewrited_name(), $compet->get_id_compet(), $compet->get_compet_slug()));

		$categories = array_reverse(CategoriesService::get_categories_manager()->get_parents($compet->get_id_category(), true));
		foreach ($categories as $id => $category)
		{
			if ($category->get_id() != Category::ROOT_CATEGORY)
				$breadcrumb->add($category->get_name(), FootballUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name()));
		}
		$breadcrumb->add($compet->get_compet_name(), FootballUrlBuilder::display($category->get_id(), $category->get_rewrited_name(), $compet->get_id_compet(), $compet->get_compet_slug()));
		$breadcrumb->add($this->lang['football.params'], FootballUrlBuilder::params($params->get_params_compet_id()));


		return $response;
	}
}
?>
