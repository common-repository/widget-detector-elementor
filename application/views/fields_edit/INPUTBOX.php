<?php

//dump($field);

if(isset($field->field))
{
    $field_id = $field->field;
}
else
{
    $field_id = 'field_'.$field->idfield;
}

if(!isset($field->hint))$field->hint = '';

if(!isset($field->class))$field->class = '';

$field_label = $field->field_label;

$required = '';
if(isset($field->is_required) && $field->is_required == 1)
    $required = '*';

?>

<div class="wde-field-edit <?php echo esc_attr($field->field_type); ?> <?php echo esc_attr($field->class); ?>">
    <label for="<?php echo esc_attr($field_id); ?>"><?php echo esc_html($field_label).$required; ?></label>
    <div class="wde-field-container">
        <input class="regular-text" name="<?php echo esc_attr($field_id); ?>" type="text" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr(wmvc_show_data($field_id, $db_data, '')); ?>">
    
        <p class="wde-hint">
            <?php echo esc_html($field->hint); ?>
        </p>
    </div>
</div>



