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

defined('_JEXEC') or die();

$doc    = &JFactory::getDocument();
 JFactory::getLanguage()->load('com_content');
JFactory::getLanguage()->load('com_tz_portfolio');
?>

<?php if($this -> listsArticle):?>

    <?php
    $params = &$this -> params;
    ?>
            <?php $i=0;?>
            <?php foreach($this -> listsArticle as $row):?>
                <?php
                    $tmpl   = null;
                    if($params -> get('tz_use_lightbox',1) == 1){
                        $tmpl   = '&tmpl=component';
                    }

                    $tzRedirect = $params -> get('tz_portfolio_redirect','p_article'); //Set params for $tzRedirect
                    $itemParams = new JRegistry($row -> attribs); //Get Article's Params
                    //Check redirect to view article
                    if($itemParams -> get('tz_portfolio_redirect')){
                        $tzRedirect = $itemParams -> get('tz_portfolio_redirect');
                    }
                    if($tzRedirect == 'article'){
                        $row ->link = JRoute::_(TZ_PortfolioHelperRoute::getArticleRoute($row -> slug, $row -> catid).$tmpl);
                    }
                    else{
                        $row ->link = JRoute::_(TZ_PortfolioHelperRoute::getPortfolioArticleRoute($row -> slug, $row -> catid).$tmpl);
                    }
                    if($params -> get('tz_column_width',230))
                        $tzItemClass    = ' tz_item';
                    else
                        $tzItemClass    = null;
                ?>
                <?php
                    if($row -> featured == 1)
                        $tzItemFeatureClass    = ' tz_feature_item';
                    else
                        $tzItemFeatureClass    = null;

                    $class  = null;
                    if($params -> get('tz_filter_type','tags') == 'tags'){
                        $class  = $row -> tagName;
                    }
                    elseif($params -> get('tz_filter_type','tags') == 'categories'){
                        $class  = 'category'.$row -> catid;
                    }
                ?>
                <div class="element <?php echo $class.$tzItemClass.$tzItemFeatureClass;?>">
                    <div class="TzInner">
                        <?php
                         if($params -> get('show_image',1) == 1 OR $params -> get('show_image_gallery',1) == 1
                                 OR $params -> get('show_video',1) == 1):
                        ?>
                            <?php
                                $media          = &JModel::getInstance('Media','TZ_PortfolioModel');
                                $mediaParams    = $this -> mediaParams;
                                $mediaParams -> merge($media -> getCatParams($row -> catid));

                                $media -> setParams($mediaParams);
                                $listMedia      = $media -> getMedia($row -> id);

                                $this -> assign('listMedia',$listMedia);
                                $this -> assign('itemLink',$row ->link);

                                echo $this -> loadTemplate('media');

                            ?>
                        <?php endif;?>

                        <div class="TzPortfolioDescription">
                        <?php if($params -> get('show_title',1)): ?>
                            <h3 class="TzPortfolioTitle name">
                                <?php if($params->get('link_titles',1)) : ?>
                                    <a<?php if($params -> get('tz_use_lightbox') == 1){echo ' class="fancybox fancybox.iframe"';}?>
                                            href="<?php echo $row ->link; ?>">
                                        <?php echo $this->escape($row -> title); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo $this->escape($row -> title); ?>
                                <?php endif; ?>
                            </h3>
                        <?php endif;?>

                        <?php echo $row -> event -> beforeDisplayContent; ?>

                        <?php if (($params->get('show_author',1)) or ($params->get('show_category',1)) or ($params->get('show_create_date',1)) or ($params->get('show_modify_date',1)) or ($params->get('show_publish_date',1)) or ($params->get('show_parent_category',1)) or ($params->get('show_hits',1))) : ?>
                            <div class="TzArticle-info">
                        <?php endif; ?>

                        <?php if ($params->get('show_category',1)) : ?>
                        <span class="TZcategory-name">
                            <?php $title = $this->escape($row->category_title);
                            $url = '<a href="' . JRoute::_(TZ_PortfolioHelperRoute::getCategoryRoute($row->catid)) . '">' . $title . '</a>'; ?>
                            <?php if ($params->get('link_category',1)) : ?>
                            <?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
                            <?php else : ?>
                            <?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($params->get('show_create_date',1)) : ?>
                        <span class="TzPortfolioDate" data-date="<?php echo strtotime($row -> created); ?>">
                            <?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHtml::_('date', $row->created, JText::_('DATE_FORMAT_LC2'))); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($params->get('show_modify_date')) : ?>
                        <span class="TzPortfolioModified">
                            <?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $row->modified, JText::_('DATE_FORMAT_LC2'))); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($params->get('show_publish_date',1)) : ?>
                        <span class="published">
                            <?php echo JText::sprintf('COM_CONTENT_PUBLISHED_DATE_ON', JHtml::_('date', $row->publish_up, JText::_('DATE_FORMAT_LC2'))); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($params->get('show_author') && !empty($row->author )) : ?>
                        <span class="TzPortfolioCreatedby">
                            <?php $author =  $row->author; ?>
                            <?php $author = ($row->created_by_alias ? $row->created_by_alias : $author);?>

                            <?php if ($params->get('link_author') == true):?>
                            <?php 	echo JText::sprintf('COM_CONTENT_WRITTEN_BY' ,
                                JHtml::_('link', JRoute::_('index.php?option=com_tz_portfolio&amp;view=users&amp;created_by='.$row -> created_by), $author)); ?>

                            <?php else :?>
                            <?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($params->get('show_hits')) : ?>
                        <span class="TzPortfolioHits">
                            <?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $row->hits); ?>
                        </span>
                        <?php endif; ?>
                        <?php if($params -> get('tz_show_count_comment',1) == 1):?>
                            <span class="TzPortfolioCommentCount">
                                <?php echo JText::_('COM_TZ_PORTFOLIO_COMMENT_COUNT');?>
                                <?php if($params -> get('tz_comment_type') == 'facebook'): ?>
                                    <?php if(isset($row -> commentCount)):?>
                                        <?php echo $row -> commentCount;?>
                                    <?php endif;?>
                                <?php endif;?>

                                <?php if($params -> get('tz_comment_type') == 'jcomment'): ?>
                                    <?php
                                        $comments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
                                        if (file_exists($comments)){
                                            require_once($comments);
                                            if(class_exists('JComments')){
                                                 echo JComments::getCommentsCount((int) $row -> id,'com_tz_portfolio');
                                            }
                                        }
                                    ?>
                                <?php endif;?>
                                <?php if($params -> get('tz_comment_type') == 'disqus'):?>
                                    <?php if(isset($row -> commentCount)):?>
                                        <?php echo $row -> commentCount;?>
                                    <?php endif;?>
                                <?php endif;?>
                            </span>
                        <?php endif;?>

                        <?php
                            $extraFields    = &JModel::getInstance('ExtraFields','TZ_PortfolioModel',array('ignore_request' => true));
                            $extraFields -> setState('article.id',$row -> id);
                            $extraFields -> setState('category.id',$row -> catid);
                            $extraFields -> setState('orderby',$params -> get('fields_order'));

                            $extraParams    = $extraFields -> getParams();
                            $itemParams     = new JRegistry($row -> attribs);

                            if($itemParams -> get('tz_fieldsid'))
                                $extraParams -> set('tz_fieldsid',$itemParams -> get('tz_fieldsid'));

                            $extraFields -> setState('params',$extraParams);
                            $this -> item -> params = $extraParams;
                            $this -> assign('listFields',$extraFields -> getExtraFields());
                        ?>
                        <?php echo $this -> loadTemplate('extrafields');?>

                        <?php if (($params->get('show_author',1)) or ($params->get('show_category',1)) or ($params->get('show_create_date',1)) or ($params->get('show_modify_date',1)) or ($params->get('show_publish_date',1)) or ($params->get('show_parent_category',1)) or ($params->get('show_hits',1))) :?>
                            </div>
                        <?php endif; ?>

                        <div class="TzPortfolioIntrotext">
                            <?php  if (!$params->get('show_intro',1)) :
                                echo $row->event->afterDisplayTitle;
                            else:
                                echo $row -> text;
                            endif; ?>
                        </div>
                        <a class="TzPortfolioReadmore<?php if($params -> get('tz_use_lightbox') == 1){echo ' fancybox fancybox.iframe';}?>" href="<?php echo $row ->link; ?>">
                            <?php echo JText::sprintf('COM_TZPORTFOLIO_READMORE'); ?>
                        </a>
                        </div>
                    </div><!--Inner-->
                </div>
                <?php echo $row->event->afterDisplayContent; ?>

                <?php $i++;?>
            <?php endforeach;?>

<?php endif;?>