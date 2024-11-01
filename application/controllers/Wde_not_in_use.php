<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Wde_not_in_use extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
        global $wpdb;

        // get existing widgets
        
        $elements_manager = \Elementor\Plugin::instance()->widgets_manager;

        $disabled_widgets = get_option( 'wde_disabled_widgets', array());

        if(isset($_POST['action']) && $_POST['action'] == 'deactivate' && isset($_POST['widgets']))
        {
            if(array($_POST['widgets']))
            {
                foreach($_POST['widgets'] as $widget_selected)
                {
                    if(!isset($disabled_widgets[$widget_selected]))
                    {
                        $disabled_widgets[$widget_selected] = $widget_selected;
                    }
                }
            }

            update_option( 'wde_disabled_widgets', $disabled_widgets, TRUE);
        }

        if(isset($_POST['action']) && $_POST['action'] == 'activate' && isset($_POST['widgets']))
        {
            if(array($_POST['widgets']))
            {
                foreach($_POST['widgets'] as $widget_selected)
                {
                    if(isset($disabled_widgets[$widget_selected]))
                    {
                        unset($disabled_widgets[$widget_selected]);
                    }
                }
            }

            update_option( 'wde_disabled_widgets', $disabled_widgets, TRUE);
        }

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
                    $widgets_used[$found[1]][$page->ID] = $page->post_title." #$page->ID";
                }
            }   
        }

        // filter only widgets not in use

        $widgets_not_used = array();
        $plugins_list = array();

		foreach ( $widgets_exists as  $widget_key => $widget )
        {
            if(!isset($widgets_used[$widget_key]))
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
                }
            }
		}

        ksort($widgets_not_used);

        $this->data['widgets_not_used'] = $widgets_not_used;
        $this->data['plugins_list'] = $plugins_list;
        $this->data['disabled_widgets'] = $disabled_widgets;

        $this->data['hidder_elements'] = get_option(WDE_HIDDER_OPTION_KEY);
        if(empty($this->data['hidder_elements'])) 
            $this->data['hidder_elements'] = array();

        $this->data['unregister_elements'] = get_option(WDE_UNREGISTER_OPTION_KEY);
        if(empty($this->data['unregister_elements'])) 
            $this->data['unregister_elements'] = array();

        // Load view
        $this->load->view('wde_not_in_use/index', $this->data);
    }

    public function export_csv_not_in_use()
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
        $plugins_list = $this->data['plugins_list'];

        foreach ( $this->data['widgets_not_used'] as $widget_key => $widget )
        {
            $row = array();

            $categories = $widget->get_categories();
        
            $plugin_name = '';
            if(isset($categories[0]) && isset($plugins_list[$categories[0].'-'.$widget->get_title()]))
            {
                $plugin_name = $plugins_list[$categories[0].'-'.$widget->get_title()];
            }

            $row['category'] = '';
            if(isset($categories[0]))
                $row['category'] = $categories[0];

            $row['widget_title'] = $widget->get_title();

            $row['widget_key'] = $widget_key;

            $row['widget_icon'] = $widget->get_icon();

            $row['plugin_name'] = $plugin_name;

            $data[] = $row;
        }

        $print_data = wde_prepare_export($data, $skip_cols);

        header('Content-Type: application/csv');
        header("Content-Length:".strlen($print_data));
        header("Content-Disposition: attachment; filename=csv_not_in_use_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

        echo $print_data;
        exit();
    }
    
}