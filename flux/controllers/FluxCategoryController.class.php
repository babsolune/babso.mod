<?php
/**
 * @copyright   &copy; 2005-2021 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2021 10 30
 * @since       PHPBoost 6.0 - 2021 10 30
*/

class FluxCategoryController extends ModuleController
{
	private $lang;
	private $common_lang;
	private $view;
	private $config;

	private $category;

	public function execute(HTTPRequestCustom $request)
	{
		$this->init();

		$this->check_authorizations();

		$this->build_view($request);

		return $this->generate_response();
	}

	private function init()
	{
		$this->lang = LangLoader::get('common', 'flux');
		$this->common_lang = LangLoader::get('common-lang');
		$this->view = new FileTemplate('flux/FluxSeveralItemsController.tpl');
		$this->view->add_lang(array_merge($this->lang, $this->common_lang, LangLoader::get('contribution-lang')));
		$this->config = FluxConfig::load();
	}

	private function build_view(HTTPRequestCustom $request)
	{
		$now = new Date();

		$authorized_categories = CategoriesService::get_authorized_categories($this->get_category()->get_id(), '', 'flux');

		$page = AppContext::get_request()->get_getint('page', 1);
		$subcategories_page = AppContext::get_request()->get_getint('subcategories_page', 1);

		$subcategories = CategoriesService::get_categories_manager('flux')->get_categories_cache()->get_children($this->get_category()->get_id(), CategoriesService::get_authorized_categories($this->get_category()->get_id(),'', 'flux'));
		$subcategories_pagination = $this->get_subcategories_pagination(count($subcategories), $this->config->get_categories_per_page(), $page, $subcategories_page);


		$sub_categories_number = 0;
		foreach ($subcategories as $id => $category)
		{
			$sub_categories_number++;

			if ($sub_categories_number > $subcategories_pagination->get_display_from() && $sub_categories_number <= ($subcategories_pagination->get_display_from() + $subcategories_pagination->get_number_items_per_page()))
			{
				$this->view->assign_block_vars('sub_categories_list', array(
					'C_SEVERAL_ITEMS' => $category->get_elements_number() > 1,

					'CATEGORY_ID'            => $category->get_id(),
					'CATEGORY_NAME'          => $category->get_name(),
					'ITEMS_NUMBER'           => $category->get_elements_number(),

					'U_CATEGORY' => FluxUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name())->rel()
				));
			}
		}

		$condition = 'WHERE id_category = :id_category AND published = 1';

		$parameters = array(
			'authorised_categories' => $authorized_categories,
			'id_category' => $this->get_category()->get_id(),
			'timestamp_now' => $now->get_timestamp()
		);

		$pagination = $this->get_pagination($condition, $parameters, $page, $subcategories_page);

