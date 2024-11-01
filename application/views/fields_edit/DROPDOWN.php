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

$field_label = $field->field_label;

$required = '';
if(isset($field->is_required) && $field->is_required == 1)
    $required = '*';

$values = array();
if(isset($field->values))
    $values = $field->values;

?>

<div class="wde-field-edit <?php echo esc_attr($field->field_type); ?>">
    <label for="<?php echo esc_attr($field_id); ?>"><?php echo esc_html($field_label).$required; ?></label>
    <div class="wde-field-container">
        <?php echo wmvc_select_option($field_id, $values, wmvc_show_data($field_id, $db_data, '')); ?>

        <p class="wde-hint">
            <?php echo esc_html($field->hint); ?>
        </p>
    </div>
</div>
