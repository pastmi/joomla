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

class MagicgalleryModelCategories extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'catid', 'a.catid',
                'published', 'a.published'
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
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $params = $app->getParams();
        $this->setState('params', $params);

        // List state information
        $value = $params->get('categories_pp', 0);
        if (!$value) {
            $value = $app->input->getInt('limit', $app->get('list_limit', 20));
        }
        $this->setState('list.limit', $value);

        $value = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $value);

        $this->setState('list.ordering', 'a.rgt');
        $this->setState('list.direction', 'ASC');

        // Get categories IDs
        $value = $app->input->get('categories_ids', array(), 'array');
        $this->setState('categories_ids', $value);
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
        $value = (array)$this->getState('categories_ids');

        $id .= ':' . implode(',', $value);

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
                'a.id, a.title, a.alias, a.params, ' .
                $query->concatenate(array('id', 'alias'), ':') . ' AS slug'
            )
        );

        $query->from($db->quoteName('#__categories', 'a'));

        // Get categories
        $categoriesIds = (array)$this->getState('categories_ids');
        if (count($categoriesIds) > 0) {
            $query->where('a.id IN (' . implode(',', $categoriesIds) . ')');
        }

        // Filter by state
        $query->where('a.published = 1');

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $orderCol  = $this->getState('list.ordering', 'a.rgt');
        $orderDirn = $this->getState('list.direction', 'ASC');

        return $orderCol . ' ' . $orderDirn;
    }
}
