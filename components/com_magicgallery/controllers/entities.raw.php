<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Entities RAW controller class.
 *
 * @package        Magicgallery
 * @subpackage     Components
 * @since          1.6
 */
class MagicgalleryControllerEntities extends JControllerLegacy
{
    /**
     * Return the model of the item.
     *
     * @param string $name
     * @param string $prefix
     * @param array  $config
     *
     * @return MagicgalleryModelEntity
     */
    public function getModel($name = 'Entity', $prefix = 'MagicgalleryModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Delete an item.
     *
     * @throws Exception
     */
    public function remove()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId   = JFactory::getUser()->get('id');

        // Check for authorized user.
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        $entityId   = $this->input->post->getInt('entity_id');
        $objectId   = $this->input->post->getInt('object_id');
        $categoryId = $this->input->post->getInt('category_id');
        $extension  = $this->input->post->getCmd('extension');

        $keys = array(
            'object_id'     => $objectId,
            'extension'     => $extension,
            'user_id'       => $userId,
            'catid'         => $categoryId
        );

        $gallery = new Magicgallery\Gallery\Gallery(JFactory::getDbo());
        $gallery->load($keys);

        // Check for valid gallery.
        if (!$gallery->getId()) {
            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_ERROR_INVALID_GALLERY'))
                ->failure();

            echo $response;
            $app->close();
        }

        try {
            $this->getModel()->remove($entityId, $gallery);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_magicgallery');
            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_MAGICGALLERY_SUCCESS'))
            ->setText(JText::_('COM_MAGICGALLERY_ITEM_DELETED'))
            ->setData(array('entity_id' => $entityId))
            ->success();

        echo $response;
        $app->close();
    }
}