		$result = PersistenceContext::get_querier()->select('SELECT flux.*, member.*
		FROM '. FluxSetup::$flux_table .' flux
		LEFT JOIN '. DB_TABLE_MEMBER .' member ON member.user_id = flux.author_user_id
		' . $condition . '
		ORDER BY flux.title ASC
		LIMIT :number_items_per_page OFFSET :display_from', array_merge($parameters, array(
			'user_id' => AppContext::get_current_user()->get_id(),
			'number_items_per_page' => $pagination->get_number_items_per_page(),
			'display_from' => $pagination->get_display_from()
		)));

		// Get last $rss_number feed items among all files
		$rss_number = $this->config->get_rss_number();
		$char_number = $this->config->get_characters_number_to_cut();
		foreach (glob(PATH_TO_ROOT . '/flux/xml/*.xml') as $filename) {
			$xml = simplexml_load_file($filename);
			$items = array();
			$items['title'] = array();
			$items['link']  = array();
			$items['desc']  = array();
			$items['img']   = array();
			$items['date']  = array();


			foreach($xml->channel->item as $i)
			{
				$items['title'][] = $i->title;
				$items['link'][]  = $i->link;
				$items['desc'][]  = $i->description;
				$items['img'][]   = $i->image;
				$items['date'][]  = $i->pubDate;
			}

			$items_number = $rss_number <= count($items['title']) ? $rss_number : count($items['title']);

			for($i = 0; $i < $items_number ; $i++)
			{
				$item_host = basename(parse_url($items['link'][$i], PHP_URL_HOST));

				$date = strtotime($items['date'][$i]);
				$item_date = strftime('%d/%m/%Y - %Hh%M', $date);
				$desc = @strip_tags(FormatingHelper::second_parse($items['desc'][$i]));
				$cut_desc = TextHelper::cut_string(@strip_tags(FormatingHelper::second_parse($desc), '<br><br/>'), (int)$this->config->get_characters_number_to_cut());
				$item_img = $items['img'][$i];
				$this->view->assign_block_vars('feed_items',array(
					'TITLE'           => $items['title'][$i],
					'U_ITEM'          => $items['link'][$i],
					'ITEM_HOST'       => $item_host,
					'DATE'            => $item_date,
					'SORT_DATE'       => $date,
					'SUMMARY'         => $cut_desc,
					'C_READ_MORE'     => strlen($desc) > $char_number,
					'WORDS_NUMBER'    => str_word_count($desc) - str_word_count($cut_desc),
					'C_HAS_THUMBNAIL' => !empty($item_img),
					'U_THUMBNAIL'     => $item_img,
				));
			}
		}

		$this->view->put_all(array(
			'C_CATEGORY'                 => true,
			'C_ITEMS'                    => $result->get_rows_count() > 0,
            'C_SEVERAL_ITEMS'            => $result->get_rows_count() > 1,
			'C_GRID_VIEW'                => $this->config->get_display_type() == FluxConfig::GRID_VIEW,
			'C_TABLE_VIEW'               => $this->config->get_display_type() == FluxConfig::TABLE_VIEW,
			'C_CATEGORY_DESCRIPTION'     => !empty($category_description),
			'C_CONTROLS'                 => CategoriesAuthorizationsService::check_authorizations($this->get_category()->get_id())->moderation(),
			'C_PAGINATION'               => $pagination->has_several_pages(),
			'C_ROOT_CATEGORY'            => $this->get_category()->get_id() == Category::ROOT_CATEGORY,
			'C_HIDE_NO_ITEM_MESSAGE'     => $this->get_category()->get_id() == Category::ROOT_CATEGORY && ($sub_categories_number != 0 || !empty($category_description)),
			'C_SUB_CATEGORIES'           => $sub_categories_number > 0,
			'C_SUBCATEGORIES_PAGINATION' => $subcategories_pagination->has_several_pages(),

			'MODULE_NAME'              => $this->config->get_module_name(),
			'ITEMS_NUMBER'			   => $this->config->get_rss_number(),
			'ROOT_CATEGORY_DESC'       => $this->config->get_root_category_description(),
			'CATEGORY_NAME'            => $this->get_category()->get_name(),
			'SUBCATEGORIES_PAGINATION' => $subcategories_pagination->display(),
			'PAGINATION'               => $pagination->display(),
			'CATEGORIES_PER_ROW'       => $this->config->get_categories_per_row(),
			'ITEMS_PER_ROW'            => $this->config->get_items_per_row(),
			'ID_CAT'                   => $this->get_category()->get_id(),

			'U_EDIT_CATEGORY' => $this->get_category()->get_id() == Category::ROOT_CATEGORY ? FluxUrlBuilder::configuration()->rel() : CategoriesUrlBuilder::edit($this->get_category()->get_id())->rel()
		));

		while ($row = $result->fetch())
		{
			$item = new FluxItem();
			$item->set_properties($row);
			$this->view->assign_block_vars('items', array_merge($item->get_array_tpl_vars()));
		}
		$result->dispose();
	}

	private function get_pagination($condition, $parameters, $page, $subcategories_page)
	{
		$items_number = FluxService::count($condition, $parameters);

		$pagination = new ModulePagination($page, $items_number, (int)FluxConfig::load()->get_items_per_page());
		$pagination->set_url(FluxUrlBuilder::display_category($this->get_category()->get_id(), $this->get_category()->get_rewrited_name(), '%d', $subcategories_page));

		if ($pagination->current_page_is_empty() && $page > 1)
		{
			$error_controller = PHPBoostErrors::unexisting_page();
			DispatchManager::redirect($error_controller);
		}

		return $pagination;
	}

	private function get_subcategories_pagination($subcategories_number, $categories_per_page, $page, $subcategories_page)
	{
		$pagination = new ModulePagination($subcategories_page, $subcategories_number, (int)$categories_per_page);
		$pagination->set_url(FluxUrlBuilder::display_category($this->get_category()->get_id(), $this->get_category()->get_rewrited_name(), $page, '%d'));

		if ($pagination->current_page_is_empty() && $subcategories_page > 1)
		{
			$error_controller = PHPBoostErrors::unexisting_page();
			DispatchManager::redirect($error_controller);
		}

		return $pagination;
	}

	private function get_category()
	{
		if ($this->category === null)
		{
			$id = AppContext::get_request()->get_getint('id_category', 0);
			if (!empty($id))
			{
				try {
					$this->category = CategoriesService::get_categories_manager('flux')->get_categories_cache()->get_category($id);
				} catch (CategoryNotFoundException $e) {
					$error_controller = PHPBoostErrors::unexisting_page();
   					DispatchManager::redirect($error_controller);
				}
			}
			else
			{
				$this->category = CategoriesService::get_categories_manager('flux')->get_categories_cache()->get_category(Category::ROOT_CATEGORY);
			}
		}
		return $this->category;
	}

	private function check_authorizations()
	{
		if (AppContext::get_current_user()->is_guest())
		{
			if (!Authorizations::check_auth(RANK_TYPE, User::MEMBER_LEVEL, $this->get_category()->get_authorizations(), Category::READ_AUTHORIZATIONS) || !CategoriesAuthorizationsService::check_authorizations($this->get_category()->get_id())->read())
			{
				$error_controller = PHPBoostErrors::user_not_authorized();
				DispatchManager::redirect($error_controller);
			}
		}
		else
		{
			if (!CategoriesAuthorizationsService::check_authorizations($this->get_category()->get_id())->read())
			{
				$error_controller = PHPBoostErrors::user_not_authorized();
				DispatchManager::redirect($error_controller);
			}
		}
	}

	private function generate_response()
	{
		$response = new SiteDisplayResponse($this->view);

		$graphical_environment = $response->get_graphical_environment();

		if ($this->get_category()->get_id() != Category::ROOT_CATEGORY)
			$graphical_environment->set_page_title($this->get_category()->get_name(), $this->config->get_module_name());
		else
			$graphical_environment->set_page_title($this->config->get_module_name());

		// $graphical_environment->get_seo_meta_data()->set_description($this->get_category()->get_description());
		$graphical_environment->get_seo_meta_data()->set_canonical_url(FluxUrlBuilder::display_category($this->get_category()->get_id(), $this->get_category()->get_rewrited_name(), AppContext::get_request()->get_getint('page', 1)));

		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->config->get_module_name(), FluxUrlBuilder::home());

		$categories = array_reverse(CategoriesService::get_categories_manager('flux')->get_parents($this->get_category()->get_id(), true));
		foreach ($categories as $id => $category)
		{
			if ($category->get_id() != Category::ROOT_CATEGORY)
				$breadcrumb->add($category->get_name(), FluxUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name()));
		}

		return $response;
	}

	public static function get_view()
	{
		$object = new self();
		$object->init();
		$object->check_authorizations();
		$object->build_view(AppContext::get_request());
		return $object->view;
	}
}
?>