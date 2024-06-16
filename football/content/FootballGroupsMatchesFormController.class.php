<?php
/**
 * @copyright   &copy; 2005-2024 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2024 06 12
 * @since       PHPBoost 6.0 - 2024 06 12
*/

class FootballGroupsMatchesFormController extends DefaultModuleController
{
    private $compet;
    private $params;
    private $hat_ranking;
    private $match;
    private $teams_number;
    private $teams_per_group;
    private $return_matches;

	public function execute(HTTPRequestCustom $request)
	{
        $this->init();
		$this->check_authorizations();

        $this->build_form();

		if ($this->submit_button->has_been_submited() && $this->form->validate())
		{
			$this->save();
            $this->view->put('MESSAGE_HELPER', MessageHelper::display($this->lang['football.warning.matches.update'], MessageHelper::SUCCESS, 4));
		}

		$this->view->put_all(array(
            'MENU' => FootballMenuService::build_compet_menu($this->compet_id()),
            'CONTENT' => $this->form->display(),
            'JS_DOC' => FootballBracketService::get_bracket_js_matches($this->compet_id(), $this->teams_number, $this->teams_per_group),
        ));

		return $this->generate_response($this->view);
	}

    private function init()
    {
        $this->hat_ranking = $this->get_params()->get_hat_ranking();
        $this->teams_number = FootballTeamService::get_teams_number($this->compet_id());
        $this->teams_per_group = $this->get_params()->get_teams_per_group();
        $this->return_matches = FootballCompetService::get_compet_match_type($this->compet_id()) == FootballDivision::RETURN_MATCHES;
    }

	private function build_form()
	{
        $i = AppContext::get_request()->get_getint('round', 0);
		$form = new HTMLForm(__CLASS__);
        $form->set_css_class('floating-submit');
		$form->set_layout_title('<div class="align-center small">' . $this->lang['football.matches.management'] . '</div>');

		$groups_fieldset = new FormFieldsetHTML('groups_bracket', $this->lang['football.matches.groups.stage']);
		$groups_fieldset->set_css_class('grouped-selects');
        $form->add_fieldset($groups_fieldset);

        if ($this->hat_ranking)
        {
            $fieldset = new FormFieldsetHTML('group_' . $i, $this->lang['football.day'] . ' ' . $i);
            $fieldset->set_css_class('grouped-selects');
            $form->add_fieldset($fieldset);
            for ($j = 1; $j <= ($this->teams_number / 2); $j++)
            {
                $match_number = '<strong>G' . $i . $j . '</strong>';
                $match_date = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_date() : new Date();
                $match_playground = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_playground() : '';
                $match_home_id = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_home_id() : 0;
                $match_home_score = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_home_score() : '';
                $match_away_score = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_away_score() : '';
                $match_away_id = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_away_id() : 0;

                $fieldset->add_field(new FormFieldFree('group_match_number_' . $i . $j, '', $match_number,
                    array('class' => 'match-select free-select small text-italic align-right form-G' . $i . $j)
                ));
                $fieldset->add_field(new FormFieldDateTime('group_match_date_' . $i . $j, '', $match_date,
                    array('class' => 'match-select date-select')
                ));
                if($this->get_params()->get_display_playgrounds())
                    $fieldset->add_field(new FormFieldTextEditor('group_match_playground_' . $i . $j, '', $match_playground,
                        array('class' => 'match-select playground', 'placeholder' => $this->lang['football.field'])
                    ));
                else
                    $fieldset->add_field(new FormFieldFree('group_match_playground_' . $i . $j, '', '',
                        array('class' => 'match-select playground')
                    ));
                $fieldset->add_field(new FormFieldSimpleSelectChoice('group_home_team_' . $i . $j, '', $match_home_id,
                    $this->get_teams_list(),
                    array('class' => 'home-team match-select home-select')
                ));
                $fieldset->add_field(new FormFieldTextEditor('group_home_score_' . $i . $j, '', $match_home_score,
                    array('class' => 'home-team match-select home-score', 'pattern' => '[0-9]*')
                ));
                $fieldset->add_field(new FormFieldTextEditor('group_away_score_' . $i . $j, '', $match_away_score,
                    array('class' => 'away-team match-select away-score', 'pattern' => '[0-9]*')
                ));
                $fieldset->add_field(new FormFieldSimpleSelectChoice('group_away_team_' . $i . $j, '', $match_away_id,
                    $this->get_teams_list(),
                    array('class' => 'away-team match-select away-select')
                ));
            }
        }
        else
        {
            $groups_number = $this->teams_per_group ? (int)($this->teams_number / $this->teams_per_group) : 0;
            if ($this->return_matches)
                $matches_number = $this->teams_per_group * ($this->teams_per_group - 1);
            else
                $matches_number = $this->teams_per_group * ($this->teams_per_group - 1) / 2;

            $fieldset = new FormFieldsetHTML('group_' . $i, $this->lang['football.group'] . ' ' . FootballGroupService::ntl($i));
            $fieldset->set_css_class('grouped-selects');
            $form->add_fieldset($fieldset);

            if ($this->return_matches)
                $fieldset->add_field(new FormFieldSpacer('group_first_leg_' . $i, $this->lang['football.first.leg']));
            for ($j = 1; $j <= $matches_number; $j++)
            {
                $match_number = '<strong>G' . $i . $j . '</strong>';
                $match_date = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_date() : new Date();
                $match_playground = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_playground() : '';
                $match_home_id = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_home_id() : 0;
                $match_home_score = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_home_score() : '';
                $match_away_score = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_away_score() : '';
                $match_away_id = $this->get_match('G', $i, $j) ? $this->get_match('G', $i, $j)->get_match_away_id() : 0;

                $fieldset->add_field(new FormFieldFree('group_match_number_' . $i . $j, '', $match_number,
                    array('class' => 'match-select free-select small text-italic align-right form-G' . $i . $j)
                ));
                $fieldset->add_field(new FormFieldDateTime('group_match_date_' . $i . $j, '', $match_date,
                    array('class' => 'match-select date-select')
                ));
                if($this->get_params()->get_display_playgrounds())
                    $fieldset->add_field(new FormFieldTextEditor('group_match_playground_' . $i . $j, '', $match_playground,
                        array('class' => 'match-select playground', 'placeholder' => $this->lang['football.field'])
                    ));
                else
                    $fieldset->add_field(new FormFieldFree('group_match_playground_' . $i . $j, '', '',
                        array('class' => 'match-select playground')
                    ));
                $fieldset->add_field(new FormFieldSimpleSelectChoice('group_home_team_' . $i . $j, '', $match_home_id,
                    $this->get_group_teams_list($i),
                    array('class' => 'home-team match-select home-select')
                ));
                $fieldset->add_field(new FormFieldTextEditor('group_home_score_' . $i . $j, '', $match_home_score,
                    array('class' => 'home-team match-select home-score', 'pattern' => '[0-9]*')
                ));
                $fieldset->add_field(new FormFieldTextEditor('group_away_score_' . $i . $j, '', $match_away_score,
                    array('class' => 'away-team match-select away-score', 'pattern' => '[0-9]*')
                ));
                $fieldset->add_field(new FormFieldSimpleSelectChoice('group_away_team_' . $i . $j, '', $match_away_id,
                    $this->get_group_teams_list($i),
                    array('class' => 'away-team match-select away-select')
                ));
                if ($this->return_matches && $j == $matches_number / 2)
                    $fieldset->add_field(new FormFieldSpacer('group_second_leg_' . $i, '<hr />' . $this->lang['football.second.leg']));
            }
        }

        $this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);

