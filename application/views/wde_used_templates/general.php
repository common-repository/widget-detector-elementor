<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wde-wrap">

    <h1 class="wp-heading-inline"><?php echo __('Used Elementor Templates', 'w-d-e'); ?></h1>

<h2><?php echo __('Used Elementor Templates inside Elementor based pages', 'w-d-e'); ?></h1><br />


<p class="alert alert-info"><?php echo __('Showing results based on 500 pages/posts analyse and based on shortcode [elementor-template...', 'w-d-e'); ?></p><br />

<p class="alert alert-danger"><?php echo __('Templates/Shortcodes used on other non Elementor pages will not be detected!', 'w-d-e'); ?></p><br />

            <table class="wp-list-table widefat fixed striped table-view-list pages">
                <thead>
                    <tr>
                        <th><?php echo __('Template ID', 'w-d-e'); ?></th>
                        <th><?php echo __('Template Title', 'w-d-e'); ?></th>
                    </tr>
                </thead>

                <?php 
                foreach ($templates_list as $templates_key => $template) :

                    $post = get_post($template);

                    if(is_null($post))continue;

                ?>
                    <tr>

                        <td>
                            <?php echo $template; ?>
                        </td>

                        <td>
                            <?php echo $post->post_title; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>

                <?php if (count($templates_list) == 0) : ?>
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