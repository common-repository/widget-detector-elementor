<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wde-wrap">

<h1 class="wp-heading-inline"><?php echo __('Elementor Widgets not used on published posts/pages','w-d-e'); ?>
<a href="<?php echo admin_url("admin.php?page=wde_not_in_use&function=export_csv_not_in_use"); ?>" class="export_csv page-title-action <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>"><span class="dashicons dashicons-download"></span>&nbsp;&nbsp;<?php echo __('Export CSV','w-d-e')?></a></h1>
<br /><br />
<p class="alert alert-info"><?php echo __('* With Hide Element will be only hidden in Elementor.','w-d-e'); ?></p>
<p class="alert alert-info"><?php echo __('* With Unregister Element will be unregistered from memory to reduce memory usage on server.','w-d-e'); ?></p>
<br />
<table class="wp-list-table widefat fixed striped table-view-list pages">
<thead>
	<tr>
        <th><?php echo __('EL Widget Category','w-d-e'); ?></th>
        <th><?php echo __('EL Widget Name','w-d-e'); ?></th>
        <th><?php echo __('EL Widget Key','w-d-e'); ?></th>
        <th><?php echo __('EL Widget Icon','w-d-e'); ?></th>
        <th><?php echo __('Plugin','w-d-e'); ?></th>
        <th>
            <label class="field-switch">
                <input class="toggle-checkbox wde_hidder_el_all" type="checkbox">
                <span class="toggle-switch"></span>
                <span class="toggle-label"><?php echo __('Hide','w-d-e'); ?></span>
            </label>
        </th>
        <th>
            <label class="field-switch <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>">
                <input class="toggle-checkbox wde_unregister_el_all <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>" type="checkbox">
                <span class="toggle-switch"></span>
                <span class="toggle-label"><?php echo __('Unregister','w-d-e'); ?></span>
            </label>
        </th>
    </tr>
</thead>

<?php if(count($widgets_not_used) == 0): ?>
    <tr class="no-items"><td class="colspanchange" colspan="4"><?php echo __('No data found.','w-d-e'); ?></td></tr>
<?php endif; ?>

<?php foreach ( $widgets_not_used as $widget_key => $widget ): 
    $categories = $widget->get_categories();


    $plugin_name = '';
    if(isset($categories[0]) && isset($plugins_list[$categories[0].'-'.$widget->get_title()]))
    {
        $plugin_name = $plugins_list[$categories[0].'-'.$widget->get_title()];
    }
?>
<tr class="<?php if(isset($disabled_widgets[$widget->get_name()]))echo 'red-row'; ?>">

<td>
<?php if(isset($categories[0])): ?>
    <?php echo $categories[0]; ?>
<?php endif; ?>
</td>

<td>
<?php echo $widget->get_title(); ?>
</td>

<td>
<?php echo $widget->get_name(); ?>
</td>

<td>
<i class="<?php echo $widget->get_icon(); ?>"></i>
</td>

<td>
<?php echo $plugin_name; ?>
</td>

<td>
    <?php if($widget->get_name() != 'common'):?>
    <label class="field-switch">
        <input class="toggle-checkbox wde_hidder_el " type="checkbox" data-el="<?php echo esc_attr($widget->get_name()); ?>" <?php if(isset($hidder_elements[$widget->get_name()])):?> checked="checked"<?php endif;?>>
        <span class="toggle-switch"></span>
        <span class="toggle-label"></span>
    </label>
    <?php endif;?>
</td>

<td>
    <?php if($widget->get_name() != 'common'):?>
    <label class="field-switch <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>">
        <input class="toggle-checkbox wde_unregister_el <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>" type="checkbox" data-el="<?php echo esc_attr($widget->get_name()); ?>" <?php if(isset($unregister_elements[$widget->get_name()])):?> checked="checked"<?php endif;?>>
        <span class="toggle-switch"></span>
        <span class="toggle-label"></span>
    </label>
    <?php endif;?>
</td>

</tr>
<?php endforeach; ?>
</table>

<script>

jQuery(document).on('ready', function(){

    wde_hide_elements();
    wde_unregister_elements();
});


