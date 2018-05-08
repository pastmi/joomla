<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Content component.
 */
class TZ_PortfolioViewCategory extends JView
{
	function display()
	{
		$app = JFactory::getApplication();

		$doc	= JFactory::getDocument();
		$params = $app->getParams();
		$feedEmail	= (@$app->getCfg('feed_email')) ? $app->getCfg('feed_email') : 'author';
		$siteEmail	= $app->getCfg('mailfrom');
		// Get some data from the model
		JRequest::setVar('limit', $app->getCfg('feed_limit'));
		$category	= $this->get('Category');
		$rows		= $this->get('Items');

		$doc->link  = JRoute::_(TZ_PortfolioHelperRoute::getCategoryRoute($category->id));
        if($params -> get('show_feed_image',1) == 1){
            $model      = JModel::getInstance('Media','TZ_PortfolioModel');
        }
        $blogItemParams = $params;

		foreach ($rows as $row)
		{
			// strip html from feed item title
			$title = $this->escape($row->title);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');



			// Compute the article slug
			$row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

			// url link to article
            $blogItemParams -> merge($row -> params);
            if($blogItemParams -> get('tz_portfolio_redirect') == 'p_article'){
                $link = JRoute::_(TZ_PortfolioHelperRoute::getPortfolioArticleRoute($row->slug, $row->catid));
            }
            else{
                $link = JRoute::_(TZ_PortfolioHelperRoute::getArticleRoute($row->slug, $row->catid));
            }

            // image to article
            $image  = null;
            if($params -> get('show_feed_image',1) == 1){
                $media  = $model -> getMedia($row -> id);
                $size   = $params -> get('feed_image_size','S');
                if(strtolower($media[0] -> type) == 'video'){
                    $image  = $media[0] -> thumb;
                }
                else{
                    $image  = $media[0] -> images;
                }
                $image  = str_replace('.'.JFile::getExt($image),'_'.$size.'.'.JFile::getExt($image),$image);
                $_link  = $link;
                if(!preg_match('/'.JURI::base().'/',$link))
                    $_link  = str_replace(JURI::base(true).'/',JURI::base(),$link);
                $image  = '<a href="'.$_link.'"><img src="'.$image.'" alt="'.$title.'"/></a>';
            }

			// strip html from feed item description text
			// TODO: Only pull fulltext if necessary (actually, just get the necessary fields).
			$description	= ($params->get('feed_summary', 0) ? $row->introtext/*.$row->fulltext*/ : $row->introtext);
			$author			= $row->created_by_alias ? $row->created_by_alias : $row->author;
			@$date			= ($row->created ? date('r', strtotime($row->created)) : '');


			// load individual item creator class
			$item = new JFeedItem();
			$item->title		= $title;
			$item->link			= $link;
			$item->description	= $image.$description;
			$item->date			= $date;
			$item->category		= $row->category;

			$item->author		= $author;
			if ($feedEmail == 'site') {
				$item->authorEmail = $siteEmail;
			}
			else {
				$item->authorEmail = $row->author_email;
			}

			// loads item info into rss array
			$doc->addItem($item);
		}
	}

}
