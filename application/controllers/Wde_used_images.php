<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Wde_used_images extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}

    public function index()
	{
        global $wpdb;

        $search = '';

        if(isset($_GET['s']))
        {
            $search = sanitize_text_field( $_GET['s'] );
            if(!empty($search))
            {
                if(is_numeric($search))
                {
                    $search = "AND post_id = ".intval($search);
                }
                else
                {
                    $search = "AND (post_title LIKE '%$search%')";
                }
                
            }
        }

        // get all posts

        $sql = "SELECT COUNT(*) AS total_related_posts FROM $wpdb->posts 
                            JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id
                            WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data' 
                            $search
                            ORDER BY $wpdb->posts.ID
                            LIMIT 0,3"; 

        $results = $wpdb->get_results($sql);

        $total_related_posts = 0;
        if(isset($results[0]->total_related_posts))
            $total_related_posts = $results[0]->total_related_posts;

        $current_page = 1;

        if(isset($_GET['paged']))
            $current_page = intval(wmvc_xss_clean($_GET['paged']));

        $per_page = 20;
        $offset = $per_page*($current_page-1);

        $this->data['pagination_output'] = '';

        if(function_exists('wmvc_wp_paginate'))
            $this->data['pagination_output'] = wmvc_wp_paginate($total_related_posts, $per_page);

        $sql = "SELECT * FROM $wpdb->posts 
                            JOIN $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id
                            WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = '_elementor_data' 
                            $search
                            ORDER BY $wpdb->posts.ID
                            LIMIT $offset,$per_page"; 

        $posts = $wpdb->get_results($sql);

        $posts_list = array();
        $upload_dir = wp_get_upload_dir();

        foreach ( $posts as $key => $page ) {

            // check if post contain elementor data

            $elementor_data = get_post_meta( $page->ID, '_elementor_data' );

            if(isset($elementor_data[0]))
            {
                // detect all links in format http*"
                $pattern = '/image":(.*?)}/s';

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

                preg_match_all($pattern, $elementor_data[0], $matches);

                $image_urls = array();
                $image_filenames = array();
                $image_sizes = array();
                foreach($matches[1] as $match)
                {
                    if(strpos($match, 'wp-content') !== FALSE)
                    {
                        $image_id = substr($match, strrpos($match, '"id":')+5);

                        if(strpos($image_id, ',') !== FALSE)
                        $image_id = substr($image_id, 0, strpos($image_id, ','));

                        $original_image_path = NULL;
                        $original_image_url  = NULL;
                        if(!is_numeric($image_id))
                        {
                            $image_id = NULL;
                        }
                        else
                        {
                            $original_image_path = wp_get_original_image_path($image_id);
                            $original_image_url  = wp_get_original_image_url($image_id);

                            if(!file_exists($original_image_path))
                            {
                                $original_image_path = NULL;
                                $original_image_url = NULL;
                            }
                        }

                        $match = substr($match, strpos($match, 'http')+4);
                        $match = substr($match, 0, strpos($match, '"'));

                        $image_url_db = 'http'.$match;

                        if($original_image_url !== NULL)
                        {
                            $image_url = $original_image_url;
                        }
                        else
                        {
                            $image_url = 'http'.$match;
                        }

                        if(isset($image_filenames[$image_url]))
                            continue;

                        $img_filename = substr($match, strrpos($match, '\\/')+2);

                        $img_path = urldecode($match);

                        $img_path = substr($img_path, strrpos($img_path, 'wp-content')+19);
                        $img_path = $upload_dir['basedir'].str_replace('\/', '/', $img_path);
                        $img_path_db = $img_path;

                        if($original_image_path !== NULL)
                        {
                            $img_path = $original_image_path;
                        }

                        $red_messages = array();

                        if(strlen($img_filename) > 40)
                            $red_messages[] = 'Unusual image filename length';

                        if(is_numeric(substr($img_filename, 0, 1)))
                            $red_messages[] = 'Unusual image filename, starting with number';

                        if(strpos($img_filename, 'opy') !== FALSE || strpos($img_filename, 'test') !== FALSE || strpos($img_filename, 'ntitled') !== FALSE || strpos($img_filename, 'reenshot') !== FALSE
                        || strpos($img_filename, ' ') !== FALSE)
                            $red_messages[] = 'Unusual image filename';

                        $img_size = '';

                        if(file_exists($img_path))
                        {
                            list($width, $height, $type, $attr) = getimagesize($img_path);

                            if($width > 2000 || $height > 2000)
                                $red_messages[] = 'Large image dimension > 2000';

                            $img_size = filesize($img_path);

                            if(isset($image_sizes[$img_size]))
                            {
                                $red_messages[] = 'Possible duplicated image on same page';
                            }

                            $image_sizes[$img_size] = $img_path;

                            if($img_size > 400000)
                                $red_messages[] = 'Filesize > 400KB';

                            $img_size = number_format($img_size/1000, 2).'KB';
                        }

                        if(!file_exists($img_path))
                        {
                            $red_messages[] = 'Can\'t locate file, only available in Elementor structure definition: '.esc_url_raw($image_url);
                        }

                        if(!file_exists($img_path_db))
                        {
                            $red_messages[] = 'In Elementor JSON structure definition post_meta wrong image url: '.esc_url_raw($image_url_db);
                        }

                        $resolution = '';
                        if(!empty($width))
                            $resolution = $width.'x'.$height.'px';

                        $image_filenames[$image_url] = array('filename' => $img_filename, 'url' => $image_url, 'red_messages' => $red_messages, 'path' => $img_path, 'size' => $img_size, 'resolution' => $resolution);
                    }
                }

                $posts_list[$key]['post_data'] = $page;
                $posts_list[$key]['images_list'] = $image_filenames;      
            }
        }

        $this->data['posts_list'] = $posts_list;

        // Load view
        $this->load->view('wde_used_images/index', $this->data);
    }

    
}
