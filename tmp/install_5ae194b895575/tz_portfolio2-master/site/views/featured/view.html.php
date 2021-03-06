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
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'libraries'.DS.'HTTPFetcher.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'libraries'.DS.'readfile.php');

/**
 * Frontpage View class.
 */
class TZ_PortfolioViewFeatured extends JView
{
	protected $state = null;
	protected $item = null;
	protected $items = null;
	protected $pagination = null;

	protected $lead_items = array();
	protected $intro_items = array();
	protected $link_items = array();
	protected $columns = 1;

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{

		// Initialise variables.
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
        $doc    = JFactory::getDocument();
        $doc -> addScript(JURI::root()."components/com_tz_portfolio/js/jquery-1.7.2.min.js");

		$state 		= $this->get('State');
		$items 		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		$params = &$state->params;

		// PREPARE THE DATA

		// Get the metrics for the structural page layout.
		$numLeading = $params->def('num_leading_articles', 1);
		$numIntro = $params->def('num_intro_articles', 4);
		$numLinks = $params->def('num_links', 4);

		// Compute the article slugs and prepare introtext (runs content plugins).
		foreach ($items as $i => & $item)
		{
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

            $tzRedirect = $params -> get('tz_portfolio_redirect','p_article'); //Set params for $tzRedirect
            $itemParams = new JRegistry($item -> attribs); //Get Article's Params
            //Check redirect to view article
            if($itemParams -> get('tz_portfolio_redirect')){
                $tzRedirect = $itemParams -> get('tz_portfolio_redirect');
            }

            if($tzRedirect == 'p_article'){
                $contentUrl =JRoute::_(TZ_PortfolioHelperRoute::getPortfolioArticleRoute($item -> slug,$item -> catid), true ,-1);
            }
            else{
                $contentUrl =JRoute::_(TZ_PortfolioHelperRoute::getArticleRoute($item -> slug,$item -> catid), true ,-1);
            }

            if($params -> get('tz_comment_type','disqus') == 'facebook'){
                if($params -> get('tz_show_count_comment',1) == 1){

                    $url    = 'http://graph.facebook.com/?ids='.$contentUrl;

                    $content    = $fetch -> get($url);

                    if($content)
                        $content    = json_decode($content -> body);

                    if(isset($content -> $contentUrl -> comments))
                        $item -> commentCount   = $content -> $contentUrl  -> comments;
                    else
                        $item -> commentCount   = 0;
                }
            }
            if($params -> get('tz_comment_type','disqus') == 'disqus'){
                if($params -> get('tz_show_count_comment',1) == 1){

                    $url        = 'https://disqus.com/api/3.0/threads/listPosts.json?api_secret='.$params -> get('disqusApiSecretKey')
								  .'&forum='.$params -> get('disqusSubDomain','templazatoturials')
								  .'&thread=link:'.$contentUrl
								  .'&include=approved';

					$content    = $fetch -> get($url);

					if($content)
						$content    = json_decode($content -> body);
					$content    = $content -> response;
					if(is_array($content)){
						$item -> commentCount	= count($content);
					}
					else{
						$item -> commentCount   = 0;
					}
                }
            }
            
            $model  = JModel::getInstance('Category','TZ_PortfolioModel', array('ignore_request' => true));
            $model -> setState('category.id',$item -> catid);
            $category   = $model -> getCategory();

            $catParams2 = new JRegistry();

            $catParams  = new JRegistry();
            if($category)
                $catParams -> loadString($category -> params);
            $catParams  = $catParams -> toArray();

            if(count($catParams)>0){
                foreach($catParams as $key => $val){
                    if(preg_match('/.*?article.*?/',$key)){
                        $catParams2 -> set($key,$val);
                    }
                }
            }


			$item->catslug = ($item->category_alias) ? ($item->catid . ':' . $item->category_alias) : $item->catid;
			$item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;
			// No link for ROOT category
			if ($item->parent_alias == 'root') {
				$item->parent_slug = null;
			}

			$item->event = new stdClass();

			$dispatcher = JDispatcher::getInstance();

			// Ignore content plugins on links.
			if ($i < $numLeading + $numIntro)
			{
				$item->introtext = JHtml::_('content.prepare', $item->introtext, '', 'com_tz_portfolio.featured');

				$results = $dispatcher->trigger('onContentAfterTitle', array('com_tz_portfolio.article', &$item, &$item->params, 0));
				$item->event->afterDisplayTitle = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_tz_portfolio.article', &$item, &$item->params, 0));
				$item->event->beforeDisplayContent = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onContentAfterDisplay', array('com_tz_portfolio.article', &$item, &$item->params, 0));
				$item->event->afterDisplayContent = trim(implode("\n", $results));
			}
		}

		// Preprocess the breakdown of leading, intro and linked articles.
		// This makes it much easier for the designer to just interogate the arrays.
		$max = count($items);

		// The first group is the leading articles.
		$limit = $numLeading;
		for ($i = 0; $i < $limit && $i < $max; $i++)
		{
			$this->lead_items[$i] = &$items[$i];
		}

		// The second group is the intro articles.
		$limit = $numLeading + $numIntro;
		// Order articles across, then down (or single column mode)
		for ($i = $numLeading; $i < $limit && $i < $max; $i++)
		{
			$this->intro_items[$i] = &$items[$i];
		}

		$this->columns = max(1, $params->def('num_columns', 1));
		$order = $params->def('multi_column_order', 1);

		if ($order == 0 && $this->columns > 1)
		{
			// call order down helper
			$this->intro_items = TZ_PortfolioHelperQuery::orderDownColumns($this->intro_items, $this->columns);
		}

		// The remainder are the links.
		for ($i = $numLeading + $numIntro; $i < $max; $i++)
		{
			$this->link_items[$i] = &$items[$i];
		}

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->assignRef('params', $params);
		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('user', $user);

        if(isset($catParams2)){
            if($catParams2){
                if($catParams2 -> get('article_leading_image_size')){
                    $catParams2 -> set('article_leading_image_resize'
                        ,strtolower($catParams2 -> get('article_leading_image_size')));
                }
                if($catParams2 -> get('article_secondary_image_size')){
                    $catParams2 -> set('article_secondary_image_resize'
                        ,strtolower($catParams2 -> get('article_leading_image_size')));
                }
                if($catParams2 -> get('article_leading_image_gallery_size')){
                    $catParams2 -> set('article_leading_image_gallery_resize'
                        ,strtolower($catParams2 -> get('article_leading_image_gallery_size')));
                }
                if($catParams2 -> get('article_secondary_image_gallery_size')){
                    $catParams2 -> set('article_secondary_image_gallery_resize'
                        ,strtolower($catParams2 -> get('article_secondary_image_gallery_size')));
                }

                $params -> merge($catParams2);
            }
        }
        
        $this -> assign('mediaParams',$params);
        
        $doc    = &JFactory::getDocument();
        if($params -> get('tz_use_image_hover',1) == 1):
            $doc -> addStyleDeclaration('
                .tz_image_hover{
                    opacity: 0;
                    position: absolute;
                    top:0;
                    left: 0;
                    transition: opacity '.$params -> get('tz_image_timeout',0.35).'s ease-in-out;
                   -moz-transition: opacity '.$params -> get('tz_image_timeout',0.35).'s ease-in-out;
                   -webkit-transition: opacity '.$params -> get('tz_image_timeout',0.35).'s ease-in-out;
                }
                .tz_image_hover:hover{
                    opacity: 1;
                    margin: 0;
                }
            ');
        endif;
        if($params -> get('tz_use_lightbox') == 1){
            $doc -> addScript('components/com_tz_portfolio/js/jquery.fancybox.pack.js');
            $doc -> addStyleSheet('components/com_tz_portfolio/assets/jquery.fancybox.css');

            $width      = null;
            $height     = null;
            $autosize   = null;
            if($params -> get('tz_lightbox_width')){
                $width  = 'width:'.$params -> get('tz_lightbox_width').',';
            }
            if($params -> get('tz_lightbox_height')){
                $height  = 'height:'.$params -> get('tz_lightbox_height').',';
            }
            $doc -> addScriptDeclaration('
                jQuery(\'.fancybox\').fancybox({
                    type:\'iframe\',
                    openSpeed:'.$params -> get('tz_lightbox_speed',350).',
                    openEffect: "'.$params -> get('tz_lightbox_transition','elastic').'",
                    '.$width.$height.'
		            closeClick	: false,
		            helpers:  {
                        title : {
                            type : "inside"
                        },
                        overlay : {
                            opacity:'.$params -> get('tz_lightbox_opacity',0.75).',
                            css : {
                                "background-color" : "#000"
                            }
                        }
                    }

                });
            ');
        }

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}

		$title = $this->params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Add feed links
		if ($this->params->get('show_feed_link', 1))
		{
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
