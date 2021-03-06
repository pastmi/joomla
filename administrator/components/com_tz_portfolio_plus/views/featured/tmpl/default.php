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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', '.multipleMediaType', null,
    array('placeholder_text_multiple' => JText::_('COM_TZ_PORTFOLIO_PLUS_OPTION_SELECT_MEDIA_TYPE')));
JHtml::_('formbehavior.chosen', '.multipleAuthors', null,
    array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_AUTHOR')));
JHtml::_('formbehavior.chosen', '.multipleAccessLevels', null,
    array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_ACCESS')));
JHtml::_('formbehavior.chosen', '.multipleCategories', null,
    array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_CATEGORY')));
JHtml::_('formbehavior.chosen', 'select');

$user		= JFactory::getUser();
        $userId		= $user->get('id');
        $listOrder	= $this->escape($this->state->get('list.ordering'));
        $listDirn	= $this->escape($this->state->get('list.direction'));
        $archived	= $this->state->get('filter.published') == 2 ? true : false;
        $trashed	= $this->state->get('filter.published') == -2 ? true : false;
        $saveOrder	= $listOrder == 'a.ordering';
        if ($saveOrder)
        {
            $saveOrderingUrl = 'index.php?option=com_tz_portfolio_plus&task=articles.saveOrderAjax&tmpl=component';
            JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
        }
        ?>
<script type="text/javascript">
    Joomla.orderTable = function() {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_tz_portfolio_plus&view=featured');?>" method="post" name="adminForm" id="adminForm">
    <?php if(!empty( $this->sidebar) AND COM_TZ_PORTFOLIO_PLUS_JVERSION_COMPARE): ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
    <div id="j-main-container">
    <?php endif;?>
        <div class="tpContainer">
            <?php
            // Search tools bar
            echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
            ?>

            <?php if (empty($this->items)){ ?>
                <div class="alert alert-no-items">
                    <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
            <?php }else{ ?>
            <table class="table table-striped" id="articleList">
                <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                        </th>
                        <th width="1%" class="hidden-phone">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th width="1%" style="min-width:55px" class="nowrap center">
                            <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                        <th>
                            <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="6%" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_TZ_PORTFOLIO_PLUS_TYPE_OF_MEDIA', 'a.type', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_TZ_PORTFOLIO_PLUS_GROUP', 'groupname', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
                </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($this->items as $i => $item) :
                        $item->max_ordering = 0; //??
                        $ordering	= ($listOrder == 'a.ordering');
                        $canCreate	= $user->authorise('core.create',		'com_tz_portfolio_plus.category.'.$item->catid);
                        $canEdit	= $user->authorise('core.edit',			'com_tz_portfolio_plus.article.'.$item->id);
                        $canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                        $canEditOwn	= $user->authorise('core.edit.own',		'com_tz_portfolio_plus.article.'.$item->id) && $item->created_by == $userId;
                        $canChange	= $user->authorise('core.edit.state',	'com_tz_portfolio_plus.article.'.$item->id) && $canCheckin;
                        ?>
                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid?>">
                            <td class="order nowrap center hidden-phone">
                            <?php if ($canChange) :
                                $disableClassName = '';
                                $disabledLabel	  = '';

                                if (!$saveOrder) :
                                    $disabledLabel    = JText::_('JORDERINGDISABLED');
                                    $disableClassName = 'inactive tip-top';
                                endif; ?>
                                <span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
                                    <i class="icon-menu"></i>
                                </span>
                                <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
                            <?php else : ?>
                                <span class="sortable-handler inactive" >
                                    <i class="icon-menu"></i>
                                </span>
                            <?php endif; ?>
                            </td>

                            <td class="center">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="center">
                                <div class="btn-group">
                                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'articles.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                                    <?php echo JHtml::_('contentadministrator.featured', $item->featured, $i, $canChange); ?>
                                    <?php // Create dropdown items and render the dropdown list.
                                    if ($canChange)
                                    {
                                        JHtml::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'articles');
                                        echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="nowrap has-context">
                                <div class="pull-left">
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'articles.', $canCheckin); ?>
                                    <?php endif; ?>
                                    <?php if ($canEdit || $canEditOwn) : ?>
                                        <a href="<?php echo JRoute::_('index.php?option=com_tz_portfolio_plus&task=article.edit&id='.$item->id);?>">
                                            <?php echo $this->escape($item->title); ?></a>
                                    <?php else : ?>
                                        <?php echo $this->escape($item->title); ?>
                                    <?php endif; ?>
                                    <span class="small">
                                        <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
                                    </span>
                                    <div class="small">
                                        <div class="clearfix">
                                            <?php echo JText::_('COM_TZ_PORTFOLIO_PLUS_MAIN_CATEGORY') . ": " ?>
                                            <a href="index.php?option=com_tz_portfolio_plus&task=category.edit&id=<?php echo $item -> catid;?>"><?php echo $this->escape($item->category_title); ?></a>
                                        </div>
                                        <?php if(isset($item -> categories) && $item -> categories && count($item -> categories)):?>
                                            <div class="clearfix">
                                                <?php echo JText::_('COM_TZ_PORTFOLIO_PLUS_SECONDARY_CATEGORY') . ": " ?>
                                                <?php foreach($item -> categories as $i => $category):?>
                                                    <a href="index.php?option=com_tz_portfolio_plus&task=category.edit&id=<?php echo $category -> id;?>"><?php echo $this->escape($category->title); ?></a>
                                                    <?php
                                                    if($i < count($item -> categories) - 1){
                                                        echo ',';
                                                    }
                                                    ?>
                                                <?php endforeach;?>
                                            </div>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $item -> type;?>
                            </td>
                            <td class="small hidden-phone">
                                    <a href="index.php?option=com_tz_portfolio_plus&view=groups&task=edit&id=<?php echo $item -> groupid?>">
                                        <?php echo $item -> groupname;?>
                                    </a>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $this->escape($item->access_level); ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $this->escape($item->author_name); ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php if ($item->language=='*'):?>
                                    <?php echo JText::alt('JALL', 'language'); ?>
                                <?php else:?>
                                    <?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
                                <?php endif;?>
                            </td>
                            <td class="small nowrap hidden-phone">
                                <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td class="center">
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>

            </table>
            <?php } ?>
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="featured" value="1" />
            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="return" value="<?php echo base64_encode(JUri::getInstance() -> toString())?>">
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>
