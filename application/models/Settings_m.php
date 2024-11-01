<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Settings_m extends Winter_MVC_Model {

	public $_table_name = 'options';
	public $_order_by = 'option_id';
    public $_primary_key = 'option_id';
    public $_own_columns = array();
    public $_timestamps = TRUE;
    protected $_primary_filter = 'intval';

    public $fields_list = null;
    
	public function __construct(){
        parent::__construct();

        $pages = array('' => __('Not Selected', 'w-d-e'));

        foreach(get_pages(array('sort_column' => 'post_title')) as $page)
        {
            $pages[$page->ID] = $page->post_title.' #'.$page->ID;
        }

        $this->fields_list = array( 
            array('field' => 'wde_editor_hover', 'field_label' => __('Widget Name Inside Editor', 'w-d-e'), 'hint' => __('Show Widget Name inside Elementor Editor on WIdget Mouseover', 'w-d-e'), 'field_type' => 'CHECKBOX', 'rules' => ''),
        );

	}

    /* [START] For dinamic data table */
    
    public function get_available_fields()
    {      
        $fields = $this->db->list_fields($this->_table_name);

        return $fields;
    }
    
    public function total_lang($where = array())
    {
        $this->db->select('COUNT(*) as total_count');
        $this->db->from($this->_table_name);
        $this->db->where($where);
        $this->db->order_by($this->_order_by);
        
        $query = $this->db->get();

        $res = $this->db->results();

        if(isset($res[0]->total_count))
            return $res[0]->total_count;

        return 0;
    }
    
    public function get_pagination_lang($limit, $offset, $where = array())
    {
        $this->db->select('*');
        $this->db->from($this->_table_name);
        $this->db->where($where);
        $this->db->limit($limit);
        $this->db->offset($offset);
        $this->db->order_by($this->_order_by);
        
        $query = $this->db->get();

        if ($this->db->num_rows() > 0)
            return $this->db->results();
        
        return array();
    }
    
    public function check_deletable($id)
    {
        return true;
    }
    
    
    /* [END] For dinamic data table */

}













?>