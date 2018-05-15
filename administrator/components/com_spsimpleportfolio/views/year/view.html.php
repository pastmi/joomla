<?php

/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

class SpsimpleportfolioViewYear extends JViewLegacy {

	protected $form;
	protected $item;
	protected $canDo;
	protected $id;

	public function display($tpl = null) {
		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->id = $this->item->id;

		$this->canDo = SpsimpleportfolioHelper::getActions($this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->addToolBar();
		parent::display($tpl);
	}

	protected function addToolBar() {
		$input = JFactory::getApplication()->input;
		$input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		JToolBarHelper::title(JText::_('COM_SPSIMPLEPORTFOLIO_MANAGER') .  ($isNew ? JText::_('COM_SPSIMPLEPORTFOLIO_YEAR_NEW') : JText::_('COM_SPSIMPLEPORTFOLIO_YEAR_EDIT')), 'pictures');

		if ($this->canDo->get('core.edit')) {
			JToolBarHelper::apply('year.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('year.save', 'JTOOLBAR_SAVE');
		}

		JToolBarHelper::cancel('year.cancel', 'JTOOLBAR_CLOSE');
	}
}
