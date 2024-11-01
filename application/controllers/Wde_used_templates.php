<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Wde_used_templates extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}

    public function index()
	{
        global $wpdb;

        $per_page = 500;
        $offset = 0;

        $sql = "SELECT * FROM $wpdb->posts 
                            JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id
                            WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data'  AND
                            $wpdb->postmeta.meta_value LIKE \"%elementor-template%\"
                            ORDER BY $wpdb->posts.ID
                            LIMIT $offset,$per_page"; 

        $posts = $wpdb->get_results($sql);

        $templates_list = array();

        foreach ( $posts as $key => $page ) {

            // check if post contain elementor data

            $elementor_data = get_post_meta( $page->ID, '_elementor_data' );

            if(isset($elementor_data[0]))
            {
                // parse elementor json and found all widgets in json for specific page "widgetType":"([^"]*)

                $regExp = '/"widgetType":"([^"]*)/i';
                // {"shortcode":"[elementor-template id=\"86\"]"}
                $regExp = '/elementor-template id=\\\"([^\\\]*)/i';
                $outputArray = array();

                if(!is_array($elementor_data) || !is_string($elementor_data[0]))
                {

                    if(is_array($elementor_data[0]))
                    {
                        $elementor_data[0] = json_encode($elementor_data[0]);
                    }
                    else
                    {
                        echo '<div class="notice notice-warning"><p>'._('Parsing elementor data issue on page:').' <a href="'.get_admin_url().'post.php?post='.$page->ID.'&action=edit">#'.$page->ID.', '.esc_html($page->post_title).'</a></p></div>';

                    }
                }
                
                if ( preg_match_all($regExp, $elementor_data[0], $outputArray, PREG_SET_ORDER) ) {
                }

                foreach($outputArray as $found)
                {
                    if(!isset($found[1]))continue;

                    if(!is_numeric($found[1]))continue;

                    $template_id = $found[1];

                    $templates_list[] = $template_id;
                }            
            }
        }

        $this->data['templates_list'] = $templates_list;

        // Load view
        $this->load->view('wde_used_templates/general', $this->data);
    }

    
}
