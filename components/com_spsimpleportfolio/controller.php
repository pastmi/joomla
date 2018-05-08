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
                ->select('DISTINCT a.id AS value, a.title AS text, a.image AS image')
                ->from('#__spsimpleportfolio_items AS a');

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
