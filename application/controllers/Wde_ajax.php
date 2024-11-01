<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Wde_ajax extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
    }

    public function sync_plugins()
    {
        $data = array('status' => 'failed', 'log' => __('No message returned', 'w-d-e'));
        $widget_keys = $this->input->post_get('widget_keys');

        // get existing widgets

        $elements_manager = \Elementor\Plugin::instance()->widgets_manager;
        $existing_widgets = $elements_manager->get_widget_types();
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

        $widgets_list = array();

        foreach($existing_widgets as $widget_key => $widget)
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
                $plugin_details = NULL;

                if(isset($existing_plugin_keys[$plugin_slug]["Name"]))
                {
                    $plugin_name = $existing_plugin_keys[$plugin_slug]["Name"];
                    $plugin_details = $existing_plugin_keys[$plugin_slug];
                }

                $widgets_list[$category.'-'.$widget->get_title()] = array(
                    'widget_key' => $widget_key,
                    'widget_title' => $widget->get_title(),
                    'widget_category' => $category,
                    'plugin_name' => $plugin_name,
                    'plugin_slug' => $plugin_slug,
                    'widget_icon' => $widget->get_icon(),
                    'plugin_details' => json_encode($plugin_details)
                );
            }
        }

        // sync plugins info, send plugins info to server and get missing plugin details localy

        $data_to_send = array('missing_widget_keys' => serialize($widget_keys),
                              'widgets_list' => serialize($widgets_list),
                              'referer' => get_home_url(),
                              'page_widget_lists' => serialize($this->get_pages_list())
                            );

        //dump($data_to_send);
        
        $ret_call = wmvc_api_call('POST', ELEMENTDETECTOR_SYNC_URL, $data_to_send);    

        //dump($ret_call);exit();

        $returned_widget_details = array();
        if($ret_call !== FALSE)
        {
            $ret_call_obj = json_decode($ret_call);

            if(isset($ret_call_obj->widget_details))
            {
                $returned_widget_details = (array) $ret_call_obj->widget_details;
            }

        }

        if(is_array($widget_keys))
        {
            $data['log'] = '';

            foreach($widget_keys as $widget_key)
            {
                if(isset($returned_widget_details[$widget_key]))
                {
                    $widget_details = $returned_widget_details[$widget_key];

                    $data['log'].= __('For widget key: ', 'w-d-e').$widget_key.__(' we found real widget category and name: ', 'w-d-e').
                                    $widget_details->widget_category.', '.
                                    $widget_details->widget_title.__(' and plugin related: ', 'w-d-e').
                                    $widget_details->plugin_name.'<br />';
                }
                else
                {
                    $data['log'].= __('For widget key: ', 'w-d-e').$widget_key.__(' we can\'t detect plugin related, please try to google if name is not recognisable', 'w-d-e').'<br />';
                }
            }

            $data['status'] = 'success';
        }
        else
        {
            $data['status'] = 'success';
            $data['log'] = __('Great, you don\'t have any Elementor Widgets used on published posts/pages but missing', 'w-d-e');
        }

        header('Content-type: application/json');
        exit(json_encode($data));
    }

    private function get_pages_list()
    {
        global $wpdb;

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

        $sql = "SELECT * FROM $wpdb->posts 
        JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data' $where_in_post_type 
        ORDER BY $wpdb->posts.ID
        LIMIT 0,100"; 

        $posts = $wpdb->get_results($sql);

        $posts_list = array();
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
                    echo '<div class="notice notice-warning"><p>'._('Parsing elementor data issue on page:').' <a href="'.get_admin_url().'post.php?post='.$page->ID.'&action=edit">#'.$page->ID.', '.$page->post_title.'</a></p></div>';

                }
                }

                if ( preg_match_all($regExp, $elementor_data[0], $outputArray, PREG_SET_ORDER) ) {
                }

                $widgets_list = array();

                foreach($outputArray as $found)
                {
                    if(!isset($found[1]))continue;

                    $widget_key = $found[1];

                    $widgets_list[$widget_key] = $widget_key;
                }     

                ksort($widgets_list);

                $posts_list[get_permalink($page)] = array_values($widgets_list);                
            }
        }

        return $posts_list;
    }

    public function hidder_el(){

        // Check _wpnonce
        check_admin_referer( 'wde_hidder_el', '_wpnonce' );

        $data = array();
        $data['message'] = '';
        $data['output_message'] = '';
        $data['popup_text_success'] = '';
        $data['popup_text_success'] = '';
        $data['popup_text_error'] = '';
        $data['output'] = array();
		$data['success'] = false;

        $el_name = sanitize_text_field($this->input->post_get('el_name'));
        $set_status = sanitize_text_field($this->input->post_get('set_status'));

        if(empty($el_name) || (empty($set_status) && $set_status!=0)) {
            $data['popup_text_error'] = __('Field el_name and set_status are required', 'w-d-e');
        } else {
            $elements = get_option(WDE_HIDDER_OPTION_KEY);
            if(empty($elements)) 
                $elements = array();

            if(stripos($el_name, ',') !== FALSE) {
                if($set_status == 1) {
                    foreach (explode(',',$el_name) as $el) {
                        $elements[$el] = 1;
                    }
                    $data['popup_text_success'] = __('Elements hiden', 'w-d-e');
                } else {
                    foreach (explode(',',$el_name) as $el) {
                        unset($elements[$el]);
                    }
                    $data['popup_text_success'] = __('Elements unhiden', 'w-d-e');
                }
            } else {
                if($set_status == 1) {
                    $elements[$el_name] = 1;
                    $data['popup_text_success'] = $el_name. ' '.__('hiden', 'w-d-e');
                } else {
                    unset($elements[$el_name]);
                    $data['popup_text_success'] = $el_name. ' '.__('unhiden', 'w-d-e');
                }
            }

            update_option(WDE_HIDDER_OPTION_KEY, $elements);
           
            $data['success'] = true;
        }

        do_action('wde/ajax/hidder_el');
        $this->output($data);
    }

    public function unregister_el(){

        /* check on pro */
        if(!function_exists('wde_prepare_export')) return false;

        // Check _wpnonce
        check_admin_referer( 'wde_unregister_el', '_wpnonce' );

        $data = array();
        $data['message'] = '';
        $data['output_message'] = '';
        $data['popup_text_success'] = '';
        $data['popup_text_success'] = '';
        $data['popup_text_error'] = '';
        $data['output'] = array();
		$data['success'] = false;

        $el_name = sanitize_text_field($this->input->post_get('el_name'));
        $set_status = sanitize_text_field($this->input->post_get('set_status'));

        if(empty($el_name) || (empty($set_status) && $set_status!=0)) {
            $data['popup_text_error'] = __('Field el_name and set_status are required', 'w-d-e');
        } else {
            $elements = get_option(WDE_UNREGISTER_OPTION_KEY);
            if(empty($elements)) 
                $elements = array();

            if(stripos($el_name, ',') !== FALSE) {
                if($set_status == 1) {
                    foreach (explode(',',$el_name) as $el) {
                        $elements[$el] = 1;
                    }
                    $data['popup_text_success'] = __('Elements unregister', 'w-d-e');
                } else {
                    foreach (explode(',',$el_name) as $el) {
                        unset($elements[$el]);
                    }
                    $data['popup_text_success'] = __('Elements registered', 'w-d-e');
                }
            } else {
                if($set_status == 1) {
                    $elements[$el_name] = 1;
                    $data['popup_text_success'] = $el_name. ' '.__('unregister', 'w-d-e');
                } else {
                    unset($elements[$el_name]);
                    $data['popup_text_success'] = $el_name. ' '.__('registered', 'w-d-e');
                }
            }

            update_option(WDE_UNREGISTER_OPTION_KEY, $elements);
           
            $data['success'] = true;
        }

        do_action('wde/ajax/hidder_el');
        $this->output($data);
    }
         
    private function output($data, $print = TRUE) {
		$data = json_encode($data);
        if($print) {
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache');
            header('Content-Type: application/json; charset=utf8');
            //header('Content-Length: '.$length); // special characters causing troubles
            echo wmvc_xss_clean($data);
            exit();
        } else {
            return $data;
        }
    }
	
}
