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

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.'/components/com_tz_portfolio/helpers/route.php';

jimport('joomla.application.component.model');

JModel::addIncludePath(JPATH_SITE.'/components/com_tz_portfolio/models', 'TZ_PortfolioModel');

abstract class modTZ_PortfolioArticlesFeaturesHelper
{
	public static function getList(&$params)
	{
		// Get an instance of the generic articles model
		$model = JModel::getInstance('Articles', 'TZ_PortfolioModel', array('ignore_request' => true));

		// Set application parameters in model
		$app = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 5));
		$model->setState('filter.published', 1);

		// Access filter
		$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		$model->setState('filter.category_id', $params->get('catid', array()));

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

		// Ordering
		$model->setState('list.ordering', 'a.hits');
		$model->setState('list.direction', 'DESC');

		$items = $model->getItems();

        $model2 = JModel::getInstance('Media','TZ_PortfolioModel');
		foreach ($items as &$item) {
			$item->slug = $item->id.':'.$item->alias;
			$item->catslug = $item->catid.':'.$item->category_alias;

			if ($access || in_array($item->access, $authorised)) {
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(TZ_PortfolioHelperRoute::getArticleRoute($item->slug, $item->catslug));
			} else {
				$item->link = JRoute::_('index.php?option=com_users&view=login');
			}

            if($params -> get('tz_show_title') == '0')
                $item -> title  = '';

            if($params -> get('tz_show_introtext') == '1'){
                if($params -> get('tz_counter')){
                    $text   = strip_tags($item -> introtext);
                    $text   = explode(' ',$text);
                    $text   = array_splice($text,0,$params -> get('tz_counter'));
                    $text   = implode(' ',$text);
                }
                else
                    $text   = $item -> introtext;

                    $item -> text   = $text;
            }

            if($model2 && $params -> get('show_tz_image') == '1'){
                if($image  = $model2 -> getMedia($item -> id)){
                    if($image[0] -> type != 'video'){
                        if(!empty($image[0] -> images)){
                            if($params -> get('tz_image_size','S')){
                                $imageName  = $image[0] -> images;
                                $item -> tz_image   = JURI::root().str_replace('.'.JFile::getExt($imageName)
                                    ,'_'.$params -> get('tz_image_size','S').'.'.JFile::getExt($imageName),$imageName);
                            }
                            $item -> tz_image_title = $image[0] -> imagetitle;
                        }
                    }
                    else{
                        if(!empty($image[0] -> thumb)){
                            if($params -> get('tz_image_size','S')){
                                $imageName  = $image[0] -> thumb;
                                $item -> tz_image   = JURI::root().str_replace('.'.JFile::getExt($imageName)
                                    ,'_'.$params -> get('tz_image_size','S').'.'.JFile::getExt($imageName),$imageName);
                            }
                            $item -> tz_image_title = $image[0] -> imagetitle;
                        }
                    }
                }
            }
		}

		return $items;
	}
}
