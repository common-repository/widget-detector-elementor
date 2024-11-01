<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wde-wrap">

    <h1 class="wp-heading-inline"><?php echo __('Used Elementor Widgets', 'w-d-e'); ?>
    <a href="<?php echo admin_url("admin.php?page=wde_used_widgets&function=export_csv_used_widgets_general"); ?>" class="export_csv page-title-action <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>"><span class="dashicons dashicons-download"></span>&nbsp;&nbsp;<?php echo __('Export CSV','w-d-e')?></a></h1>
    <br /><br />

    <ul class="subsubsub">
        <li><a href="<?php echo get_admin_url() . "admin.php?page=wde_used_widgets"; ?>"><?php echo __('Per Page', 'w-d-e'); ?></a> |</li>
        <li><a class="current" href="<?php echo get_admin_url() . "admin.php?page=wde_used_widgets&function=general"; ?>"><?php echo __('All in General', 'w-d-e'); ?></a></li>

    </ul>
    <br /><br />


<h2><?php echo __('Used Elementor Widgets on website', 'w-d-e'); ?></h1><br />


<p class="alert alert-info"><?php echo __('Showing results based on 100 pages/posts analyse', 'w-d-e'); ?></p><br />

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
                            <?php if (isset($widget) && is_object($widget)) echo $widget->get_title();
                            else echo __('Widget missing', 'w-d-e'); ?>
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

                <?php if (count($widgets_list) == 0) : ?>
                    <tr class="no-items">
                        <td class="colspanchange" colspan="4"><?php echo __('No data found.', 'w-d-e'); ?></td>
                    </tr>
                <?php endif; ?>
            </table>


</div>


<?php

//wp_enqueue_style('wde_basic_wrapper');

?>

<script>
    // Generate table
    jQuery(document).ready(function($) {


    });
</script>

<style>
    table.wp-list-table tr.red td {
        color: red;
    }
</style>

<?php $this->view('general/footer', $data); ?>