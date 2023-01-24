<?php
/**
 * @copyright   &copy; 2005-2022 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2022 11 18
 * @since       PHPBoost 6.0 - 2022 11 18
 */

class GuideCommentsTopic extends CommentsTopic
{
	private $item;

	public function __construct(GuideItem $item = null)
	{
		parent::__construct('guide');
		$this->item = $item;
	}

	public function get_authorizations()
	{
		$authorizations = new CommentsAuthorizations();
		$authorizations->set_authorized_access_module(GuideAuthorizationsService::check_authorizations($this->get_item()->get_id_category())->read());
		return $authorizations;
	}

	public function is_displayed()
	{
		return $this->get_item()->is_published();
	}

	private function get_item()
	{
		if ($this->item === null)
		{
			$this->item = GuideService::get_item($this->get_id_in_module());
		}
		return $this->item;
	}
}
?>