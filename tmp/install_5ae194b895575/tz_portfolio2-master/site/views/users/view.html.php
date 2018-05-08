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
 
//no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'libraries'.DS.'HTTPFetcher.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'libraries'.DS.'readfile.php');

class TZ_PortfolioViewUsers extends JView
{
    function display($tpl = null){
        $doc    = JFactory::getDocument();
        $doc -> addScript(JURI::root()."components/com_tz_portfolio/js/jquery-1.7.2.min.js");
        $doc -> addStyleSheet('components/com_tz_portfolio/css/tz_portfolio.css');
        $state  = $this -> get('State');
        $params = $state -> params;

        $rows   = $this -> get('Users');
        if(count($rows)>0){
            $fetch       = new Services_Yadis_PlainHTTPFetcher();
            foreach($rows as $row){
                $tzRedirect = $params -> get('tz_portfolio_redirect','p_article'); //Set params for $tzRedirect
                $itemParams = new JRegistry($row -> attribs); //Get Article's Params
                //Check redirect to view article
                if($itemParams -> get('tz_portfolio_redirect')){
                    $tzRedirect = $itemParams -> get('tz_portfolio_redirect');
                }

                if($tzRedirect == 'p_article'){
                    $contentUrl =JRoute::_(TZ_PortfolioHelperRoute::getPortfolioArticleRoute($row -> slug,$row -> catid), true ,-1);
                }
                else{
                    $contentUrl =JRoute::_(TZ_PortfolioHelperRoute::getArticleRoute($row -> slug,$row -> catid), true ,-1);
                }

                if($params -> get('tz_comment_type','disqus') == 'facebook'){
                    if($params -> get('tz_show_count_comment',1) == 1){

                        $url    = 'http://graph.facebook.com/?ids='.$contentUrl;

                        $content    = $fetch -> get($url);

                        if($content)
                            $content    = json_decode($content -> body);

                        if(isset($content -> $contentUrl -> comments))
                            $row -> commentCount   = $content -> $contentUrl  -> comments;
                        else
                            $row -> commentCount   = 0;
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
                            $row -> commentCount	= count($content);
                        }
                        else{
                            $row -> commentCount   = 0;
                        }
                    }
                }

                if ($params->get('show_intro', 1)==1) {
                    $row -> text = $row -> introtext;
                }

                JPluginHelper::importPlugin('content');

                $dispatcher	= JDispatcher::getInstance();

                    //
                // Process the content plugins.
                //

                $row->event = new stdClass();
                $results = $dispatcher->trigger('onContentPrepare', array ('com_tz_portfolio.users', &$row, &$params, $state -> get('offset')));

                $results = $dispatcher->trigger('onContentAfterTitle', array('com_tz_portfolio.users', &$row, &$params, $state -> get('offset')));
                $row->event->afterDisplayTitle = trim(implode("\n", $results));

                $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_tz_portfolio.users', &$row, &$params, $state -> get('offset')));
                $row->event->beforeDisplayContent = trim(implode("\n", $results));

                $results = $dispatcher->trigger('onContentAfterDisplay', array('com_tz_portfolio.users', &$row, &$params, $state -> get('offset')));
                $row->event->afterDisplayContent = trim(implode("\n", $results));
            }

        }
        
        $this -> assign('listsUsers',$rows);
        $this -> assign('authorParams',$this -> get('state') -> params);
        $this -> assign('params',$this -> get('state') -> params);
        $this -> assign('mediaParams',$this -> get('state') -> params);
        $this -> assign('pagination',$this -> get('Pagination'));
        $author = JModel::getInstance('User','TZ_PortfolioModel');
        $author = $author -> getUserId(JRequest::getInt('created_by'));
        $this -> assign('listAuthor',$author);

        
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
                if(preg_match('/%|px/',$params -> get('tz_lightbox_width'))){
                    $width  = 'width:\''.$params -> get('tz_lightbox_width').'\',';
                }
                else
                    $width  = 'width:'.$params -> get('tz_lightbox_width').',';
            }
            if($params -> get('tz_lightbox_height')){
                if(preg_match('/%|px/',$params -> get('tz_lightbox_height'))){
                    $height  = 'height:\''.$params -> get('tz_lightbox_height').'\',';
                }
                else
                    $height  = 'height:'.$params -> get('tz_lightbox_height').',';
            }
            if($width || $height){
                $autosize   = 'fitToView: false,autoSize: false,';
            }
            $doc -> addScriptDeclaration('
                jQuery(\'.fancybox\').fancybox({
                    type:\'iframe\',
                    openSpeed:'.$params -> get('tz_lightbox_speed',350).',
                    openEffect: "'.$params -> get('tz_lightbox_transition','elastic').'",
                    '.$width.$height.$autosize.'
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
}