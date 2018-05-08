<?php
/**
 * @package      Magicgallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class MagicgalleryViewCategories extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    protected $items;
    protected $pagination;

    protected $event;
    protected $option;

    protected $category;
    protected $categoryId;
    protected $portfolio;
    protected $projectsView;

    protected $pageclass_sfx;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $this->option     = $app->input->get('option');

        // Initialise variables
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->params     = $this->state->params;

        $this->projectsView = $app->input->getString('projects_view', 'categories');

        // Parse parameters
        if ($this->items !== null and count($this->items) > 0) {
            foreach ($this->items as $item) {
                $item->params = json_decode($item->params);
                if (($item->params !== null) and isset($item->params->image) and ($item->params->image !== '')) {
                    $item->image = $item->params->image;
                }
            }
        }

        $this->prepareDocument();

        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher = JEventDispatcher::getInstance();
        $offset     = 0;

        $item              = new stdClass();
        $item->title       = $this->document->getTitle();
        $item->link        = MagicgalleryHelperRoute::getCategoriesViewRoute('categories');
        $item->image_intro = MagicgalleryHelper::getCategoryImage($this->items);

        $this->event                         = new stdClass();
        $results                             = $dispatcher->trigger('onContentBeforeDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentBeforeDisplay = trim(implode("\n", $results));

        $results                            = $dispatcher->trigger('onContentAfterDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentAfterDisplay = trim(implode("\n", $results));

        parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function prepareDocument()
    {
        $app   = JFactory::getApplication();
        $menus = $app->getMenu();

        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_MAGICGALLERY_CATEGORIES_DEFAULT_PAGE_TITLE'));
        }

        // Set page title
        $title = $this->params->get('page_title', '');
        if ($title !== '') {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0)) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        }
        $this->document->setTitle($title);

        // Meta Description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        // Meta keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }
    }
}
