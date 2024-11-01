<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Wde_used_widgets extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}

    public function index()
	{
        global $wpdb;

        // prepare post

        $this->data['show_categories'] = array();
        $this->data['show_widgets'] = array();
        $this->data['show_post_types'] = array();

        if(isset($_GET['show_categories']))
            $this->data['show_categories'] = wmvc_xss_clean_array($_GET['show_categories']);

        if(isset($_GET['show_widgets']))
            $this->data['show_widgets'] = wmvc_xss_clean_array($_GET['show_widgets']);

        if(isset($_GET['show_post_types']))
            $this->data['show_post_types'] = wmvc_xss_clean_array($_GET['show_post_types']);

        // get existing widgets

        $elements_manager = \Elementor\Plugin::instance()->widgets_manager;
        $widgets_exists = $elements_manager->get_widget_types();
        ksort($widgets_exists);

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

        $widgets_exists_title = array();

        foreach($widgets_exists as $key=>$widget)
        {
            $widgets_exists_title[$widget->get_title()] = $widget;
        }

        ksort($widgets_exists_title);

        $this->data['widgets_exists'] = $widgets_exists;
        $this->data['widgets_exists_title'] = $widgets_exists_title;

        // widget categories

        $widget_categories = array();

        foreach($widgets_exists as $key=>$widget)
        {
            $categories = $widget->get_categories();

            if(isset($categories[0]))
            {
                if($categories[0] == 'wordpress')
                {
                    $widget_categories[$categories[0]] = 'WordPress';
                }
                else
                {
                    $widget_categories[$categories[0]] = ucfirst($categories[0]);
                }
            }  
        }

        ksort($widget_categories);

        $this->data['widget_categories'] = $widget_categories;

        // category colors

        $colors = array();
        $colors['basic'] = '#20639B';
        $colors['general'] = '#20639B';
        $colors['wordpress'] = '#3CAEA3';
        $colors['pro-elements'] = '#ED553B';

        $this->data['category_colors'] = $colors;

        // get post types

        $post_types = array();
        $post_types_all = get_post_types( array(), 'objects' );
        $post_types_available = array();
        foreach($post_types_all as $post_type_obj)
        {
            $post_types[$post_type_obj->name] = $post_type_obj->label;
        }

        ksort($post_types);

        $this->data['post_types'] = $post_types;

        $sql = "SELECT post_type FROM $wpdb->posts 
        JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data' 
        ORDER BY $wpdb->posts.ID
        LIMIT 1000"; 

        $posts = $wpdb->get_results($sql);

        foreach ( $posts as $key => $page ) 
        {
            // save post type

            if(isset($post_types[$page->post_type]))
            {
                $post_types_available[$page->post_type] = $post_types[$page->post_type];
            }
            else
            {
                $post_types_available[$page->post_type] = ucfirst($page->post_type);
            }
        }

        //echo '<pre>';
        //var_dump($post_types_available);
        //echo '</pre>';
        $this->data['post_types_available'] = $post_types_available;

        // fetch all templates, for detect global widgets names

        $sql = "SELECT ID, post_title, post_type FROM $wpdb->posts 
        WHERE $wpdb->posts.post_type = 'elementor_library' 
        ORDER BY $wpdb->posts.ID
        LIMIT 1000"; 

        $post_templates = array();

        $posts = $wpdb->get_results($sql);

        foreach ( $posts as $key => $page ) 
        {
            $post_templates[$page->ID] = $page;
        }

        // get all posts

        $where_in_post_type = '';
        if(isset($this->data['show_post_types'][0]))
        {
            $pt_join_text = array();
            foreach($this->data['show_post_types'] as $post_type_value)
            {
                $pt_join_text[] = '\''.esc_sql($post_type_value).'\'';
            }

            $where_in_post_type = ' AND '.$wpdb->posts.'.post_type IN ('.join(',', $pt_join_text).') ';
        }

        $where_in_widgets = '';
        if(isset($this->data['show_widgets'][0]))
        {
            $pt_join_text = array();
            foreach($this->data['show_widgets'] as $widget_key_sel)
            {
                $pt_join_text[] = $wpdb->postmeta.".meta_value LIKE '%\"".esc_sql($widget_key_sel)."%' ";
            }

            $where_in_widgets = ' AND ( '.join(' OR ', $pt_join_text).' ) ';
        }

        $sql = "SELECT COUNT(*) AS total_related_posts FROM $wpdb->posts 
                            JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id
                            WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data' $where_in_post_type $where_in_widgets
                            ORDER BY $wpdb->posts.ID"; 

        $results = $wpdb->get_results($sql);

        $total_related_posts = 0;
        if(isset($results[0]->total_related_posts))
            $total_related_posts = $results[0]->total_related_posts;

        $current_page = 1;

        if(isset($_GET['paged']))
            $current_page = intval(wmvc_xss_clean($_GET['paged']));

        $per_page = 100;
        $offset = $per_page*($current_page-1);

        $this->data['pagination_output'] = '';

        if(function_exists('wmvc_wp_paginate'))
            $this->data['pagination_output'] = wmvc_wp_paginate($total_related_posts, $per_page);

        $sql = "SELECT * FROM $wpdb->posts 
                            JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id
                            WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data' $where_in_post_type $where_in_widgets
                            ORDER BY $wpdb->posts.ID
                            LIMIT $offset,$per_page"; 

        $posts = $wpdb->get_results($sql);

        $posts_list = array();
        $plugins_list = array();

        foreach ( $posts as $key => $page ) {

            // check if post contain elementor data

            $elementor_data = get_post_meta( $page->ID, '_elementor_data' );

            if(isset($elementor_data[0]))
            {
                // parse elementor json and found all widgets in json for specific page

                $regExp = '/"widgetType":"([^}]*)/i';
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

                $posts_list[$key]['post_data'] = $page;

                $widgets_list = array();

                foreach($outputArray as $found)
                {
                    if(!isset($found[1]))continue;

                    $widget_key = $found[1];

                    $widget_key = strtok($widget_key, '"');

                    $widget = NULL;
                    if(isset($widgets_exists[$widget_key]))
                        $widget = $widgets_exists[$widget_key];

                    $template_id = NULL;
                    if($widget_key == 'global' && isset($found[0]))
                    {
                        $template_id_temp = substr($found[0], strpos($found[0], '"templateID"') + 13);
                        if(is_numeric($template_id_temp))
                        {
                            $template_id = $template_id_temp;

                            if(isset($post_templates[$template_id]))
                                $template_id = $post_templates[$template_id]->post_title;

                            $widget->custom_title = $widget->get_title().' ('.$template_id.')';
                        }

                    }

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
                    }

                    if (count($this->data['show_categories']) > 0 && isset($categories[0])) {
                        if (!in_array($category, $this->data['show_categories'])) continue;
                    }

                    if (count($this->data['show_widgets']) > 0) {
                        if (!in_array($widget_key,$this->data['show_widgets'])) continue;
                    }

                    if (!empty($_GET['s'])) {
                        if (
                            strpos($widget_key, $_GET['s']) === FALSE &&
                            strpos(isset($categories[0]) ? $categories[0] : '', $_GET['s']) === FALSE &&
                            strpos($widget->get_title(), $_GET['s']) === FALSE
                        ) continue;
                    }

                    if(isset($widget))
                    {
                        $widgets_list[$category.'-'.$widget->get_title()] = $widget;
                    }
                    else
                    {
                        $widgets_list[$category.'-'.$widget_key] = array('key' => $widget_key);
                    }
                    
                }     

                ksort($widgets_list);

                $posts_list[$key]['widgets_list'] = $widgets_list;                
            }
        }

        $this->data['posts_list'] = $posts_list;
        $this->data['plugins_list'] = $plugins_list;

        // export url generate

        $url ='admin.php';
        $qs_parameters = wmvc_xss_clean( $_GET );
        $qs_parameters['function'] = 'export_csv_used_widgets';
        
        $qs_part = http_build_query($qs_parameters);
        $url.='?'.$qs_part;

        $this->data['export_url'] = $url;

        // Load view
        $this->load->view('wde_used_widgets/index', $this->data);
    }

    public function general()
	{
        global $wpdb;

        // prepare post

        $this->data['show_categories'] = $this->data['show_widgets'] = array();

        if(isset($_GET['show_categories']))
            $this->data['show_categories'] = wmvc_xss_clean_array($_GET['show_categories']);

        if(isset($_GET['show_widgets']))
            $this->data['show_widgets'] = wmvc_xss_clean_array($_GET['show_widgets']);

        if(isset($_GET['show_post_types']))
            $this->data['show_post_types'] = wmvc_xss_clean_array($_GET['show_post_types']);

        // get existing widgets

        $elements_manager = \Elementor\Plugin::instance()->widgets_manager;
        $widgets_exists = $elements_manager->get_widget_types();
        $this->data['widgets_exists'] = $widgets_exists;

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


        // widget categories

        $widget_categories = array();

        foreach($widgets_exists as $key=>$widget)
        {
            $categories = $widget->get_categories();

            if(isset($categories[0]))
                $widget_categories[$categories[0]] = ucfirst($categories[0]);
        }

        $this->data['widget_categories'] = $widget_categories;

        // get all posts

        $sql = "SELECT COUNT(*) AS total_related_posts FROM $wpdb->posts 
                            JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id
                            WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data' 
                            ORDER BY $wpdb->posts.ID
                            LIMIT 0,3"; 

        $results = $wpdb->get_results($sql);

        $total_related_posts = 0;
        if(isset($results[0]->total_related_posts))
            $total_related_posts = $results[0]->total_related_posts;

        $current_page = 1;

        if(isset($_GET['paged']))
            $current_page = intval(wmvc_xss_clean($_GET['paged']));

        $per_page = 100;
        $offset = $per_page*($current_page-1);

        $this->data['pagination_output'] = '';

        if(function_exists('wmvc_wp_paginate'))
            $this->data['pagination_output'] = wmvc_wp_paginate($total_related_posts, $per_page);

        $sql = "SELECT * FROM $wpdb->posts 
                            JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id
                            WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data' 
                            ORDER BY $wpdb->posts.ID
                            LIMIT $offset,$per_page"; 

        $posts = $wpdb->get_results($sql);

        $posts_list = array();

        $widgets_list = array();
        $plugins_list = array();

        foreach ( $posts as $key => $page ) {

            // check if post contain elementor data

            $elementor_data = get_post_meta( $page->ID, '_elementor_data' );

            if(isset($elementor_data[0]))
            {
                // parse elementor json and found all widgets in json for specific page

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
                        echo '<div class="notice notice-warning"><p>'._('Parsing elementor data issue on page:').' <a href="'.get_admin_url().'post.php?post='.$page->ID.'&action=edit">#'.$page->ID.', '.esc_html($page->post_title).'</a></p></div>';

                    }
                }
                
                if ( preg_match_all($regExp, $elementor_data[0], $outputArray, PREG_SET_ORDER) ) {
                }

                foreach($outputArray as $found)
                {
                    if(!isset($found[1]))continue;

                    $widget_key = $found[1];

                    $widget = $widgets_exists[$widget_key];
    
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
                    }

                    if(isset($widget))
                    {
                        $widgets_list[$category.'-'.$widget->get_title()] = $widget;
                    }
                    else
                    {
                        $widgets_list[$category.'-'.$widget_key] = array('key' => $widget_key);
                    }
                }            
            }
        }

        // sort
        ksort($widgets_list);

        $this->data['widgets_list'] = $widgets_list;
        $this->data['plugins_list'] = $plugins_list;

        // Load view
        $this->load->view('wde_used_widgets/general', $this->data);
    }

    
    public function export_csv_used_widgets_general()
    {
        ob_clean();

        if(!function_exists('wde_prepare_export'))
            exit('Missing addon');

        $gmt_offset = get_option('gmt_offset');
        $skip_cols = array();

        // generate data, but not output
        ob_start();
        $this->general();
        ob_clean();

        $data = array();
        $plugins_list = $this->data['plugins_list'];

        foreach ( $this->data['widgets_list'] as $widget_key => $widget )
        {
            $row = array();

            $categories = array();
            if (isset($widget) && is_object($widget))
            {
                $categories = $widget->get_categories();
            }
    
            $plugin_name = '';
            if(isset($categories[0]) && isset($plugins_list[$categories[0].'-'.$widget->get_title()]))
            {
                $plugin_name = $plugins_list[$categories[0].'-'.$widget->get_title()];
            }

            $row['category'] = '';
            if(isset($categories[0]))
                $row['category'] = $categories[0];

            if (isset($widget) && is_object($widget))
            {
                $row['widget_title'] = $widget->get_title();
            }
            else
            {
                $row['widget_title'] = __('Widget missing', 'w-d-e');
            }

            if (isset($widget) && is_object($widget))
            {
                $row['widget_key'] = $widget->get_name();
            }
            elseif (isset($widget) && is_array($widget))
            {
                $row['widget_key'] = $widget['key'];
            }           

            if (isset($widget) && is_object($widget))
            {
                $row['widget_icon'] = $widget->get_icon();
            }
            
            $row['plugin_name'] = $plugin_name;

            $data[] = $row;
        }

        $print_data = wde_prepare_export($data, $skip_cols);

        header('Content-Type: application/csv');
        header("Content-Length:".strlen($print_data));
        header("Content-Disposition: attachment; filename=csv_used_widgets_general_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

        echo $print_data;
        exit();
    }   

    public function export_csv_used_widgets()
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

        foreach ($this->data['posts_list'] as $key => $post)
        {
            $page = $post['post_data'];
            $widgets_list = $post['widgets_list'];

            foreach ( $widgets_list as $widget_key => $widget )
            {
                $row = array();

                $categories = array();
                if (isset($widget) && is_object($widget))
                {
                    $categories = $widget->get_categories();
                }
        
                $plugin_name = '';
                if(isset($categories[0]) && isset($plugins_list[$categories[0].'-'.$widget->get_title()]))
                {
                    $plugin_name = $plugins_list[$categories[0].'-'.$widget->get_title()];
                }


                $row['post_title'] = $page->post_title;

                $row['post_id'] = $page->ID;

                $row['post_type'] = $page->post_type;

                $row['category'] = '';
                if(isset($categories[0]))
                    $row['category'] = $categories[0];

                if (isset($widget) && is_object($widget))
                {
                    $row['widget_title'] = $widget->get_title();
                }
                else
                {
                    $row['widget_title'] = __('Widget missing', 'w-d-e');
                }

                if (isset($widget) && is_object($widget))
                {
                    $row['widget_key'] = $widget->get_name();
                }
                elseif (isset($widget) && is_array($widget))
                {
                    $row['widget_key'] = $widget['key'];
                }           

                if (isset($widget) && is_object($widget))
                {
                    $row['widget_icon'] = $widget->get_icon();
                }
                
                $row['plugin_name'] = $plugin_name;

                $data[] = $row;
            }

        }

        $print_data = wde_prepare_export($data, $skip_cols);

        $current_page = 1;

        if(isset($_GET['paged']))
            $current_page = intval(wmvc_xss_clean($_GET['paged']));

        header('Content-Type: application/csv');
        header("Content-Length:".strlen($print_data));
        header("Content-Disposition: attachment; filename=csv_used_widgets_page_{$current_page}_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

        echo $print_data;
        exit();
    }   
    
}
