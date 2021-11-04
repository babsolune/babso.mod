<?php
/**
 * @copyright   &copy; 2005-2020 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2021 10 30
 * @since       PHPBoost 6.0 - 2021 10 30
*/

class FluxItem
{
	private $id;
	private $id_category;
	private $title;
	private $rewrited_title;
	private $website_url;
	private $website_xml;
	private $content;

	private $published;

	private $creation_date;
	private $author_user;
	private $views_number;
	private $visits_number;
	private $thumbnail_url;

	const THUMBNAIL_URL = '/templates/__default__/images/default_item_thumbnail.png';

	const NOT_PUBLISHED = 0;
	const PUBLISHED = 1;

	public function get_id()
	{
		return $this->id;
	}

	public function set_id($id)
	{
		$this->id = $id;
	}

	public function get_id_category()
	{
		return $this->id_category;
	}

	public function set_id_category($id_category)
	{
		$this->id_category = $id_category;
	}

	public function get_category()
	{
		return CategoriesService::get_categories_manager()->get_categories_cache()->get_category($this->id_category);
	}

	public function get_title()
	{
		return $this->title;
	}

	public function set_title($title)
	{
		$this->title = $title;
	}

	public function get_rewrited_title()
	{
		return $this->rewrited_title;
	}

	public function set_rewrited_title($rewrited_title)
	{
		$this->rewrited_title = $rewrited_title;
	}

	public function get_website_url()
	{
		if (!$this->website_url instanceof Url)
			return new Url('');

		return $this->website_url;
	}

	public function set_website_url(Url $website_url)
	{
		$this->website_url = $website_url;
	}

	public function get_website_xml()
	{
		if (!$this->website_xml instanceof Url)
			return new Url('');

		return $this->website_xml;
	}

	public function set_website_xml(Url $website_xml)
	{
		$this->website_xml = $website_xml;
	}

	public function get_content()
	{
		return $this->content;
	}

	public function set_content($content)
	{
		$this->content = $content;
	}

	public function get_published()
	{
		return $this->published;
	}

	public function set_published($published)
	{
		$this->published = $published;
	}

	public function is_published()
	{
		$now = new Date();
		return CategoriesAuthorizationsService::check_authorizations($this->id_category)->read() && ($this->get_published() == self::PUBLISHED );
	}

	public function get_status()
	{
		switch ($this->published) {
			case self::PUBLISHED:
				return LangLoader::get_message('common.status.approved', 'common-lang');
			break;
			case self::NOT_PUBLISHED:
				return LangLoader::get_message('common.status.draft', 'common-lang');
			break;
		}
	}

	public function get_creation_date()
	{
		return $this->creation_date;
	}

	public function set_creation_date(Date $creation_date)
	{
		$this->creation_date = $creation_date;
	}

	public function get_author_user()
	{
		return $this->author_user;
	}

	public function set_author_user(User $user)
	{
		$this->author_user = $user;
	}

	public function get_views_number()
	{
		return $this->views_number;
	}

	public function set_views_number($views_number)
	{
		$this->views_number = $views_number;
	}

	public function get_visits_number()
	{
		return $this->visits_number;
	}

	public function set_visits_number($visits_number)
	{
		$this->visits_number = $visits_number;
	}

	public function get_thumbnail()
	{
		if (!$this->thumbnail_url instanceof Url)
			return new Url($this->thumbnail_url == FormFieldThumbnail::DEFAULT_VALUE ? FormFieldThumbnail::get_default_thumbnail_url(self::THUMBNAIL_URL) : $this->thumbnail_url);

		return $this->thumbnail_url;
	}

	public function set_thumbnail($thumbnail)
	{
		$this->thumbnail_url = $thumbnail;
	}

	public function has_thumbnail()
	{
		$thumbnail = ($this->thumbnail_url instanceof Url) ? $this->thumbnail_url->rel() : $this->thumbnail_url;
		return !empty($thumbnail);
	}

	public function is_authorized_to_add()
	{
		return CategoriesAuthorizationsService::check_authorizations($this->id_category)->write() || CategoriesAuthorizationsService::check_authorizations($this->id_category)->contribution();
	}

	public function is_authorized_to_edit()
	{
		return CategoriesAuthorizationsService::check_authorizations($this->id_category)->moderation() || ((CategoriesAuthorizationsService::check_authorizations($this->id_category)->write() || (CategoriesAuthorizationsService::check_authorizations($this->id_category)->contribution() && !$this->is_published())) && $this->get_author_user()->get_id() == AppContext::get_current_user()->get_id() && AppContext::get_current_user()->check_level(User::MEMBER_LEVEL));
	}

	public function is_authorized_to_delete()
	{
		return CategoriesAuthorizationsService::check_authorizations($this->id_category)->moderation() || ((CategoriesAuthorizationsService::check_authorizations($this->id_category)->write() || (CategoriesAuthorizationsService::check_authorizations($this->id_category)->contribution() && !$this->is_published())) && $this->get_author_user()->get_id() == AppContext::get_current_user()->get_id() && AppContext::get_current_user()->check_level(User::MEMBER_LEVEL));
	}

