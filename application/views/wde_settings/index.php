<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wde-wrap">

    <h1 class="wp-heading-inline"><?php echo __('Settings', 'w-d-e'); ?></h1>
    <br /><br />

    <div class="wde-body">

        <form method="post" action="" novalidate="novalidate">

            <div class="postbox" style="display: block;">
                <div class="postbox-header">
                    <h3><?php echo __('General Settings', 'w-d-e'); ?></h3>
                </div>
                <div class="inside">
                <?php

                $form->messages();

                if (isset($_GET['is_updated'])) {
                    echo '<p class="alert alert-success">' . __('Successfuly saved', 'w-d-e') . '</p>';
                }

                //dump($db_data);
                ?>
                <?php echo wde_generate_fields($fields, $db_data); ?>                   
                </div>
            </div>
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
        </form>
    </div>

</div>

<script>
    // Generate table
    jQuery(document).ready(function($) {


    });
</script>

<?php
//wp_enqueue_script( 'jquery-ui-datepicker' );
//wp_enqueue_style('jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', false, null );
?>

<style>


</style>

<?php $this->view('general/footer', $data); ?>