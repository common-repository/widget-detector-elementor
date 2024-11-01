<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wde-wrap">

    <h1 class="wp-heading-inline"><?php echo __('Used Elementor Widgets', 'w-d-e'); ?>
    <a href="<?php echo admin_url($export_url); ?>" class="export_csv page-title-action <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>"><span class="dashicons dashicons-download"></span>&nbsp;&nbsp;<?php echo __('Export CSV','w-d-e')?></a></h1>
    <br /><br />

    <ul class="subsubsub">
        <li><a class="current" href="<?php echo get_admin_url() . "admin.php?page=wde_used_widgets"; ?>"><?php echo __('Per Page', 'w-d-e'); ?></a> |</li>
        <li><a href="<?php echo get_admin_url() . "admin.php?page=wde_used_widgets&function=general"; ?>"><?php echo __('All in General', 'w-d-e'); ?></a></li>

    </ul>
    <br /><br />

    <form name="eli-filter" id="eli-filter" action="<?php echo get_admin_url() . "admin.php?page=wde_used_widgets"; ?>" method="get">
        <?php
        foreach ($_GET as $key => $value) {
            if (is_array($value)) continue;
            echo ("<input type='hidden' name='" . esc_attr(wmvc_xss_clean($key)) . "' value='" . esc_attr(wmvc_xss_clean($value)) . "'/>");
        }
        ?>

        <fieldset class="metabox-prefs wde_show_more">
            <legend><strong><?php echo __('EL Widget Categories (Will filter widgets, show all posts)', 'w-d-e'); ?></strong></legend>
            <?php foreach ($widget_categories as $category_key => $category_name) : ?>
                <label style="<?php echo isset($category_colors[$category_key])?'color:'.$category_colors[$category_key].';':'';  ?>"><input class="hide-column-tog" name="show_categories[]" type="checkbox" id="cat_<?php echo $category_key; ?>" value="<?php echo $category_key; ?>" <?php echo in_array($category_key, $show_categories) ? 'checked' : ''; ?>><?php echo esc_html($category_name); ?></label>
            <?php endforeach; ?>
        </fieldset>
        <fieldset class="metabox-prefs wde_show_more">
            <legend><strong><?php echo __('EL Widgets (Will filter posts and widgets)', 'w-d-e'); ?></strong></legend>
            <?php foreach ($widgets_exists_title as $widget_key_ar => $widget) : ?>
                <?php if (!empty($widget_key_ar)) :
                    $widget_key = $widget->get_name();
                    $widget_title = $widget->get_title();

                    if (empty($widget_title)) $widget_title = $widget_key;

                    $categories = $widget->get_categories();
                    $category_key = '';
                    if(isset($categories[0]))
                        $category_key = $categories[0];

                ?>
                    <label style="<?php echo isset($category_colors[$category_key])?'color:'.$category_colors[$category_key].';':'';  ?>"><input class="hide-column-tog" name="show_widgets[]" type="checkbox" id="wid_<?php echo $widget_key; ?>" value="<?php echo $widget_key; ?>" <?php echo in_array($widget_key, $show_widgets) ? 'checked' : ''; ?>><?php echo esc_html($widget_title); ?></label>
                <?php endif; ?>
            <?php endforeach; ?>
        </fieldset>
        <fieldset class="metabox-prefs wde_show_more">
            <legend><strong><?php echo __('Post types (Will filter posts)', 'w-d-e'); ?></strong></legend>
            <?php foreach ($post_types_available as $post_type_name => $post_type_label) : ?>
                <?php if (!empty($post_type_name)) :


                ?>
                    <label style=""><input class="hide-column-tog" name="show_post_types[]" type="checkbox" id="pt_<?php echo $post_type_name; ?>" value="<?php echo $post_type_name; ?>" <?php echo in_array($post_type_name, $show_post_types) ? 'checked' : ''; ?>><?php echo esc_html($post_type_label).', '.esc_html($post_type_name); ?></label>
                <?php endif; ?>
            <?php endforeach; ?>
        </fieldset>
        <fieldset class="metabox-prefs">
            <legend><strong><?php echo __('Text criteria (Will filter widgets, show all posts)', 'w-d-e'); ?></strong></legend>
            <input type="text" id="eli-search-input" name="s" value="<?php echo esc_attr(wmvc_show_data('s', $_GET, '')); ?>">
        </fieldset>
        <p class="filter-submit"><input type="submit" name="screen-options-apply" id="screen-options-apply" class="button button-primary <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>" value="<?php echo esc_attr(__('Filter', 'w-d-e')); ?>"></p>
    </form>

    <style>
        #eli-filter {
            background-color: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin: 0px;
            clear: both;
        }

        #eli-filter legend {
            padding: 5px 0px;
        }

        #eli-filter p.filter-submit {
            padding: 15px 0px 5px 0px;
            margin: 0px;
        }
    </style>



    <?php foreach ($posts_list as $key => $post) :

        $page = $post['post_data'];
        $widgets_list = $post['widgets_list'];

    ?>

        <h2><?php echo $page->post_title; ?> #<?php echo $page->ID; ?>, 
        <?php echo $page->post_type; ?><?php if (count($widgets_list) == 0) : ?> - <?php echo __('Widgets not found', 'w-d-e'); ?><?php endif; ?></h2>

        <?php if (count($widgets_list) > 0) : 
            
            $position_url = get_permalink( $page->ID );

            if(strpos($position_url, '?') === FALSE)
            {
                $position_url = $position_url. "?wde_show";
            }
            else
            {
                $position_url = $position_url. "&amp;wde_show";
            }
            
            ?>
        <a class="button" href="<?php echo get_admin_url() . "post.php?post=$page->ID&action=edit"; ?>"> <?php echo __('Edit Page', 'w-d-e'); ?></a>
        <a class="button" href="<?php echo get_admin_url() . "post.php?post=$page->ID&action=elementor"; ?>"> <?php echo __('Edit in Elementor', 'w-d-e'); ?></a>
        <a class="button" href="<?php echo esc_url( $position_url ); ?>"> <?php echo __('View position on page', 'w-d-e'); ?></a>
        <?php endif; ?>
        <br /><br />

        <?php if (count($widgets_list) > 0) : ?>
            <table class="wp-list-table widefat fixed striped table-view-list pages">
                <thead>
                    <tr>
                        <th><?php echo __('EL Widget Category', 'w-d-e'); ?></th>
                        <th><?php echo __('EL Widget Name', 'w-d-e'); ?></th>
                        <th><?php echo __('EL Widget Key', 'w-d-e'); ?></th>
                        <th><?php echo __('EL Widget Icon', 'w-d-e'); ?></th>
                        <th><?php echo __('Plugin','w-d-e'); ?></th>
                    </tr>
                </thead>

                <?php
                foreach ($widgets_list as $widget_key => $widget) :

                    $categories = array();
                    if (isset($widget) && is_object($widget))
                    {
                        $categories = $widget->get_categories();
                    }

                    $plugin_name = '';
                    if(isset($categories[0]) && isset($plugins_list[$categories[0].'-'.$widget->get_title()]))
                    {
                        $plugin_name = $plugins_list[$categories[0].'-'.$widget->get_title()];
                    }
                        
                ?>
                    <tr class="<?php if (!isset($widget) || !is_object($widget)) echo 'red missing' ?>">

                        <td>
                            <?php if (isset($categories[0]) && isset($widget)) : ?>
                                <?php echo $categories[0]; ?>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php 
                            if (isset($widget) && is_object($widget))
                            {
                                $widget_title = $widget->get_title();

                                if(isset($widget->custom_title))
                                {
                                    $widget_title = $widget->custom_title;
                                }

                                echo esc_html($widget_title);
                            }
                            else
                            {
                                echo __('Widget missing', 'w-d-e');
                            }
                            ?>
                        </td>

                        <td>
                            <?php if (isset($widget) && is_object($widget)) : ?>    
                            <?php echo $widget->get_name(); ?>
                            <?php elseif (isset($widget) && is_array($widget)) : ?> 
                            <?php echo $widget['key']; ?>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if (isset($widget) && is_object($widget)) : ?>
                                <i class="<?php echo $widget->get_icon(); ?>"></i>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php echo $plugin_name; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>


        <?php endforeach; ?>

        <div class="tablenav bottom">
            <div class="alignleft actions">
            </div>
            <?php echo $pagination_output; ?>
            <br class="clear">
        </div>

