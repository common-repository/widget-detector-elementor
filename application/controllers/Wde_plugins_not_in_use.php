<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Wde_plugins_not_in_use extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
        global $wpdb;

        // get existing widgets
        
        $elements_manager = \Elementor\Plugin::instance()->widgets_manager;
        $widgets_exists = $elements_manager->get_widget_types();

        // get existing plugins

        $existing_plugins = array();

        if ( function_exists( 'get_plugins' ) ) {
            $existing_plugins = get_plugins();
        }

        $existing_plugin_keys = array();
        foreach($existing_plugins as $key => $row)
        {
            $key = substr( $key, 0, strpos( $key, '/' ) );
            $existing_plugin_keys[$key] = $row;
        }

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
                    if(!isset($widgets_used[$found[1]]))
                        $widgets_used[$found[1]] = 0;

                    $widgets_used[$found[1]]++;
                }
            }   
        }

        // filter only widgets not in use

        $widgets_not_used = array();
        $plugins_list = array();
        $plugins_list_used_time = array();
        $plugins_list_categories = array();

		foreach ( $widgets_exists as  $widget_key => $widget )
        {
            $category = '';
            if (isset($widget))
            {
                $categories = $widget->get_categories();

                if(isset($categories[0]))
                    $category = $categories[0];

                $reflection = new \ReflectionClass( $widget );

                $widget_path = plugin_basename( $reflection->getFileName() );
                $plugin_slug = substr( $widget_path, 0, strpos( $widget_path, '/' ) );

                $plugin_name = $plugin_slug;

                if(isset($existing_plugin_keys[$plugin_slug]["Name"]))
                {
                    $plugin_name = $existing_plugin_keys[$plugin_slug]["Name"];
                }

                $plugins_list[$category.'-'.$widget->get_title()] = $plugin_name;
                $widgets_not_used[$category.'-'.$widget->get_title()] = $widget;

                if(!isset($plugins_list_used_time[$plugin_name]))
                    $plugins_list_used_time[$plugin_name] = 0;

                if(isset($widgets_used[$widget_key]))
                    $plugins_list_used_time[$plugin_name]+=$widgets_used[$widget_key];

                $plugins_list_categories[$plugin_name][] = $category;
            }
		}

        ksort($plugins_list_used_time);

        //$this->data['widgets_not_used'] = $widgets_not_used;
        //$this->data['plugins_list'] = $plugins_list;
        $this->data['plugins_list_categories'] = $plugins_list_categories;
        $this->data['plugins_list_used_time'] = $plugins_list_used_time;

        // Load view
        $this->load->view('wde_plugins_not_in_use/index', $this->data);
    }

    public function export_csv_plugins_not_in_use()
    {
        ob_clean();

        if(!function_exists('wde_prepare_export'))
            exit('Missing addon');

        $gmt_offset = get_option('gmt_offset');
        $skip_cols = array();

        // generate data, but not output
        ob_start();
        $this->index();
        ob_clean();

        $data = array();
        $plugins_list_used_time = $this->data['plugins_list_used_time'];

        foreach ( $plugins_list_used_time as $plugin_name => $used_times )
        {
            $row = array();

            $row['plugin_name'] = $plugin_name;

            $row['used_times'] = (string) $used_times;

            $data[] = $row;
        }

        $print_data = wde_prepare_export($data, $skip_cols);

        header('Content-Type: application/csv');
        header("Content-Length:".strlen($print_data));
        header("Content-Disposition: attachment; filename=csv_plugins_not_in_use_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

        echo $print_data;
        exit();
    }
    
}