<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/
jimport('joomla.application.component.controller');
class SpsimpleportfolioController extends JControllerLegacy {

    function getJsonTags () {
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
    function getJsonTagsList () {
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
    function getJsonItems () {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('DISTINCT a.id AS value, a.title AS text, a.image AS image, a.tagids as tagids, a.published AS published, a.date AS date')
                ->from('#__spsimpleportfolio_items AS a');

            $db->setQuery($query);
            $count = count($db->loadObjectList());
            $options = $db->loadObjectList();
            $options['count_of_pages'] = ( $count < 10) ? 1 : ceil($count/10);
            echo new JResponseJson($options);
        }
        catch(Exception $e)
        {
            echo new JResponseJson($e);
        }

    }
}