		$this->form = $form;
	}

	private function save()
	{
        $i = AppContext::get_request()->get_getint('round', 0);
        if ($this->hat_ranking)
        {
            for ($j = 1; $j <= ($this->teams_number / 2); $j++)
            {
                $match = $this->get_match('G', $i, $j);
                $match->set_match_compet_id($this->compet_id());
                $match->set_match_type('G');
                $match->set_match_group($i);
                $match->set_match_order($j);
                $match->set_match_date($this->form->get_value('group_match_date_' . $i . $j));
                if($this->get_params()->get_display_playgrounds())
                    $match->set_match_playground($this->form->get_value('group_match_playground_' . $i . $j));
                $match->set_match_home_id((int)$this->form->get_value('group_home_team_' . $i . $j)->get_raw_value());
                $match->set_match_home_score($this->form->get_value('group_home_score_' . $i . $j));
                $match->set_match_away_score($this->form->get_value('group_away_score_' . $i . $j));
                $match->set_match_away_id((int)$this->form->get_value('group_away_team_' . $i . $j)->get_raw_value());

                if ($match->get_id_match() == null)
                {
                    $id = FootballMatchService::add_match($match);
                    $match->set_id_match($id);
                }
                else {
                    FootballMatchService::update_match($match, $match->get_id_match());
                }
            }
        }
        else
        {
            if ($this->return_matches)
                $matches_number = $this->teams_per_group * ($this->teams_per_group - 1);
            else
                $matches_number = $this->teams_per_group * ($this->teams_per_group - 1) / 2;

            for ($j = 1; $j <= $matches_number; $j++)
            {
                $match = $this->get_match('G', $i, $j);
                $match->set_match_compet_id($this->compet_id());
                $match->set_match_type('G');
                $match->set_match_group($i);
                $match->set_match_order($j);
                $match->set_match_date($this->form->get_value('group_match_date_' . $i . $j));
                if($this->get_params()->get_display_playgrounds())
                    $match->set_match_playground($this->form->get_value('group_match_playground_' . $i . $j));
                $match->set_match_home_id((int)$this->form->get_value('group_home_team_' . $i . $j)->get_raw_value());
                $match->set_match_home_score($this->form->get_value('group_home_score_' . $i . $j));
                $match->set_match_away_score($this->form->get_value('group_away_score_' . $i . $j));
                $match->set_match_away_id((int)$this->form->get_value('group_away_team_' . $i . $j)->get_raw_value());

                if ($match->get_id_match() == null)
                {
                    $id = FootballMatchService::add_match($match);
                    $match->set_id_match($id);
                }
                else {
                    FootballMatchService::update_match($match, $match->get_id_match());
                }
            }
        }

		FootballCompetService::clear_cache();
	}

	private function get_match($type, $group, $order)
	{
        $compet_id = $this->compet_id();
        $id = FootballMatchService::get_match($compet_id, $type, $group, $order) ? FootballMatchService::get_match($compet_id, $type, $group, $order)->get_id_match() : null;

        if($id !== null)
        {
            try {
                $this->match = FootballMatchService::get_match($compet_id, $type, $group, $order);
            } catch (RowNotFoundException $e) {
                $error_controller = PHPBoostErrors::unexisting_page();
                DispatchManager::redirect($error_controller);
            }
        }
        else
        {
            $this->match = new FootballMatch();
        }
		return $this->match;
	}

	private function get_compet()
	{
		$id = AppContext::get_request()->get_getint('compet_id', 0);
		try {
            $this->compet = FootballCompetService::get_compet($id);
        } catch (RowNotFoundException $e) {
            $error_controller = PHPBoostErrors::unexisting_page();
            DispatchManager::redirect($error_controller);
        }
		return $this->compet;
	}

    private function compet_id()
    {
        return $this->get_compet()->get_id_compet();
    }

    private function get_group_teams_list($group)
    {
        $teams_list = [];
        foreach (FootballTeamService::get_teams($this->compet_id()) as $team)
        {
            $team_group = $team['team_group'];
            $team_group = $team_group ? TextHelper::substr($team_group, 0, 1) : '';
            if ($team_group == $group)
                $teams_list[] = $team;
        }
        $options = array();

        $clubs = FootballClubCache::load();
        $options[] = new FormFieldSelectChoiceOption('', 0);
		foreach($teams_list as $team)
		{
			$options[] = new FormFieldSelectChoiceOption($clubs->get_club_name($team['team_club_id']), $team['id_team']);
		}

		return $options;
    }

    private function get_teams_list()
    {
        $options = array();

        $clubs = FootballClubCache::load();
        $options[] = new FormFieldSelectChoiceOption('', '');
		foreach(FootballTeamService::get_teams($this->compet_id()) as $team)
		{
			$options[] = new FormFieldSelectChoiceOption($clubs->get_club_name($team['team_club_id']), $team['id_team']);
		}

		return $options;
    }

    private function get_params()
	{
        $id = AppContext::get_request()->get_getint('compet_id', 0);
        if (!empty($id))
        {
            try {
                $this->params = FootballParamsService::get_params($id);
            } catch (RowNotFoundException $e) {
                $error_controller = PHPBoostErrors::unexisting_page();
                DispatchManager::redirect($error_controller);
            }
        }
		return $this->params;
	}

	protected function get_template_string_content()
	{
		return '
            # INCLUDE MESSAGE_HELPER #
            # INCLUDE MENU #
            # INCLUDE CONTENT #
            # INCLUDE JS_DOC #
        ';
	}

	private function check_authorizations()
	{
		if (!$this->get_compet()->is_authorized_to_manage_compets())
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

	private function generate_response(View $view)
	{
		$compet = $this->get_compet();

		// $location_id = $compet->get_id_compet() ? 'football-edit-'. $compet->get_id_compet() : '';

		// $response = new SiteDisplayResponse($view, $location_id);
		$response = new SiteDisplayResponse($view);
		$graphical_environment = $response->get_graphical_environment();

		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['football.module.title'], FootballUrlBuilder::home());

		// if (!AppContext::get_session()->location_id_already_exists($location_id))
        //     $graphical_environment->set_location_id($location_id);

        $graphical_environment->set_page_title($this->lang['football.matches.management'], $this->lang['football.module.title']);
        $graphical_environment->get_seo_meta_data()->set_description($this->lang['football.matches.management']);
        $graphical_environment->get_seo_meta_data()->set_canonical_url(FootballUrlBuilder::edit_groups_matches($compet->get_id_compet()));

        $categories = array_reverse(CategoriesService::get_categories_manager()->get_parents($compet->get_id_category(), true));
        foreach ($categories as $id => $category)
        {
            if ($category->get_id() != Category::ROOT_CATEGORY)
                $breadcrumb->add($category->get_name(), FootballUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name()));
        }
        $category = $compet->get_category();
        $breadcrumb->add($compet->get_compet_name(), FootballUrlBuilder::compet_home($compet->get_id_compet()));
        $breadcrumb->add($this->lang['football.matches.management'], FootballUrlBuilder::edit_groups_matches($compet->get_id_compet()));

		return $response;
	}
}
?>