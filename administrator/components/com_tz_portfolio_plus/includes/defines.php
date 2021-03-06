<?php
/*------------------------------------------------------------------------

# TZ Portfolio Plus Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2013 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// No direct access
defined('_JEXEC') or die;
if(!defined('COM_TZ_PORTFOLIO_PLUS_JVERSION_COMPARE')) {
    define('COM_TZ_PORTFOLIO_PLUS_JVERSION_COMPARE', version_compare(JVERSION, '3.0', 'ge'));
}
if(!DIRECTORY_SEPARATOR){
    define('DIRECTORY_SEPARATOR',DS);
}
if(!defined('COM_TZ_PORTFOLIO_PLUS')) {
    define('COM_TZ_PORTFOLIO_PLUS', 'com_tz_portfolio_plus');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_EDITION')) {
    define('COM_TZ_PORTFOLIO_PLUS_EDITION', 'free');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_PATH_SITE')) {
    define('COM_TZ_PORTFOLIO_PLUS_PATH_SITE', JPATH_SITE . '/components/com_tz_portfolio_plus');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_ADMIN_PATH')) {
    define ('COM_TZ_PORTFOLIO_PLUS_ADMIN_PATH', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR
        .'components'.DIRECTORY_SEPARATOR.COM_TZ_PORTFOLIO_PLUS);
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_ADMIN_HELPERS_PATH')) {
    define ('COM_TZ_PORTFOLIO_PLUS_ADMIN_HELPERS_PATH', COM_TZ_PORTFOLIO_PLUS_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_SITE_HELPERS_PATH')) {
    define ('COM_TZ_PORTFOLIO_PLUS_SITE_HELPERS_PATH', COM_TZ_PORTFOLIO_PLUS_PATH_SITE.DIRECTORY_SEPARATOR.'helpers');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_LIBRARIES')) {
    define ('COM_TZ_PORTFOLIO_PLUS_LIBRARIES', COM_TZ_PORTFOLIO_PLUS_ADMIN_PATH.DIRECTORY_SEPARATOR.'libraries');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_MEDIA_BASE')) {
    define ('COM_TZ_PORTFOLIO_PLUS_MEDIA_BASE', JPATH_ROOT . '/media/tz_portfolio_plus');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_MEDIA_ARTICLE_BASE')) {
    define ('COM_TZ_PORTFOLIO_PLUS_MEDIA_ARTICLE_BASE', 'media/tz_portfolio_plus/article/cache');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_MEDIA_ARTICLE_ROOT')) {
    define ('COM_TZ_PORTFOLIO_PLUS_MEDIA_ARTICLE_ROOT', JPATH_ROOT . DIRECTORY_SEPARATOR.COM_TZ_PORTFOLIO_PLUS_MEDIA_ARTICLE_BASE);
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_MEDIA_BASEURL')) {
    define ('COM_TZ_PORTFOLIO_PLUS_MEDIA_BASEURL', JURI::root() . 'media/tz_portfolio_plus/article/cache');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_TEMPLATE_PATH')) {
    define ('COM_TZ_PORTFOLIO_PLUS_TEMPLATE_PATH',COM_TZ_PORTFOLIO_PLUS_PATH_SITE.DIRECTORY_SEPARATOR.'templates');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_ADDON_PATH')) {
    define('COM_TZ_PORTFOLIO_PLUS_ADDON_PATH', COM_TZ_PORTFOLIO_PLUS_PATH_SITE.DIRECTORY_SEPARATOR.'addons');
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_ACL_SECTIONS')) {
    define('COM_TZ_PORTFOLIO_PLUS_ACL_SECTIONS', json_encode(array('category', 'group', 'tag', 'addon', 'template', 'style')));
}
if(!defined('COM_TZ_PORTFOLIO_PLUS_VERSION')) {
    if(file_exists(JPATH_ADMINISTRATOR.'/components/com_tz_portfolio_plus/tz_portfolio_plus.xml')){
        define('COM_TZ_PORTFOLIO_PLUS_VERSION',simplexml_load_file(JPATH_ADMINISTRATOR.'/components/com_tz_portfolio_plus/tz_portfolio_plus.xml')->version);
    }elseif(file_exists(JPATH_ADMINISTRATOR.'/components/com_tz_portfolio_plus/manifest.xml')){
        define('COM_TZ_PORTFOLIO_PLUS_VERSION',simplexml_load_file(JPATH_ADMINISTRATOR.'/components/com_tz_portfolio_plus/manifest.xml')->version);
    }
}