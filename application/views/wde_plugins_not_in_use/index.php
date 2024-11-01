<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wde-wrap">

<h1 class="wp-heading-inline"><?php echo __('Elementor Plugins usage on published posts/pages','w-d-e'); ?>
<a href="<?php echo admin_url("admin.php?page=wde_plugins_not_in_use&function=export_csv_plugins_not_in_use"); ?>" class="export_csv page-title-action <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>"><span class="dashicons dashicons-download"></span>&nbsp;&nbsp;<?php echo __('Export CSV','w-d-e')?></a></h1>
<br />
<p class="alert alert-info"><?php echo __('This tool only checking widgets usage time, so some noted plugins still may be used because of different reasons like extensions or other functionalities', 'w-d-e'); ?></p><br /><br />
<table class="wp-list-table widefat fixed striped table-view-list pages">
<thead>
	<tr>
        <th><?php echo __('EL Plugin Name','w-d-e'); ?></th>
        <th><?php echo __('Usage times','w-d-e'); ?></th>
    </tr>
</thead>

<?php if(count($plugins_list_used_time) == 0): ?>
    <tr class="no-items"><td class="colspanchange" colspan="4"><?php echo __('No data found.','w-d-e'); ?></td></tr>
<?php endif; ?>

<?php foreach ( $plugins_list_used_time as $plugin_key => $used_number ): ?>
<tr>

<td>
<?php
    $link = '';
    $widget_part = '';

    if( isset($plugins_list_categories[$plugin_key]) && 
        is_array($plugins_list_categories[$plugin_key]))
    {
        foreach($plugins_list_categories[$plugin_key] as $key => $cat)
        {
            $widget_part.='&show_categories['.$key.']='.$cat;
        }

        $link =  admin_url('admin.php?page=wde_used_widgets'.$widget_part);
    }
?>

<?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ): ?> 
    <?php echo esc_html($plugin_key); ?>
<?php else: ?>
    <a href="<?php echo esc_url($link); ?>" <?php echo $used_number==0?'style="color:red;"': '';?>><?php echo esc_html($plugin_key); ?></a>
<?php endif; ?>
</td>

<td>

<?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ): ?> 
    <a href="#" class="export_csv page-title-action <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>"><?php echo __('Show number','w-d-e')?></a>
<?php else: ?>
    <?php echo esc_html($used_number); ?>
<?php endif; ?>
</td>

</tr>
<?php endforeach; ?>
</table>

</div>

<?php $this->view('general/footer', $data); ?>
