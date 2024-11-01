<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Wde_index extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
        global $wpdb;

        // get existing widgets

        $elements_manager = \Elementor\Plugin::instance()->widgets_manager;
        $existing_widgets = $elements_manager->get_widget_types();

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

        //dump($existing_plugins);

        $widgets_list = array();
        $plugins_list = array();

        foreach($existing_widgets as $widget)
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
                $widgets_list[$category.'-'.$widget->get_title()] = $widget;
            }
        }

        ksort($widgets_list);

        $this->data['existing_widgets'] = $widgets_list;
        $this->data['plugins_list'] = $plugins_list;

        // Load view
        $this->load->view('wde/index', $this->data);
    }

    public function export_csv_installed()
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

        foreach ( $this->data['existing_widgets'] as $widget_key => $widget )
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
        header("Content-Disposition: attachment; filename=csv_installed_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

        echo $print_data;
        exit();
    }
    
}
