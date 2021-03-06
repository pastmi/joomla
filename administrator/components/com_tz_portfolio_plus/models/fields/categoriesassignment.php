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

//no direct access
defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('checkboxes');

class JFormFieldCategoriesAssignment extends JFormFieldCheckboxes
{
    protected $type = 'CategoriesAssignment';

    protected function getOptions()
    {
        $options = array();
        $db     = JFactory::getDbo();
        $query  = $db -> getQuery(true);
        $query -> select('c.title AS text, c.id AS value,c.template_id');
        $query -> from('#__tz_portfolio_plus_categories AS c');
        $query -> where('extension = "com_tz_portfolio_plus"');

        $db -> setQuery($query);
        if($rows = $db -> loadObjectList()){
            foreach($rows as $option){
                $tmp = JHtml::_('select.option', (string) $option -> value, trim($option -> text), 'value', 'text');

                $checked    = false;
                $app    = JFactory::getApplication();
                $input  = $app -> input;
                $curTemplateId  = null;

                if(!isset($this -> element['template_id'])){
                    if($input -> get('option') == 'com_tz_portfolio_plus' && $input -> get('view') == 'template_style'){
                        $curTemplateId  = $input -> get('id');
                    }
                }else{
                    $curTemplateId  = $this -> element['template_id'];
                }

                if(isset($option -> template_id) && $option -> template_id && !empty($option -> template_id)){
                    if($option -> template_id == $curTemplateId){
                        $checked    = true;
                    }
                }

                $checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

                // Set some option attributes.
                $tmp->checked = $checked;

                // Add the option object to the result set.
                $options[] = $tmp;
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

    protected function getInput()
    {
        $html = array();

        // Initialize some field attributes.
        $class          = !empty($this->class) ? ' class="checkboxes ' . $this->class . '"' : ' class="checkboxes"';
        $checkedOptions = explode(',', (string) $this->checkedOptions);
        $required       = $this->required ? ' required aria-required="true"' : '';
        $autofocus      = $this->autofocus ? ' autofocus' : '';

        // Including fallback code for HTML5 non supported browsers.
        JHtml::_('jquery.framework');
        JHtml::_('script', 'system/html5fallback.js', false, true);

        // Start the checkbox field output.
        $html[] = '<fieldset id="' . $this->id . '"' . $class . $required . $autofocus . '>';

        // Get the field options.
        $options = $this->getOptions();

        // Build the checkbox field output.
        $html[] = '<div class="btn-toolbar">
                <button class="btn" type="button" class="jform-rightbtn" onclick="jQuery(\'.chk-category\').attr(\'checked\', !jQuery(\'.chk-category\').attr(\'checked\'));">
                    <i class="icon-checkbox-partial"></i> '.JText::_('JGLOBAL_SELECTION_INVERT').'
                </button>
            </div>';

        foreach ($options as $i => $option)
        {
            // Initialize some option attributes.
            if (!isset($this->value) || empty($this->value))
            {
                $checked = (in_array((string) $option->value, (array) $checkedOptions) ? ' checked="checked"' : '');
            }
            else
            {
                $value = !is_array($this->value) ? explode(',', $this->value) : $this->value;
                $checked = (in_array((string) $option->value, $value) ? ' checked="checked"' : '');
            }

            $checked = empty($checked) && $option->checked ? ' checked="checked"' : $checked;

            $class = !empty($option->class) ? ' class="chk-category ' . $option->class . '"' : 'class="chk-category"';
            $disabled = !empty($option->disable) || $this->disabled ? ' disabled' : '';

            // Initialize some JavaScript option attributes.
            $onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
            $onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

            if($i % 4 == 0){
                $html[] = '<ul class="row-fluid">';
            }
            $html[] = '<li class="span3">';
            $html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '" value="'
                . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $onchange . $disabled . '/>';

            $html[] = '<label for="' . $this->id . $i . '"' . $class . '>' . JText::_($option->text) . '</label>';
                $html[] = '</li>';
            if($i % 4 == 3 || $i == (count($options) - 1)){
                $html[] = '</ul>';
            }
        }

        // End the checkbox field output.
        $html[] = '</fieldset>';

        return implode($html);
    }
}
?>