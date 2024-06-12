<?php
/**
 * @copyright   &copy; 2005-2022 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LATIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2022 12 22
 * @since       PHPBoost 6.0 - 2022 12 22
*/

class FootballDaysMatchesFormController extends DefaultModuleController
{
    private $compet;
    private $params;
    private $match;
    private $teams_number;
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
        ));

		return $this->generate_response($this->view);
	}

    private function init()
    {
        $this->teams_number = FootballTeamService::get_teams_number($this->compet_id());
        $this->return_matches = FootballCompetService::get_compet_match_type($this->compet_id()) == FootballDivision::RETURN_MATCHES;
    }

	private function build_form()
	{
        $i = $id = AppContext::get_request()->get_getint('round', 0);
		$form = new HTMLForm(__CLASS__);
        $form->set_css_class('floating-submit');
		$form->set_layout_title('<div class="align-center small">' . $this->lang['football.matches.management'] . '</div>');

        $fieldset = new FormFieldsetHTML('days', $this->lang['football.day'].' ' . $i);
		$fieldset->set_css_class('grouped-selects');
		$form->add_fieldset($fieldset);

        for($j = 1; $j <= $this->teams_number / 2; $j++)
        {
            $match_number = '<strong>D' . $i . $j . '</strong>';
            $match_date = $this->get_match('D', $i, $j) ? $this->get_match('D', $i, $j)->get_match_date() : new Date();
            $match_playground = $this->get_match('D', $i, $j) ? $this->get_match('D', $i, $j)->get_match_playground() : '';
            $match_home_id = $this->get_match('D', $i, $j) ? $this->get_match('D', $i, $j)->get_match_home_id() : 0;
            $match_home_score = $this->get_match('D', $i, $j) ? $this->get_match('D', $i, $j)->get_match_home_score() : '';
            $match_away_score = $this->get_match('D', $i, $j) ? $this->get_match('D', $i, $j)->get_match_away_score() : '';
            $match_away_id = $this->get_match('D', $i, $j) ? $this->get_match('D', $i, $j)->get_match_away_id() : 0;

            $fieldset->add_field(new FormFieldFree('day_match_number_' . $i . $j, '', $match_number,
                array('class' => 'match-select free-select small text-italic align-right form-D' . $i . $j)
            ));
            $fieldset->add_field(new FormFieldDateTime('day_match_date_' . $i . $j, '', $match_date,
                array('class' => 'match-select date-select')
            ));
            if($this->get_params()->get_display_playgrounds())
                $fieldset->add_field(new FormFieldTextEditor('day_match_playground_' . $i . $j, '', $match_playground,
                    array('class' => 'match-select playground', 'placeholder' => $this->lang['football.field'])
                ));
            else
                $fieldset->add_field(new FormFieldFree('day_match_playground_' . $i . $j, '', '',
                    array('class' => 'match-select playground')
                ));
            $fieldset->add_field(new FormFieldSimpleSelectChoice('day_home_team_' . $i . $j, '', $match_home_id,
                $this->get_teams_list(),
                array('class' => 'home-team match-select home-bracket')
            ));
            $fieldset->add_field(new FormFieldTextEditor('day_home_score_' . $i . $j, '', $match_home_score,
                array('class' => 'home-team match-select home-score', 'pattern' => '[0-9]*')
            ));
            $fieldset->add_field(new FormFieldFree('day_home_empty_' . $i . $j, '', '',
                array('class' => 'match-select home_pen')
            ));
            $fieldset->add_field(new FormFieldFree('day_away_empty_' . $i . $j, '', '',
                array('class' => 'match-select away_pen')
            ));
            $fieldset->add_field(new FormFieldTextEditor('day_away_score_' . $i . $j, '', $match_away_score,
                array('class' => 'away-team match-select away-score', 'pattern' => '[0-9]*')
            ));
            $fieldset->add_field(new FormFieldSimpleSelectChoice('day_away_team_' . $i . $j, '', $match_away_id,
                $this->get_teams_list(),
                array('class' => 'away-team match-select away-bracket')
            ));
        }

        $this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);

		$this->form = $form;
	}

	private function save()
	{
        $i = $id = AppContext::get_request()->get_getint('round', 0);

        for($j = 1; $j <= $this->teams_number / 2; $j++)
        {
            $match = $this->get_match('D', $i, $j);
            $match->set_match_compet_id($this->compet_id());
            $match->set_match_type('D');
            $match->set_match_group($i);
            $match->set_match_order($j);
            $match->set_match_date($this->form->get_value('day_match_date_' . $i . $j));
            if($this->get_params()->get_display_playgrounds())
                $match->set_match_playground($this->form->get_value('day_match_playground_' . $i . $j));
            $match->set_match_home_id((int)$this->form->get_value('day_home_team_' . $i . $j)->get_raw_value());
            $match->set_match_home_score($this->form->get_value('day_home_score_' . $i . $j));
            $match->set_match_away_score($this->form->get_value('day_away_score_' . $i . $j));
            $match->set_match_away_id((int)$this->form->get_value('day_away_team_' . $i . $j)->get_raw_value());

            if ($match->get_id_match() == null)
            {
                $id = FootballMatchService::add_match($match);
                $match->set_id_match($id);
            }
            else {
                FootballMatchService::update_match($match, $match->get_id_match());
            }
            if ($match->get_match_home_score())
                FootballDayService::update_day_played($this->compet_id(), $match->get_match_group(), 1);
        }

		FootballCompetService::clear_cache();
	}

	private function get_match($type, $group, $order)
	{
        $compet_id = $this->compet_id();
        $id = FootballMatchService::get_match($compet_id, $type, $group, $order) ? FootballMatchService::get_match($compet_id, $type, $group, $order)->get_id_match() : null;

        if($id !== null)
            try {
                $this->match = FootballMatchService::get_match($compet_id, $type, $group, $order);
            } catch (RowNotFoundException $e) {
                $error_controller = PHPBoostErrors::unexisting_page();
                DispatchManager::redirect($error_controller);
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

    private function get_teams_list()
    {
        $options = [];
        $options[] = new FormFieldSelectChoiceOption('', 0);
        foreach (FootballTeamService::get_teams($this->compet_id()) as $team)
        {
			$options[] = new FormFieldSelectChoiceOption($team['team_club_name'] ? $team['team_club_name'] : $team['team_club_place'], $team['id_team']);
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
        $graphical_environment->get_seo_meta_data()->set_canonical_url(FootballUrlBuilder::edit_brackets_matches($compet->get_id_compet()));

        $categories = array_reverse(CategoriesService::get_categories_manager()->get_parents($compet->get_id_category(), true));
        foreach ($categories as $id => $category)
        {
            if ($category->get_id() != Category::ROOT_CATEGORY)
                $breadcrumb->add($category->get_name(), FootballUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name()));
        }
        $category = $compet->get_category();
        $breadcrumb->add($compet->get_compet_name(), FootballUrlBuilder::compet_home($compet->get_id_compet()));
        $breadcrumb->add($this->lang['football.matches.management'], FootballUrlBuilder::edit_brackets_matches($compet->get_id_compet()));

		return $response;
	}
}
?>