const wde_hide_elements = () => {
	/* ajax query, do step by query finish */
	var ajaxQueue = jQuery({});
	jQuery.ajaxQueue = function(ajaxOpts) {
	  // hold the original complete function
	  var oldComplete = ajaxOpts.complete;
  
	  // queue our ajax request
	  ajaxQueue.queue(function(next) {    
  
		// create a complete callback to fire the next event in the queue
		ajaxOpts.complete = function() {
		  // fire the original complete if it was there
		  if (oldComplete) oldComplete.apply(this, arguments);    
		  next(); // run the next query in the queue
		};
  
		// run the query
		jQuery.ajax(ajaxOpts);
	  });
	};

    var _check_all_status = () => {
        var all_checked = true;
        jQuery('.wde_hidder_el').each(function(){
            if(!jQuery(this).prop('checked')) {
                all_checked = false;
                return;
            }
        });

        if(all_checked) {
            jQuery('.wde_hidder_el_all').prop('checked', 'checked')
        } else {
            jQuery('.wde_hidder_el_all').prop('checked', false)
        }
    };

    _check_all_status();
    jQuery('.wde_hidder_el').on('input', function(e){
        if(jQuery(this).prop('checked')) {
            toogle_hidder(jQuery(this), jQuery(this).attr('data-el'), 1);
        } else {
            toogle_hidder(jQuery(this), jQuery(this).attr('data-el'), 0);
        }
        _check_all_status();
    })

    jQuery('.wde_hidder_el_all').on('input', function(){
        var el_names = '';
        jQuery('.wde_hidder_el').each(function(){
            el_names += jQuery(this).attr('data-el')+',';
        });

        if(jQuery(this).prop('checked')) {
            toogle_hidder(jQuery(this), el_names, 1);
            jQuery('.wde_hidder_el').prop('checked', 'checked')
        } else {
            toogle_hidder(jQuery(this), el_names, 0);
            jQuery('.wde_hidder_el').prop('checked', false)
        }
    })

	var toogle_hidder = (el, el_name, set_status) => {

        data = [];
        data.push({ name: 'action', value: "elementdetector_action" });
        data.push({ name: 'page', value: "wde_ajax" });
        data.push({ name: 'function', value: "hidder_el" });
        data.push({ name: '_wpnonce', value: "<?php echo wp_create_nonce('wde_hidder_el');?>" });
        data.push({ name: 'el_name', value: el_name});
        data.push({ name: 'set_status', value: set_status});

        el.closest('.field-switch').addClass('loading');

        jQuery.ajaxQueue({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: data,
            success: function(data){

                /*
                if(data.popup_text_success)
                    wdk_log_notify(data.popup_text_success);
                
                if(data.popup_text_error)
                    wdk_log_notify(data.popup_text_error, 'error');
                */ 

                if(data.success)
                {
                } else {
                    
                }

                el.closest('.field-switch').removeClass('loading');
            }
        });
	}
}

const wde_unregister_elements = () => {
	/* ajax query, do step by query finish */
	var ajaxQueue = jQuery({});
	jQuery.ajaxQueue = function(ajaxOpts) {
	  // hold the original complete function
	  var oldComplete = ajaxOpts.complete;
  
	  // queue our ajax request
	  ajaxQueue.queue(function(next) {    
  
		// create a complete callback to fire the next event in the queue
		ajaxOpts.complete = function() {
		  // fire the original complete if it was there
		  if (oldComplete) oldComplete.apply(this, arguments);    
		  next(); // run the next query in the queue
		};
  
		// run the query
		jQuery.ajax(ajaxOpts);
	  });
	};

    var _check_all_status = () => {
        var all_checked = true;
        jQuery('.wde_unregister_el').each(function(){
            if(!jQuery(this).prop('checked')) {
                all_checked = false;
                return;
            }
        });

        if(all_checked) {
            jQuery('.wde_unregister_el_all').prop('checked', 'checked')
        } else {
            jQuery('.wde_unregister_el_all').prop('checked', false)
        }
    };
    _check_all_status();

    jQuery('.wde_unregister_el').on('input', function(e){
        if(jQuery(this).prop('checked')) {
            toogle_hidder(jQuery(this), jQuery(this).attr('data-el'), 1);
        } else {
            toogle_hidder(jQuery(this), jQuery(this).attr('data-el'), 0);
        }
        _check_all_status();
    })

    
    jQuery('.wde_unregister_el_all').on('input', function(){
        var el_names = '';
        jQuery('.wde_unregister_el').each(function(){
            el_names += jQuery(this).attr('data-el')+',';
        });

        if(jQuery(this).prop('checked')) {
            toogle_hidder(jQuery(this), el_names, 1);
            jQuery('.wde_unregister_el').prop('checked', 'checked')
        } else {
            toogle_hidder(jQuery(this), el_names, 0);
            jQuery('.wde_unregister_el').prop('checked', false)
        }
    })

	var toogle_hidder = (el, el_name, set_status) => {

        data = [];
        data.push({ name: 'action', value: "elementdetector_action" });
        data.push({ name: 'page', value: "wde_ajax" });
        data.push({ name: 'function', value: "unregister_el" });
        data.push({ name: '_wpnonce', value: "<?php echo wp_create_nonce('wde_unregister_el');?>" });
        data.push({ name: 'el_name', value: el_name});
        data.push({ name: 'set_status', value: set_status});

        el.closest('.field-switch').addClass('loading');

        jQuery.ajaxQueue({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: data,
            success: function(data){

                /*
                if(data.popup_text_success)
                    wdk_log_notify(data.popup_text_success);
                
                if(data.popup_text_error)
                    wdk_log_notify(data.popup_text_error, 'error');
                */ 

                if(data.success)
                {
                } else {
                    
                }

                el.closest('.field-switch').removeClass('loading');
            }
        });
	}
}

</script>

</div>

<?php $this->view('general/footer', $data); ?>
