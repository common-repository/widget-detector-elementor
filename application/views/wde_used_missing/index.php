<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wde-wrap">

<h1 class="wp-heading-inline"><?php echo __('Elementor Widgets used on published posts/pages but missing','w-d-e'); ?></h1>
<br /><br />
<table class="wp-list-table widefat fixed striped table-view-list pages">
<thead>
	<tr>
		<th><?php echo __('EL Widget Key','w-d-e'); ?></th>
    </tr>
</thead>

<?php if(count($widgets_missing) == 0): ?>
    <tr class="no-items"><td class="colspanchange" colspan="1"><?php echo __('No data found.','w-d-e'); ?></td></tr>
<?php endif; ?>

<?php foreach ( $widgets_missing as $widget_key => $posts ): 

?>
<tr>


<td>
<?php echo $widget_key; ?>
</td>



</tr>
<?php endforeach; ?>
</table>

<br style="clear:both;" />


<a href="#" id="sync-plugin-data" class="update-now button"><?php echo __('Get Widgets and Plugins Info','w-d-e'); ?></a>

<div id="log_place">


</div>

</div>

<script>
 
// Generate table
jQuery(document).ready(function($) {
    $('#sync-plugin-data').on('click', function(){
        wde_sync_data();
    });

    wde_sync_data();

    function wde_sync_data()
    {
        $('#sync-plugin-data').addClass('animate');

        var widget_keys = [];
        <?php foreach($widgets_missing as $key=>$det): ?>
        widget_keys.push('<?php echo esc_js($key); ?>');
        <?php endforeach; ?>

        $('div#log_place').html('');

        // Assign handlers immediately after making the request,
        // and remember the jqxhr object for this request
        var jqxhr = $.post( "<?php echo admin_url('admin-ajax.php?action=elementdetector_action&function=sync_plugins&page=wde_ajax'); ?>", {'widget_keys': widget_keys}, function(data) {

            if(data.status == 'success')
            {
                $('div#log_place').append(data.log);
            }
            else
            {
                $('div#log_place').append(data + '<br />');
            }
        })
        .done(function(data) {
            //alert( "second success" );
        })
        .fail(function(data) {
            $('div#log_place').append("Error: " + data + '<br />');
        })
        .always(function(data) {
            //alert( "finished" );
            $('#sync-plugin-data').removeClass('animate');
        });
    }
});

</script>

<style>

a#sync-plugin-data::before {
    color: #f56e28;
    content: "\f463";
    display: inline-block;
    font: normal 20px/1 dashicons;
    margin: 3px 5px 0 -2px;
    speak: none;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    vertical-align: top;
}

a.animate#sync-plugin-data::before {
    content: "\f463";
    animation: rotation 2s infinite linear;
}

div#log_place
{
    padding:15px 0px;

    color: blue;
}

</style>

<?php $this->view('general/footer', $data); ?>
