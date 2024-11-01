<?php

function wde_generate_fields($fields, $db_data)
{
    if(is_array($fields))
    foreach($fields as $field)
    {
        $field = (object) $field;

        $field_type = $field->field_type;

        $field_view_path = WIDGET_DETECTOR_ELEMENTOR_PATH.'application/views/fields_edit/'.$field_type.'.php';

        if(file_exists($field_view_path))
        {
            include $field_view_path;
        }
        else
        {
            echo __('Missing VIEW file: ', 'w-d-e').$field_type.'.php';
        }
    }
}



?>