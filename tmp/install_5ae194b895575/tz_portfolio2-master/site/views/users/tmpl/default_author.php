<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

$authorParams   = $this -> authorParams;
$tmpl           = JRequest::getString('tmpl');
?>

<?php if($authorParams -> get('show_user',1)):?>
    <?php if($this -> listAuthor):?>
        <?php
            if($this -> listAuthor -> images){
                $images = JURI::root().$this -> listAuthor -> images;
            }
            else{
                $images = JURI::root().'components/com_tz_portfolio/assets/no_user.png';
            }
        

        ?>
        <div class="clr"></div>
        <div class="tz_portfolio_user tz_portfolio_clear">
            <h3 class="TzArticleAuthorTitle"><?php echo JText::_('ARTICLE_AUTHOR_TITLE'); ?>
                <?php
                    $target = '';
                    if(isset($tmpl) AND !empty($tmpl)):
                        $target = ' target="_blank"';
                    endif;
                ?>
                <?php if(!empty($this -> listAuthor -> twitter)): ?>
                    <a style="float: right; margin-left: 5px;"
                       href="<?php echo $this -> listAuthor -> twitter?>"<?php echo $target?>>
                        <img src="components/com_tz_portfolio/assets/twitter.png" width="16px"/>

                    </a>
                <?php endif;?>
                <?php if(!empty($this -> listAuthor -> facebook)):?>
                    <a style="float: right; margin-left: 5px;"
                       href="<?php echo $this -> listAuthor -> facebook;?>"<?php echo $target?>>
                        <img src="components/com_tz_portfolio/assets/facebook.png" width="16px"/>
                    </a>
                <?php endif;?>
                <?php if(!empty($this -> listAuthor -> google_one)):?>
                    <a style="float: right; margin-left: 5px;"
                       href="<?php echo $this -> listAuthor -> google_one?>"<?php echo $target?>>
                        <img src="components/com_tz_portfolio/assets/google_one.png" width="16px"/>
                    </a>
                <?php endif;?>
            </h3>
            <div class="AuthorBlock">
                    <img class="AuthorAvatar" src="<?php echo $images;?>" width="<?php echo $authorParams -> get('tz_user_image_width')?>"/>
                    <div class="AuthorDetails">
                        <h3 class="AuthorName" >
                            <a href="<?php echo JRoute::_('index.php?option=com_tz_portfolio&amp;view=users&amp;created_by='.$this -> listAuthor -> id.'&amp;Itemid='.JRequest::getCmd('Itemid'));?>"<?php echo $target?>>
                                <?php echo $this -> listAuthor -> name;?>
                            </a>
                        </h3>

                        <?php if($authorParams -> get('show_gender_user')):?>
                            <?php if($this -> listAuthor -> gender):?>
                                <span class="AuthorGender">
                                    <?php echo JText::_('Gender');?>:
                                    <span><?php if($this -> listAuthor -> gender == 'm'): echo JText::_('Male');?>
                                        <?php elseif($this -> listAuthor -> gender == 'f'): echo JText::_('Female');?>
                                        <?php endif;?>
                                    </span>
                                </span>
                            <?php endif;?>
                        <?php endif;?>

                        <?php if($authorParams -> get('show_email_user')):?>
                            <?php if($this -> listAuthor -> email):?>
                                <span class="AuthorEmail">
                                    <?php echo JText::_('COM_TZ_PORTFOLIO_EMAIL');?>
                                    <span>
                                        <?php echo $this -> listAuthor -> email;?>
                                    </span>
                                </span>
                            <?php endif;?>
                        <?php endif;?>
                        
                        <?php if($authorParams -> get('show_url_user',1) AND !empty($this -> listAuthor -> url)):?>
                            <span class="AuthorUrl">
                                <?php echo JText::_('COM_TZ_PORTFOLIO_WEBSITE');?>
                                <a href="<?php echo $this -> listAuthor -> url;?>" target="_blank">
                                    <?php echo $this -> listAuthor -> url;?>
                                </a>
                            </span>
                        <?php endif;?>

                        <?php if($authorParams -> get('show_description_user')  AND !empty($this -> listAuthor -> description)):?>
                            <?php echo $this -> listAuthor -> description?>
                        <?php endif;?>
                    </div>
                <div class="clr"></div>
            </div>
        </div>

    <?php endif;?>
<?php endif; ?>