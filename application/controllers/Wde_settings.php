<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Wde_settings extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
    // Edit listing method
	public function index()
	{
        $this->load->model('settings_m');

        $this->data['db_data'] = NULL;
        $this->data['form'] = &$this->form;
        $this->data['fields'] = $this->settings_m->fields_list;

        if($this->form->run($this->data['fields']))
        {
            // Save procedure for basic data
    
            $data = $this->settings_m->prepare_data($this->input->post(), $this->data['fields']);

            // Save standard wp post

            foreach($data as $key => $val)
            {
                update_option( $key, $val, TRUE);
            }         

            // redirect
            if(empty($listing_post_id) && !empty($id))
            {
                //wp_redirect(admin_url("admin.php?page=wde_settings&is_updated=true"));
                exit;
            }
                
        }

        // fetch data, after update/insert to get updated last data
        $fields_data = $this->settings_m->get();

        foreach($fields_data as $field)
        {
            $this->data['db_data'][$field->option_name] = $field->option_value;
        }

        $this->load->view('wde_settings/index', $this->data);
    }
    
}
