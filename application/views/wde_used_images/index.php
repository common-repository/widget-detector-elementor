<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wde-wrap">

    <h1 class="wp-heading-inline"><?php echo __('Used Images inside Elementor', 'w-d-e'); ?></h1>
    <br /><br />

    <form name="eli-filter" id="eli-filter" action="<?php echo get_admin_url() . "admin.php?page=wde_used_images"; ?>" method="get">
        <?php
        foreach ($_GET as $key => $value) {
            if (is_array($value)) continue;
            echo ("<input type='hidden' name='" . esc_attr(wmvc_xss_clean($key)) . "' value='" . esc_attr(wmvc_xss_clean($value)) . "'/>");
        }
        ?>
        <fieldset class="metabox-prefs">
            <legend><strong><?php echo __('Posts/Page text filter criteria or ID', 'w-d-e'); ?></strong></legend>
            <input type="text" id="eli-search-input" name="s" value="<?php echo esc_attr(wmvc_show_data('s', $_GET, '')); ?>">
        </fieldset>
        <p class="filter-submit"><input type="submit" class="button button-primary" value="<?php echo esc_attr(__('Filter', 'w-d-e')); ?>"></p>
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
<br />
<p class="alert alert-info"><?php echo __('Some images if are not saved like urls in elementor structure will not be detected', 'w-d-e'); ?></p><br />
<p class="alert alert-info"><?php echo __('Unusual image filename means thats not lowercase, contain spacing, untitled, copy, screenshot words, what is not good for SEO and looks unprofesional', 'w-d-e'); ?></p><br />


    <?php foreach ($posts_list as $key => $post) :

        $page = $post['post_data'];
        $images_list = $post['images_list'];

    ?>

        <h2><?php echo $page->post_title; ?> #<?php echo $page->ID; ?>, 
        <?php echo $page->post_type; ?><?php if (count($images_list) == 0) : ?> - <?php echo __('Images not found', 'w-d-e'); ?><?php endif; ?></h2>

        <?php if (count($images_list) > 0) : 
            
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
        <a class="button" href="<?php echo esc_url( $position_url ); ?>"> <?php echo __('View page and element positions', 'w-d-e'); ?></a>
        <?php endif; ?>
        <br /><br />

        <?php if (count($images_list) > 0) : ?>
            <table class="wp-list-table widefat fixed striped table-view-list pages">
                <thead>
                    <tr>
                        <th><?php echo __('Image', 'w-d-e'); ?></th>
                        <th><?php echo __('File name', 'w-d-e'); ?></th>
                        <th><?php echo __('Size', 'w-d-e'); ?></th>
                        <th><?php echo __('Resolution', 'w-d-e'); ?></th>
                        <th><?php echo __('Messages', 'w-d-e'); ?></th>
                    </tr>
                </thead>

                <?php
                foreach ($images_list as $image_key => $image) :
                ?>
                    <tr class="<?php if (FALSE) echo 'red missing' ?>">

                        <td class="img-col">
                            <a href="<?php echo esc_url($image['url']); ?>" target="_blank"><img class="small_img" src="<?php echo esc_url($image['url']); ?>" alt="" /></a>
                        </td>

                        <td>
                            <?php echo esc_html($image['filename']); ?>
                        </td>

                        <td>
                            <?php echo esc_html($image['size']); ?>
                        </td>

                        <td>
                            <?php echo esc_html($image['resolution']); ?>
                        </td>

                        <td>
                        
                        <?php foreach($image['red_messages'] as $message): ?>
                            <span style="color:red;"><?php echo esc_html($message);?></span><br />                        
                        <?php endforeach; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>


        <?php endforeach; ?>

        <div class="tablenav bottom <?php if ( !function_exists('wdep_fs') || !wdep_fs()->is_plan_or_trial('widgetdetectorelementorpropro') ) echo 'wde-pro'; ?>">
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

    .small_img
    {
        max-width: 100px;
        max-height: 100px;
    }

    table.wp-list-table td.img-col a
    {
       display: inline-block;
        padding: 0px;
        margin: 0px;
    }
</style>

<?php $this->view('general/footer', $data); ?>