</div>


<?php

//wp_enqueue_style('wde_basic_wrapper');

?>

<script>
    // Generate table
    var wde_defined_limit = 20;

    jQuery(document).ready(function($) {
        $('.wde_show_more').each(function( index ) {
            var total_elements = $( this ).find('label').length;

            if(total_elements >= wde_defined_limit)
            {
                $(this).find('label:nth-child(n+'+Number(wde_defined_limit+2)+')').hide();
                $(this).append("<a class='wde_show_all' href='#'><?php echo _js(__('Show all...','w-d-e')); ?></a>");
                $(this).append("<a class='wde_hide_bit' href='#'><?php echo _js(__('Hide a bit..','w-d-e')); ?>.</a>");
                $(this).find('a.wde_hide_bit').hide();
                $(this).find('a.wde_show_all').on('click', function(){
                    $(this).parent().find('label').show();
                    $(this).parent().find('a.wde_show_all').hide();
                    $(this).parent().find('a.wde_hide_bit').show();
                    return false;
                });
                $(this).find('a.wde_hide_bit').on('click', function(){
                    $(this).parent().find('label:nth-child(n+'+Number(wde_defined_limit+2)+')').hide();
                    $(this).parent().find('a.wde_hide_bit').hide();
                    $(this).parent().find('a.wde_show_all').show();
                    return false;
                });

            }

        });
    });
</script>

<style>
    table.wp-list-table tr.red td {
        color: red;
    }
</style>

<?php $this->view('general/footer', $data); ?>