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

class MagicgalleryModelLineal extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array  $config An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'catid', 'a.catid',
                'published', 'a.published',
                'ordering', 'a.ordering'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering
     * @param string $direction
     *
     * @return  void
     * @since   1.6
     */
    protected function populateState($ordering = 'ordering', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $this->setState('params', $app->getParams());

        $this->setState($this->context.'.filter.catid', $app->input->getInt('id'));

        $this->setState('list.start', $app->input->getInt('limitstart', 0));
        $this->setState('list.limit', 1);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string $id A prefix for the store id.
     *
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.catid');

        return parent::getStoreId($id);
    }

    /**
     * Get the master query for retrieving a list of projects to the model state.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    public function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.description, a.url, a.params, ' .
                'a.catid, a.published, a.ordering '
            )
        );

        $query->from($db->quoteName('#__magicgallery_galleries', 'a'));

        // Filter by a single or group of categories
        $categoryId = (int)$this->getState($this->context.'.filter.catid');
        if ($categoryId > 0) {
            $query->where('a.catid = ' . (int)$categoryId);
        }

        // Filter by state
        $query->where('a.published = '. (int)Prism\Constants::PUBLISHED);

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $orderCol  = $this->getState('list.ordering', 'a.ordering');
        $orderDirn = $this->getState('list.direction', 'ASC');

        return $orderCol . ' ' . $orderDirn;
    }
}
