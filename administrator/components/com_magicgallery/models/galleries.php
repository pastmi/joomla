<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class MagicgalleryModelGalleries extends JModelList
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
                'title', 'a.title',
                'published', 'a.published',
                'ordering', 'a.ordering',
                'category', 'b.title',
                'extension', 'a.extension',
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        // Load the filter state.
        $value = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $value);

        $value = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $value);

        $value = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', 0, 'integer');
        $this->setState('filter.category_id', $value);

        $value = $this->getUserStateFromRequest($this->context . '.filter.extension', 'filter_extension', '', 'string');
        $this->setState('filter.extension', $value);

        // Load the component parameters.
        $params = JComponentHelper::getParams($this->option);
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.ordering', 'asc');
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
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.extension');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.alias, a.title, a.url, a.extension, ' .
                'a.published, a.ordering, a.catid, ' .
                'b.title as category'
            )
        );

        $query->from($db->quoteName('#__magicgallery_galleries', 'a'));
        $query->leftJoin($db->quoteName('#__categories', 'b') . ' ON a.catid = b.id');

        // Filter by category
        $categoryId = (int)$this->getState('filter.category_id');
        if ($categoryId > 0) {
            $query->where('a.catid =' . (int)$categoryId);
        }

        // Filter by extension.
        $extension = $this->getState('filter.extension');
        if (JString::strlen($extension) > 0) {
            $query->where('a.extension =' . $db->quote($extension));
        }

        // Filter by published state
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.published = ' . (int)$state);
        } elseif ($state === '') {
            $query->where('(a.published IN (0, 1))');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (JString::strlen($search) > 0) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $orderCol  = $this->getState('list.ordering', 'a.id');
        $orderDirn = $this->getState('list.direction', 'asc');
        if ($orderCol === 'a.ordering') {
            $orderCol = 'a.catid ' . $orderDirn . ', a.ordering';
        }

        return $orderCol . ' ' . $orderDirn;
    }
}
