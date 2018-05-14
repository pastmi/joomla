<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/
jimport('joomla.application.component.controller');
class SpsimpleportfolioController extends JControllerLegacy {
    function getJsonAuthorId () {
        try
        {
            $db = JFactory::getDbo();
            $id = (int)JRequest::getVar('id');
            $query = $db->getQuery(true)
                ->select( $db->quoteName(array('id', 'title', 'description','params')) )
                ->from($db->quoteName('#__categories'))
                ->where($db->quoteName('extension') . ' = '. $db->quote('com_spsimpleportfolio') . ' AND ' . $db->quoteName('id') . ' = '. $id);
            $db->setQuery($query);
            $options = $db->loadObjectList();
            foreach ($options as $option) {
                $image = json_decode($option->params)->image;
                $newquery = $db->getQuery(true)
                    ->select(array('title AS name','image'))
                    ->from($db->quoteName('#__spsimpleportfolio_tags'))
                    ->where($db->quoteName('catid') . ' = '. $option->id);
                $db->setQuery($newquery);
                $options['list_of_pictures'] = $db->loadObjectList();
                $option->image = '//'.$_SERVER['SERVER_NAME'].'/'.$image;
                $option->params = NULL;
            }
            foreach($options['list_of_pictures'] as $option) {
                $option->image = '//'.$_SERVER['SERVER_NAME'].'/'.$option->image;
            }
            echo new JResponseJson($options);
        }
        catch(Exception $e)
        {
            echo new JResponseJson($e);
        }
    }
    function getJsonAuthors () {
        try
        {
            $db = JFactory::getDbo();
            $page = (int)JRequest::getVar('page');
            $page = isset($page) ? ($page * 10) - 10 : 0;
            $query = $db->getQuery(true)
                ->select( $db->quoteName(array('id', 'title' , 'description','params')) )
                ->from($db->quoteName('#__categories'))
                ->where($db->quoteName('extension') . ' = '. $db->quote('com_spsimpleportfolio'))
                ->setLimit(10, $page);
            $db->setQuery($query);
            $count = count($db->loadObjectList());
            $options = $db->loadObjectList();
            foreach ($options as $option) {
                $image = json_decode($option->params)->image;
                $newquery = $db->getQuery(true)
                    ->select(array('catid', 'COUNT(catid) AS count'))
                    ->from($db->quoteName('#__spsimpleportfolio_tags'))
                    ->where($db->quoteName('catid') . ' = '. $option->id);
                $db->setQuery($newquery);
                $option->image = '//'.$_SERVER['SERVER_NAME'].'/'.$image;
                $option->count_of_images = $db->loadObjectList()[0]->count;
                $option->params = NULL;
            }
            $response['list'] = $options;
            $response['count_of_pages'] = ( $count <= 10) ? 1 : ceil($count/10);
            echo new JResponseJson($response);
        }
        catch(Exception $e)
        {
            echo new JResponseJson($e);
        }

    }
    function getJsonPictures () {
        try
        {
            $id = JRequest::getVar('id');
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select($db->quoteName(array('tagids')));
            $query->from($db->quoteName('#__spsimpleportfolio_items'));
            $query->where($db->quoteName('id') . ' = '. $id);
            $db->setQuery($query);
            $ids = $db->loadObjectList()[0]->tagids;
            if(!is_array($ids)) {
                $ids = (array) json_decode($ids, $id);
            }

            $ids = implode(',', $ids);
            $query = $db->getQuery(true);
            $query->select($db->quoteName(array('id', 'title', 'alias','image','catid')));
            $query->from($db->quoteName('#__spsimpleportfolio_tags'));
            $query->where($db->quoteName('id')." IN (" . $ids . ")");

            $query->order('id ASC');

            $db->setQuery($query);
            $objects = $db->loadObjectList();
            foreach ($objects as $object) {
                $newquery = $db->getQuery(true)
                    ->select(array('title'))
                    ->from($db->quoteName('#__categories'))
                    ->where($db->quoteName('id') . ' = '. $object->catid);
                $db->setQuery($newquery);
                $object->nameAuthor = $db->loadObjectList()[0]->title;
            }
            echo new JResponseJson($objects);
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
                $option->image = '//'.$_SERVER['SERVER_NAME'].'/'.$option->image;
                $tags = $option->tagids;
                if(!is_array($tags)) {
                    $tags = (array) json_decode($tags, true);
                }
                $tags = implode(',', $tags);
                $tags = explode(',', $tags);
                $option->count_of_images = count($tags);
            }
            $response['list'] = $options;
            $response['count_of_pages'] = ( $count <= 10) ? 1 : ceil($count/10);
            echo new JResponseJson($response);
        }
        catch(Exception $e)
        {
            echo new JResponseJson($e);
        }
    }
    function getJsonYears () {
        try
        {
            $db = JFactory::getDbo();

            $query = $db->getQuery(true)
                ->select('DISTINCT a.title AS name, a.description AS information')
                ->from('#__spsimpleportfolio_years AS a');

            $db->setQuery($query);
            $options = $db->loadObjectList();
            echo new JResponseJson($options);
        }
        catch(Exception $e)
        {
            echo new JResponseJson($e);
        }

    }
}
