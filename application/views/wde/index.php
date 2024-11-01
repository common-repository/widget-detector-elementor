<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wde-wrap">

<h1 class="wp-heading-inline"><?php echo __('Installed Elementor Widgets','w-d-e'); ?>
<a href="<?php echo admin_url("admin.php?page=wde&function=export_csv_installed"); ?>" class="export_csv page-title-action <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>"><span class="dashicons dashicons-download"></span>&nbsp;&nbsp;<?php echo __('Export CSV','w-d-e')?></a></h1>
<br /><br />
<table class="wp-list-table widefat fixed striped table-view-list pages">
<thead>
	<tr>
		<th><?php echo __('EL Widget Category','w-d-e'); ?></th>
        <th><?php echo __('EL Widget Name','w-d-e'); ?></th>
        <th><?php echo __('EL Widget Key','w-d-e'); ?></th>
        <th><?php echo __('EL Widget Icon','w-d-e'); ?></th>
        <th><?php echo __('Plugin','w-d-e'); ?></th>
    </tr>
</thead>

<?php if(count($existing_widgets) == 0): ?>
    <tr class="no-items"><td class="colspanchange" colspan="4"><?php echo __('No data found.','w-d-e'); ?></td></tr>
<?php endif; ?>

<?php foreach ( $existing_widgets as $widget_key => $widget ): 
    $categories = $widget->get_categories();

    $plugin_name = '';
    if(isset($categories[0]) && isset($plugins_list[$categories[0].'-'.$widget->get_title()]))
    {
        $plugin_name = $plugins_list[$categories[0].'-'.$widget->get_title()];
    }
    
?>
<tr>

<td>
<?php if(isset($categories[0])): ?>
    <?php echo $categories[0]; ?>
<?php endif; ?>
</td>

<td>
<?php echo $widget->get_title(); ?>
</td>

<td>
<?php echo $widget_key; ?>
</td>

<td>
<i class="<?php echo $widget->get_icon(); ?>"></i>
</td>

<td>
<?php echo $plugin_name; ?>
</td>

</tr>
<?php endforeach; ?>
</table>

</div>

<script>
 
// Generate table
jQuery(document).ready(function($) {


});

</script>


<style>
</style>

<?php $this->view('general/footer', $data); ?>
