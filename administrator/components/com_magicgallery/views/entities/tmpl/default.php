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
?>
<form action="<?php echo JRoute::_('index.php?option=com_magicgallery&view=entities&gid='.(int)$this->galleryId); ?>" method="post" name="adminForm" id="adminForm">
    <?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
    <?php else : ?>
	<div id="j-main-container">
    <?php endif;?>
        <?php
        // Search tools bar
        echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
        ?>

        <table class="table table-striped" id="entitiesList">
           <thead><?php echo $this->loadTemplate('head');?></thead>
    	   <tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
    	   <tbody><?php echo $this->loadTemplate('body');?></tbody>
    	</table>
    
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="gid" value="<?php echo (int)$this->galleryId; ?>" />
        <?php echo JHtml::_('form.token'); ?>
        
    </div>
</form>