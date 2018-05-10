<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/
jimport('joomla.application.component.controller');
class SpsimpleportfolioController extends JControllerLegacy {

    function getJsonAuthors () {
        try
        {
            $db = JFactory::getDbo();
            $page = (int)JRequest::getVar('page');
            $page = isset($page) ? ($page * 10) - 10 : 0;
            $query = $db->getQuery(true)
                ->select( $db->quoteName(array('id', 'title', 'description','params')) )
                ->from($db->quoteName('#__categories'))
                ->where($db->quoteName('extension') . ' = '. $db->quote('com_spsimpleportfolio'));
//            $query = $db->getQuery(true)
//                ->select('DISTINCT a.id AS value, a.title AS text, a.image AS image, a.tagids as tagids, a.published AS published, a.date AS date')
//                ->from('#__categories AS a')
//                ->setLimit(10, $page);

            $db->setQuery($query);
//            $tags = $db->loadObject()->tagids;
//            if(!is_array($tags)) {
//                $tags = (array) json_decode($tags, true);
//            }
//            $tags = implode(',', $tags);
//            $tags = explode(',', $tags);
//            $count = count($db->loadObjectList());
            $options = $db->loadObjectList();
            foreach ($options as $option) {
                $newquery = $db->getQuery(true)
                    ->select(array('catid', 'COUNT(*)'))
                    ->from($db->quoteName('#__spsimpleportfolio_tags'))
                    ->where($db->quoteName('id')." IN (" . $option->id . ")");
                $db->setQuery($newquery);
                $option->cont_of_images = $db->loadObjectList()->catid;
                var_dump($newquery);
            }
//            $options[$db->loadObject()->value]['imagecount'] = count($tags);
//            $options['count_of_pages'] = ( $count <= 10) ? 1 : ceil($count/10);
            echo new JResponseJson($options);
        }
        catch(Exception $e)
        {
            echo new JResponseJson($e);
        }

    }
    function getJsonPictures () {
        try
        {
            $ids = JRequest::getVar('id');
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if(!is_array($ids)) {
                $ids = (array) json_decode($ids, true);
            }

            $ids = implode(',', $ids);

            $query->select($db->quoteName(array('id', 'title', 'alias','image')));
            $query->from($db->quoteName('#__spsimpleportfolio_tags'));
            $query->where($db->quoteName('id')." IN (" . $ids . ")");

            $query->order('id ASC');

            $db->setQuery($query);
            echo new JResponseJson($db->loadObjectList());
        }
        catch(Exception $e)
        {
            echo new JResponseJson($e);
        }
    }
    function getJsonPicturesList () {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('DISTINCT a.id AS value, a.title AS text, a.image AS image')
                ->from('#__spsimpleportfolio_tags AS a');

            $db->setQuery($query);
            $options = $db->loadObjectList();
            echo new JResponseJson($options);
        }
        catch(Exception $e)
        {
            echo new JResponseJson($e);
        }

    }
    function getJsonExhibitions () {
        try
        {
            $db = JFactory::getDbo();
            $page = (int)JRequest::getVar('page');
            $page = isset($page) ? ($page * 10) - 10 : 0;

            $query = $db->getQuery(true)
                ->select('DISTINCT a.id AS value, a.title AS text, a.image AS image, a.tagids as tagids, a.published AS published, a.date AS date')
                ->from('#__spsimpleportfolio_items AS a')
                ->setLimit(10, $page);

            $db->setQuery($query);
            $count = count($db->loadObjectList());
            $options = $db->loadObjectList();
            foreach ($options as $option) {
                $tags = $option->tagids;
                if(!is_array($tags)) {
                    $tags = (array) json_decode($tags, true);
                }
                $tags = implode(',', $tags);
                $tags = explode(',', $tags);
                $option->count_of_images = count($tags);
            }
            $options['count_of_pages'] = ( $count <= 10) ? 1 : ceil($count/10);
            echo new JResponseJson($options);
        }
        catch(Exception $e)
        {
            echo new JResponseJson($e);
        }

    }
}
