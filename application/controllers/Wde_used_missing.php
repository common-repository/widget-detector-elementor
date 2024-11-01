<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Wde_used_missing extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
        global $wpdb;

        // get existing widgets
        
        $elements_manager = \Elementor\Plugin::instance()->widgets_manager;
        $widgets_exists = $elements_manager->get_widget_types();

        $widgets_used = array();

        // get all posts

        $sql = "SELECT * FROM $wpdb->posts JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data' ORDER BY $wpdb->posts.ID"; 

        $posts = $wpdb->get_results($sql);

        foreach ( $posts as $page ) {

            // parse elementor json and found all widgets in json

            $elementor_data = get_post_meta( $page->ID, '_elementor_data' );

            if(isset($elementor_data[0]))
            {
                $regExp = '/"widgetType":"([^"]*)/i';
                $outputArray = array();
                
                if(!is_array($elementor_data) || !is_string($elementor_data[0]))
                {

                    if(is_array($elementor_data[0]))
                    {
                        $elementor_data[0] = json_encode($elementor_data[0]);
                    }
                    else
                    {
                        echo '<div class="notice notice-warning"><p>'._('Parsing elementor data issue on page:').' <a href="'.get_admin_url().'post.php?post='.$page->ID.'&action=edit">#'.$page->ID.', '.$page->post_title.'</a></p></div>';

                    }
                }

                if ( preg_match_all($regExp, $elementor_data[0], $outputArray, PREG_SET_ORDER) ) {
                }

                foreach($outputArray as $found)
                {
                    $widgets_used[$found[1]][$page->ID] = $page->post_title." #$page->ID";
                }
            }   
        }

        // filter only widgets missing

        $widgets_missing = array();

		foreach ( $widgets_used as  $widget_key => $post_data )
        {
            if(!isset($widgets_exists[$widget_key]))
            {
                $widgets_missing[$widget_key] = $post_data;
            }
		}

        $this->data['widgets_missing'] = $widgets_missing;

        // Load view
        $this->load->view('wde_used_missing/index', $this->data);
    }
    
}