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

$option         = JRequest::getCmd('option','com_tz_portfolio');
$view           = JRequest::getCmd('view','articles');
$task           = JRequest::getCmd('task',null);

if($view != 'categories' && $view != 'category' && $view != 'articles' && $view != 'article' && $view != 'featured'
   && $view != 'users'){
    $controllerName     = $view;

    $path   = JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$controllerName.'.php';

    if(file_exists($path))
        require_once($path);
    else
        JError::raiseError(500,'Invalid controller');

    $controllerClass    = 'TZ_PortfolioController'.ucfirst($controllerName);

    if(class_exists($controllerClass)){
        $controller = new $controllerClass;
    }
    else
        JError::raiseError(500,'Invalid class controller');

        $controller->execute($view);
}
else{

    // Register helper class
    JLoader::register('ContentHelper', dirname(__FILE__) . '/helpers/content.php');

    // Access check.
    if (!JFactory::getUser()->authorise('core.manage', JRequest::getCmd('extension'))) {
        return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    }

    // Include dependancies
    jimport('joomla.application.component.controller');

    // Execute the task.
    $controller	= JController::getInstance('TZ_Portfolio');

//    if($task=='lists')
//        $controller -> execute('article.edit');
//    else
        $controller->execute(JRequest::getVar('task'));
}
$controller->redirect();