	public function get_properties()
	{
		return array(
			'id' => $this->get_id(),
			'id_category' => $this->get_id_category(),
			'title' => $this->get_title(),
			'rewrited_title' => $this->get_rewrited_title(),
			'website_url' => $this->get_website_url()->absolute(),
			'website_xml' => $this->get_website_xml()->absolute(),
			'content' => $this->get_content(),
			'published' => $this->get_published(),
			'creation_date' => $this->get_creation_date()->get_timestamp(),
			'author_user_id' => $this->get_author_user()->get_id(),
			'views_number' => $this->get_views_number(),
			'visits_number' => $this->get_visits_number(),
			'thumbnail_url' => $this->get_thumbnail()->relative(),
		);
	}

	public function set_properties(array $properties)
	{
		$this->id = $properties['id'];
		$this->id_category = $properties['id_category'];
		$this->title = $properties['title'];
		$this->rewrited_title = $properties['rewrited_title'];
		$this->website_url = new Url($properties['website_url']);
		$this->website_xml = new Url($properties['website_xml']);
		$this->content = $properties['content'];
		$this->published = $properties['published'];
		$this->creation_date = new Date($properties['creation_date'], Timezone::SERVER_TIMEZONE);
		$this->views_number = $properties['views_number'];
		$this->visits_number = $properties['visits_number'];
		$this->thumbnail_url = $properties['thumbnail_url'];

		$user = new User();
		if (!empty($properties['user_id']))
			$user->set_properties($properties);
		else
			$user->init_visitor_user();

		$this->set_author_user($user);
	}

	public function init_default_properties($id_category = Category::ROOT_CATEGORY)
	{
		$this->id_category = $id_category;
		$this->published = self::PUBLISHED;
		$this->author_user = AppContext::get_current_user();
		$this->creation_date = new Date();
		$this->views_number = 0;
		$this->visits_number = 0;
		$this->thumbnail_url = FormFieldThumbnail::DEFAULT_VALUE;
		$this->website_url = new Url('');
		$this->website_xml = new Url('');
	}

	public function get_array_tpl_vars()
	{
		$category = $this->get_category();
		$content = FormatingHelper::second_parse($this->content);
		$user = $this->get_author_user();
		$user_group_color = User::get_group_color($user->get_groups(), $user->get_level(), true);
        $config = FluxConfig::load();

		return array_merge(
			Date::get_array_tpl_vars($this->creation_date, 'date'),
			array(
            'C_NEW_WINDOW'         => $config->get_new_window(true),
            'C_CONTENT'            => !empty($content),
			'C_VISIBLE'            => $this->is_published(),
			'C_CONTROLS'           => $this->is_authorized_to_edit() || $this->is_authorized_to_delete(),
			'C_EDIT'               => $this->is_authorized_to_edit(),
			'C_DELETE'             => $this->is_authorized_to_delete(),
			'C_AUTHOR_GROUP_COLOR' => !empty($user_group_color),
            'C_VISIT'              => !empty($this->website_url->absolute()),
            'C_HAS_RSS'            => !empty($this->website_xml->absolute()),
			'C_HAS_THUMBNAIL'      => $this->has_thumbnail(),

            // Category
			'C_ROOT_CATEGORY'      => $category->get_id() == Category::ROOT_CATEGORY,
			'CATEGORY_ID'          => $category->get_id(),
			'CATEGORY_NAME'        => $category->get_name(),
			'U_EDIT_CATEGORY'      => $category->get_id() == Category::ROOT_CATEGORY ? FluxUrlBuilder::configuration()->rel() : CategoriesUrlBuilder::edit($category->get_id())->rel(),

			// Item
			'ID'                  => $this->id,
			'TITLE'               => $this->title,
			'REWRITED_TITLE'      => $this->rewrited_title,
			'WEBSITE_URL'         => $this->website_url->absolute(),
			'WEBSITE_RSS'         => $this->website_xml->absolute(),
			'CONTENT'             => $content,
			'STATUS'              => $this->get_status(),
			'C_AUTHOR_EXIST'      => $user->get_id() !== User::VISITOR_LEVEL,
			'AUTHOR_DISPLAY_NAME' => $user->get_display_name(),
			'AUTHOR_LEVEL_CLASS'  => UserService::get_level_class($user->get_level()),
			'AUTHOR_GROUP_COLOR'  => $user_group_color,
			'VIEWS_NUMBER'        => $this->views_number,
			'VISITS_NUMBER'       => $this->visits_number,

			'U_SYNDICATION'    => SyndicationUrlBuilder::rss('flux', $this->id_category)->rel(),
			'U_AUTHOR_PROFILE' => UserUrlBuilder::profile($this->get_author_user()->get_id())->rel(),
			'U_ITEM'           => FluxUrlBuilder::display($category->get_id(), $category->get_rewrited_name(), $this->id, $this->rewrited_title)->rel(),
			'U_VISIT'          => FluxUrlBuilder::visit($this->id)->rel(),
			'U_DEADLINK'       => FluxUrlBuilder::dead_link($this->id)->rel(),
			'U_CATEGORY'       => FluxUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name())->rel(),
			'U_EDIT'           => FluxUrlBuilder::edit($this->id)->rel(),
			'U_DELETE'         => FluxUrlBuilder::delete($this->id)->rel(),
			'U_THUMBNAIL'      => $this->get_thumbnail()->rel()
			)
		);

	}
}
?>
