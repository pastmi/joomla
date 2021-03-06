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

// No direct access.
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');

$doc    = JFactory::getDocument();
$doc -> addScript(TZ_Portfolio_PlusUri::root(true, null, true).'/js/jquery-ui.min.js');
$doc -> addStyleSheet(TZ_Portfolio_PlusUri::root(true, null, true).'/css/jquery-ui.min.css');
$doc -> addStyleSheet(TZ_Portfolio_PlusUri::root(true, null, true).'/css/tz_portfolio_plus.min.css');

if(!$this -> tagsSuggest){
    $this -> tagsSuggest    = 'null';
}

$doc -> addScriptDeclaration('
(function($){
    "use strict";
    $(document).ready(function(){
        Joomla.submitbutton = function(task) {
            if (task == "article.cancel" || document.formvalidator.isValid(document.id("item-form"))) {
                '. $this->form->getField('articletext')->save().'
                Joomla.submitform(task, document.getElementById("item-form"));
            }
        }
        
        $(\'#jform_second_catid option[value="\'+$(\'#jform_catid\').val()+\'"]\').attr(\'disabled\',\'disabled\');
            $(\'#jform_second_catid\').trigger(\'liszt:updated\');
            $(\'#jform_catid\').on(\'change\',function(){
                $(\'#jform_second_catid option:selected\').removeAttr(\'selected\');
                $(\'#jform_second_catid option:disabled\').removeAttr(\'disabled\');
                $(\'#jform_second_catid option[value="\'+this.value+\'"]\').attr(\'disabled\',\'disabled\');
                $(\'#jform_second_catid\').trigger(\'liszt:updated\');
        });
        $(document).off("click.bs.tab.data-api")
					.on("click.bs.tab.data-api", "[data-toggle=tab]", function (e) {
            e.preventDefault();
              $(this).tab("show");
        });
                
        $(document).ready(function(){
            $("[data-toggle=dropdown]").parent().on("hidden.bs.dropdown", function(){ $(this).show();});
        });
    });
})(jQuery);
');

?>

<div class="tp-edit-page tp-item-page<?php echo $this->pageclass_sfx; ?> tzpp_bootstrap3">
    <?php if ($params->get('show_page_heading')){ ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape($params->get('page_heading')); ?>
            </h1>
        </div>
    <?php } ?>

    <form action="<?php echo JRoute::_('index.php?option=com_tz_portfolio_plus&a_id=' . (int) $this->item->id);
    ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical"
          enctype="multipart/form-data">

        <?php echo JHtml::_('bootstrap.startTabSet', 'tp-tab-edit', array('active' => 'tp-tab-edit__general')); ?>

            <?php
            // Start tab general
            echo JHtml::_('bootstrap.addTab', 'tp-tab-edit', 'tp-tab-edit__general', JText::_('JDETAILS')); ?>
                <div class="row">
                    <div class="col-md-6">
                        <?php echo $this->form->renderField('title'); ?>
                        <?php echo $this->form->renderField('alias'); ?>

                        <div class="control-group">
                            <div class="control-label">
                                <label><?php echo $this->form->getLabel('tags');?></label>
                            </div>
                            <div class="controls">
                                <?php echo $this -> form -> getInput('tags'); ?>
                                <div><?php echo JText::_('COM_TZ_PORTFOLIO_PLUS_FORM_TAGS_DESC');?></div>
                            </div>
                        </div>
                        <?php echo $this->form->renderField('state'); ?>
                        <?php echo $this->form->renderField('access'); ?>
                        <?php echo $this->form->renderField('id'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo $this->form->renderField('catid'); ?>
                        <?php echo $this->form->renderField('second_catid'); ?>
                        <?php echo $this->form->renderField('groupid'); ?>
                        <?php echo $this->form->renderField('type'); ?>
                        <?php echo $this->form->renderField('featured'); ?>
                        <?php echo $this->form->renderField('language'); ?>
                        <?php echo $this->form->renderField('template_id'); ?>
                    </div>
                </div>

                <?php echo JHtml::_('bootstrap.startTabSet', 'tp-tab-add-on', array('active' => 'tp-tab-add-on__content')); ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'tp-tab-add-on', 'tp-tab-add-on__content', JText::_('COM_TZ_PORTFOLIO_PLUS_TAB_CONTENT')); ?>
                    <?php echo $this->form->getInput('articletext'); ?>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>

                    <?php
                    if($this -> plgTabs && count($this -> plgTabs)) {
                        foreach ($this->plgTabs as $media) {
                            echo JHtml::_('bootstrap.addTab', 'tp-tab-add-on', 'tp-tab-add-on__mediatype-'.$media -> type -> value, $media -> type -> text);
                            echo $media -> html;
                            echo JHtml::_('bootstrap.endTab');
                        }
                    }
                    ?>

                    <?php echo JHtml::_('bootstrap.addTab', 'tp-tab-add-on', 'tp-tab-add-on__fields', JText::_('COM_TZ_PORTFOLIO_PLUS_TAB_FIELDS')); ?>
                    <?php echo $this-> loadTemplate('extrafields'); ?>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php echo JHtml::_('bootstrap.endTabSet'); ?>

            <?php echo JHtml::_('bootstrap.endTab'); // End tab general ?>

            <?php // Start tab publishing
            echo JHtml::_('bootstrap.addTab', 'tp-tab-edit', 'tp-tab-edit__publishing', JText::_('COM_TZ_PORTFOLIO_PLUS_PUBLISHING')); ?>
                <?php echo $this->form->renderField('publish_up'); ?>
                <?php echo $this->form->renderField('publish_down'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); // End tab publishing ?>

            <?php // Start tab options
            echo JHtml::_('bootstrap.addTab', 'tp-tab-edit', 'tp-tab-edit__options',
                JText::_('JOPTIONS')); ?>

                <?php  $fieldSets = $this->form->getFieldsets('attribs');
                if($fieldSets && count($fieldSets)) {
                    ?>
                    <?php echo JHtml::_('bootstrap.startAccordion', 'tp-ac-edit__article-options', array('active' => 'tp-ac-edit__collapse0'
                    , 'parent' => true)); // Start accordion ?>
                    <?php $i = 0; ?>
                    <?php foreach ($fieldSets as $name => $fieldSet) { ?>
                        <?php echo JHtml::_('bootstrap.addSlide', 'tp-ac-edit__article-options', JText::_($fieldSet->label), 'tp-ac-edit__collapse' . $i++); ?>
                        <?php if (isset($fieldSet->description) && trim($fieldSet->description)) { ?>
                            <p class="tip"><?php echo $this->escape(JText::_($fieldSet->description)); ?></p>
                        <?php } ?>
                        <fieldset>
                            <?php foreach ($this->form->getFieldset($name) as $field) {
                                echo $field->renderField();
                            } ?>
                        </fieldset>
                        <?php echo JHtml::_('bootstrap.endSlide'); ?>
                    <?php } ?>

                    <?php echo JHtml::_('bootstrap.endAccordion');
                } // End accordion?>
            <?php echo JHtml::_('bootstrap.endTab'); // End tab options ?>

            <?php // Start tab metadata
            echo JHtml::_('bootstrap.addTab', 'tp-tab-edit', 'tp-tab-edit__metadata',
                JText::_('COM_TZ_PORTFOLIO_PLUS_METADATA')); ?>
                <?php echo $this->loadTemplate('metadata'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); // End tab metadata ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <div class="btn-toolbar">
            <div class="btn-group">
                <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('article.save')">
                    <span class="icon-ok"></span><?php echo JText::_('JSAVE') ?>
                </button>
            </div>
            <div class="btn-group">
                <button type="button" class="btn" onclick="Joomla.submitbutton('article.cancel')">
                    <span class="icon-cancel"></span><?php echo JText::_('JCANCEL') ?>
                </button>
            </div>
            <?php if ($params->get('save_history', 0) && $this->item->id) : ?>
                <div class="btn-group">
                    <?php echo $this->form->getInput('contenthistory'); ?>
                </div>
            <?php endif; ?>
        </div>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
