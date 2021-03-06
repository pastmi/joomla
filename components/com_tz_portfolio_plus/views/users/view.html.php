<?php
/*------------------------------------------------------------------------

# TZ Portfolio Plus Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2015 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/
 
//no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');

JLoader::import('com_tz_portfolio_plus.helpers.article', JPATH_SITE.DIRECTORY_SEPARATOR.'components');
JLoader::import('com_tz_portfolio_plus.helpers.extrafields', JPATH_SITE.DIRECTORY_SEPARATOR.'components');

use Joomla\Registry\Registry;

class TZ_Portfolio_PlusViewUsers extends JViewLegacy
{
    protected $state        = null;
    protected $item         = null;
    protected $item_author  = null;
    protected $params       = null;
    protected $char         = null;
    protected $availLetter  = null;

    function __construct($config = array()){
        $this -> item           = new stdClass();
        parent::__construct($config);
    }
    function display($tpl = null){
        $doc        = JFactory::getDocument();
        $menus		= JMenu::getInstance('site');
        $active     = $menus->getActive();

        $state          = $this -> get('State');
        $this -> state  = $state;
        $params         = $state -> params;

        // Set value again for option tz_portfolio_plus_redirect
        if($params -> get('tz_portfolio_plus_redirect') == 'default'){
            $params -> set('tz_portfolio_plus_redirect','article');
        }

        $items   = $this -> get('Items');

        if($items){
            $user	= JFactory::getUser();
            $userId	= $user->get('id');
            $guest	= $user->get('guest');

            $content_ids    = array();
            if($items) {
                $content_ids    = ArrayHelper::getColumn($items, 'id');
            }

            $mainCategories     = TZ_Portfolio_PlusFrontHelperCategories::getCategoriesByArticleId($content_ids,
                array('main' => true));
            $second_categories  = TZ_Portfolio_PlusFrontHelperCategories::getCategoriesByArticleId($content_ids,
                array('main' => false));

            $tags   = null;
            if(count($content_ids) && $params -> get('show_tags',1)) {
                $tags = TZ_Portfolio_PlusFrontHelperTags::getTagsByArticleId($content_ids, array(
                        'orderby' => 'm.contentid',
                        'reverse_contentid' => true
                    )
                );
            }

            $dispatcher	= JDispatcher::getInstance();
            JPluginHelper::importPlugin('content');
            TZ_Portfolio_PlusPluginHelper::importPlugin('content');
            TZ_Portfolio_PlusPluginHelper::importPlugin('mediatype');

            $dispatcher -> trigger('onAlwaysLoadDocument', array('com_tz_portfolio_plus.users'));
            $dispatcher -> trigger('onLoadData', array('com_tz_portfolio_plus.users', $items, $params));

            foreach($items as $i => &$item){

                $item->params   = clone($params);

                $articleParams = new JRegistry;
                $articleParams->loadString($item->attribs);

                if($mainCategories && isset($mainCategories[$item -> id])){
                    $mainCategory   = $mainCategories[$item -> id];
                    if($mainCategory){
                        $item -> catid          = $mainCategory -> id;
                        $item -> category_title = $mainCategory -> title;
                        $item -> catslug        = $mainCategory -> id.':'.$mainCategory -> alias;
                        $item -> category_link  = $mainCategory -> link;

                        // Merge main category's params to article
                        $catParams  = new JRegistry($mainCategory ->  params);
                        if($inheritFrom = $catParams -> get('inheritFrom', 0)){
                            if($inheritCategory    = TZ_Portfolio_PlusFrontHelperCategories::getCategoriesById($inheritFrom)) {
                                $inheritCatParams   = new JRegistry($inheritCategory->params);
                                $catParams          = clone($inheritCatParams);
                            }
                        }
                        $item -> params -> merge($catParams);
                    }
                }else {

                    // Create main category's link
                    $item -> category_link      = TZ_Portfolio_PlusHelperRoute::getCategoryRoute($item -> catid);

                    // Merge main category's params to article
                    if($mainCategory = TZ_Portfolio_PlusFrontHelperCategories::getCategoriesById($item -> catid)) {
                        $catParams = new JRegistry($mainCategory->params);
                        if ($inheritFrom = $catParams->get('inheritFrom', 0)) {
                            if ($inheritCategory = TZ_Portfolio_PlusFrontHelperCategories::getCategoriesById($inheritFrom)) {
                                $inheritCatParams = new JRegistry($inheritCategory->params);
                                $catParams = clone($inheritCatParams);
                            }
                        }
                        $item->params->merge($catParams);
                    }
                }

                // Merge with article params
                $item -> params -> merge($articleParams);

                // Get all second categories
                $item -> second_categories  = null;
                if(isset($second_categories[$item -> id])) {
                    $item->second_categories = $second_categories[$item -> id];
                }

                // Get article's tags
                $item -> tags   = null;
                if($tags && count($tags) && isset($tags[$item -> id])){
                    $item -> tags   = $tags[$item -> id];
                }

                /*** New source ***/
                $tmpl   = null;
                if($item -> params -> get('tz_use_lightbox', 0)){
                    $tmpl   = '&tmpl=component';
                }

				$config = JFactory::getConfig();
				$ssl    = 2;
				if($config -> get('force_ssl')){
					$ssl    = $config -> get('force_ssl');
				}
                $uri    = JUri::getInstance();
                if($uri -> isSsl()){
                    $ssl    = 1;
                }

                // Create article link
                $item ->link        = JRoute::_(TZ_Portfolio_PlusHelperRoute::getArticleRoute($item -> slug, $item -> catid).$tmpl);
                $item -> fullLink   = JRoute::_(TZ_Portfolio_PlusHelperRoute::getArticleRoute($item -> slug, $item -> catid), true, $ssl);

                // Create author link
                $item -> author_link    = JRoute::_(TZ_Portfolio_PlusHelperRoute::getUserRoute($item -> created_by,
                    $params -> get('user_menu_active','auto')));

                // Compute the asset access permissions.
                // Technically guest could edit an article, but lets not check that to improve performance a little.
                if (!$guest) {
                    $asset	= 'com_tz_portfolio_plus.article.'.$item->id;

                    // Check general edit permission first.
                    if ($user->authorise('core.edit', $asset)) {
                        $item -> params ->set('access-edit', true);
                    }
                    // Now check if edit.own is available.
                    elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
                        // Check for a valid user and that they are the owner.
                        if ($userId == $item->created_by) {
                            $item -> params ->set('access-edit', true);
                        }
                    }
                }

                // Old plugins: Ensure that text property is available
                if (!isset($item->text))
                {
                    $item -> text = $item -> introtext;
                }
                if(version_compare(COM_TZ_PORTFOLIO_PLUS_VERSION,'3.1.7','<')){
                    $item -> text    = null;
                    if ($params->get('show_intro', 1)) {
                        $item -> text = $item -> introtext;
                    }
                }

                $item->event = new stdClass();

                //Call trigger in group content
                $results = $dispatcher->trigger('onContentPrepare', array ('com_tz_portfolio_plus.users', &$item, &$params, $state -> get('offset')));
                $item->introtext = $item->text;

                $results = $dispatcher->trigger('onContentAfterTitle', array('com_tz_portfolio_plus.users', &$item, &$params, $state -> get('offset')));
                $item->event->afterDisplayTitle = trim(implode("\n", $results));

                $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_tz_portfolio_plus.users', &$item, &$params, $state -> get('offset')));
                $item->event->beforeDisplayContent = trim(implode("\n", $results));

                $results = $dispatcher->trigger('onContentAfterDisplay', array('com_tz_portfolio_plus.users', &$item, &$params, $state -> get('offset')));
                $item->event->afterDisplayContent = trim(implode("\n", $results));

                $results = $dispatcher->trigger('onContentTZPortfolioVote', array('com_tz_portfolio_plus.users', &$item, &$params, $state -> get('offset')));
                $item->event->TZPortfolioVote = trim(implode("\n", $results));

                // Process the tz portfolio's content plugins.
                $results    = $dispatcher -> trigger('onContentDisplayVote',array('com_tz_portfolio_plus.users',
                    &$item, &$params, $state -> get('offset')));
                $item -> event -> contentDisplayVote   = trim(implode("\n", $results));

                $results    = $dispatcher -> trigger('onBeforeDisplayAdditionInfo',array('com_tz_portfolio_plus.users',
                    &$item, &$params, $state -> get('offset')));
                $item -> event -> beforeDisplayAdditionInfo   = trim(implode("\n", $results));

                $results    = $dispatcher -> trigger('onAfterDisplayAdditionInfo',array('com_tz_portfolio_plus.users',
                    &$item, &$params, $state -> get('offset')));
                $item -> event -> afterDisplayAdditionInfo   = trim(implode("\n", $results));

                $results    = $dispatcher -> trigger('onContentDisplayListView',array('com_tz_portfolio_plus.users',
                    &$item, &$params, $state -> get('offset')));
                $item -> event -> contentDisplayListView   = trim(implode("\n", $results));

                //Call trigger in group tz_portfolio_plus_mediatype
                $results    = $dispatcher -> trigger('onContentDisplayMediaType',array('com_tz_portfolio_plus.users',
                    &$item, &$params, $state -> get('offset')));
                if($item){
                    $item -> event -> onContentDisplayMediaType    = trim(implode("\n", $results));
                    if($results    = $dispatcher -> trigger('onAddMediaType')){
                        $mediatypes = array();
                        foreach($results as $result){
                            if(isset($result -> special) && $result -> special) {
                                $mediatypes[] = $result -> value;
                            }
                        }
                        $item -> mediatypes = $mediatypes;
                    }
                }else{
                    unset($items[$i]);
                }

                // Get article's extrafields
                $extraFields    = TZ_Portfolio_PlusFrontHelperExtraFields::getExtraFields($item, $item -> params,
                    false, array('filter.list_view' => true, 'filter.group' => $params -> get('order_fieldgroup', 'rdate')));
                $item -> extrafields    = $extraFields;
            }
        }

        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

        if ($active)
        {
            $params->def('page_heading', $params->get('page_title', $active->title));
        }
        else
        {
            $params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
        }

        $this -> items  = $items;
        $this -> params = $params;
        $this -> assign('mediaParams',$params);
        $this -> assign('pagination',$this -> get('Pagination'));

        if($author    = JFactory::getUser($state -> get('users.id'))){
            $author_registry    = $author -> getParameters();
            $author_info    = new stdClass();

            $author_info -> id              = $author -> id;
            $author_info -> url             = $author_registry -> get('tz_portfolio_plus_user_url');
            $author_info -> email           = $author -> email;
            $author_info -> gender          = $author_registry -> get('tz_portfolio_plus_user_gender');
            $author_info -> avatar          = $author_registry -> get('tz_portfolio_plus_user_avatar');
            $author_info -> twitter         = $author_registry -> get('tz_portfolio_plus_user_twitter');
            $author_info -> facebook        = $author_registry -> get('tz_portfolio_plus_user_facebook');
            $author_info -> instagram       = $author_registry -> get('tz_portfolio_plus_user_instagram');
            $author_info -> googleplus      = $author_registry -> get('tz_portfolio_plus_user_googleplus');
            $author_info -> description     = $author_registry -> get('tz_portfolio_plus_user_description');
            $author_info -> author          = $author -> name;
            $author_info -> author_link     = JRoute::_(TZ_Portfolio_PlusHelperRoute::getUserRoute($state -> get('users.id'),
                $params -> get('user_menu_active','auto')));
            $this -> item_author    = $author_info;
        }

        $params = $state -> params;

        JModelLegacy::addIncludePath(COM_TZ_PORTFOLIO_PLUS_PATH_SITE.DIRECTORY_SEPARATOR.'models');
        $model  = JModelLegacy::getInstance('Portfolio','TZ_Portfolio_PlusModel',array('ignore_request' => true));
        $model -> setState('params',$params);
        $model -> setState('filter.userId',$state -> get('users.id'));
        $this -> char           = $state -> get('filter.char');
        $this -> availLetter    = $model -> getAvailableLetter();

        $doc -> addStyleSheet('components/com_tz_portfolio_plus/css/tzportfolioplus.min.css');

        $this -> _prepareDocument();

        // Add feed links
		if ($params->get('show_feed_link', 1)) {
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$doc->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$doc->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
        
        parent::display($tpl);

    }

    protected function _prepareDocument()
    {
        $app    = JFactory::getApplication();
        $title  = $this->params->get('page_title', '');

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

        if ($app->getCfg('MetaAuthor') == '1' && $this -> item_author) {
            $this->document->setMetaData('author', $this -> item_author -> author);
        }
    }
}