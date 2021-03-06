<?php
/*------------------------------------------------------------------------

# TZ Portfolio Plus Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2015 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', 'select');

$assoc      = JLanguageAssociations::isEnabled();
// Are associations implemented for this extension?
$extensionassoc = array_key_exists('item_associations', $this->form->getFieldsets());

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "category.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			' . $this->form->getField("description")->save() . '
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>

<form method="post" name="adminForm" id="item-form" class="form-validate form-horizontal tpArticle" enctype="multipart/form-data"
	action="<?php echo JRoute::_('index.php?option=com_tz_portfolio_plus&extension='
        .JFactory::getApplication()->input->getCmd('extension', 'com_tz_portfolio_plus').'&layout=edit&id='
        .(int) $this->item->id); ?>">
    <div class="row-fluid">
        <div class="span8">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('JDETAILS', true)); ?>
            <div class="row-fluid">
                <div class="span6">
                    <?php echo $this -> form -> renderField('title');?>
                    <?php echo $this -> form -> renderField('alias');?>
                    <?php echo $this -> form -> renderField('groupid');?>
                    <?php echo $this -> form -> renderField('images');?>
                    <?php echo $this -> form -> renderField('parent_id');?>
                    <?php echo $this -> form -> renderField('template_id');?>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label max-width-180">
                            <?php echo $this->form->getLabel('inheritFrom','params'); ?>
                        </div>
                        <div class="controls">
                            <?php echo $this->form->getInput('inheritFrom','params'); ?>
                        </div>
                    </div>
                    <?php echo $this -> form -> renderField('published');?>
                    <?php echo $this -> form -> renderField('access');?>
                    <?php echo $this -> form -> renderField('language');?>
                    <?php echo $this -> form -> renderField('id');?>
                </div>
            </div>

            <?php echo $this -> form -> renderField('description');?>
            <?php echo $this -> form -> renderField('extension');?>

            <?php echo JHtml::_('bootstrap.endTab'); ?>

            <?php if ($assoc && $extensionassoc) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS', true)); ?>
            <?php echo $this->loadTemplate('associations'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php endif;?>

            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'metadata', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS', true)); ?>
            <?php echo $this->loadTemplate('metadata'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>

            <?php if ($this->canDo->get('core.admin')): ?>
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_CATEGORIES_FIELDSET_RULES', true)); ?>
                <?php echo $this->form->getInput('rules'); ?>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php endif; ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        </div>

        <div class="span4">
            <?php echo $this->loadTemplate('options'); ?>
        </div>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
