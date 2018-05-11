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
defined('_JEXEC') or die('Restricted access');

JHtml::_('dropdown.init');
JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$user		= JFactory::getUser();
$userId		= $user->get('id');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="index.php?option=com_tz_portfolio_plus&view=tags" method="post" name="adminForm" id="adminForm">
    <?php if(!empty($this -> sidebar)):?>
    <div id="j-sidebar-container" class="span2">
		<?php echo $this -> sidebar; ?>
	</div>
    <div id="j-main-container" class="span10">
    <?php else:?>
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
        <table class="table table-striped" id="tagsList">
            <thead>
            <tr>
                <th width="1%"><?php echo JText::_('#');?></th>
                <th width="1%" class="center">
                    <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                <th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
					</th>
                <th class="title">
                    <?php echo JHtml::_('searchtools.sort','JGLOBAL_TITLE','title', $listDirn, $listOrder);?>
                </th>
                <th nowrap="nowrap" width="1%">
                    <?php echo JHtml::_('searchtools.sort','JGRID_HEADING_ID','id', $listDirn, $listOrder);?>
                </th>
            </tr>
            </thead>

            <?php if($this -> items):?>
            <tbody>
            <?php
            $canEdit    = $user->authorise('core.edit',       'com_tz_portfolio_plus.tag');
            $canChange  = $user->authorise('core.edit.state', 'com_tz_portfolio_plus.tag');
            foreach($this -> items as $i => $item):?>
                <tr class="<?php echo ($i%2==0)?'row0':'row1';?>">
                    <td>
                        <?php echo $i+1;?>
                        <input type="hidden" name="order[]">
                    </td>
                    <td class="center">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td class="center">
                        <div class="btn-group">
                            <?php echo JHtml::_('jgrid.published', $item->published, $i, 'tags.', $canChange, 'cb'); ?>
                        </div>
                    </td>
                    <td class="nowrap has-context">
                        <div class="pull-left">
                            <?php if($canEdit){ ?>
                            <a href="index.php?option=com_tz_portfolio_plus&task=tag.edit&id=<?php echo $item -> id;?>">
                                <?php echo $this -> escape($item -> title);?>
                            </a>
                            <?php }else{ ?>
                                <?php echo $this -> escape($item -> title);?>
                            <?php } ?>
                            <span class="small">
                                <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                            </span>
                        </div>
                    </td>

                    <td align="center"><?php echo $item -> id;?></td>
                </tr>
            <?php endforeach;?>
            </tbody>
            <?php endif;?>

            <tfoot>
            <tr>
                <td colspan="11">
                    <?php echo $this -> pagination -> getListFooter();?>
                </td>
            </tr>
            </tfoot>

        </table>
        <?php } ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <input type="hidden" name="return" value="<?php echo base64_encode(JUri::getInstance() -> toString())?>">
        <?php echo JHtml::_('form.token');?>

        </div>
    </div>
</form>