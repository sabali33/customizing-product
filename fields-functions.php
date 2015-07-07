<?php

/* 
 * A file that define input functions
 */

// call select field
function soya_wp_select($term_id){
    global $product;
    $term = get_term($term_id, 'soya-range');
    $term_depth =  soya_get_term_custom_field($term_id, 'soya_depth_meta');
    $term_units = soya_get_term_custom_field($term_id, 'soya_depth_unit_meta');
    //$term_units = empty($term_units) ? '' : 
    if($term_depth < 2 ){
        printf(__('Sorry please use other inputs types for this field %s ', 'soya-customize-product'), edit_term_link('here', '', '', $term, false));
        return;
    }
    $options = soya_get_term_range($term_id, $term_depth);
    echo '<label>'.$term->name.' '.edit_term_link('edit '.$term->name, '', '', $term, false).'</label><br>';
    echo '<select name="'.$term->slug.'">';
    
    foreach ($options as $index => $option){
        $feature_meta_key = $product->all_features->get_feature_meta_key_by_index($term_id, $index);
        //remove range part of the key and prepend term id to it.
        $feature_meta_key = explode('_', $feature_meta_key);
       $product->all_features->set_quantity(270);
        $product->all_features->update_feature_meta($term_id, $index);
       // $product->all_features->set_quantity(200);
        $price = $product->all_features->get_feature_price($term_id);
        echo '<option value="'.$feature_meta_key[0].'_'.$term_id.'">'.$option.' '.$term_units.''.$price.'</option>';
    }
    echo '<select>';
}

add_action('soya_get_select_field', 'soya_wp_select' );

// call text, color, number, radio and date input fields

function soya_wp_type_input($term_id, $type ){
    global $product;
    $term = get_term($term_id, 'soya-range');
    $term_depth =  soya_get_term_custom_field($term_id, 'soya_depth_meta');
   // $input_type = (is_null($type)) ? 'text' : $type;
    echo '<label>'.$term->name.' '.edit_term_link('edit '.$term->name, '', '', $term, false).'</label><br>';
  
    for ($index = 1; $index <= $term_depth; $index++){
       // set prefix to handle inputs differently
        $prefix_index = ( $type === 'radio') ? '' : '_'.$index; 
        $minimum_range = ( $type === 'range') ? '1' : '';
        $maximum_range = ( $type === 'range') ? '10' : '';
        //check to include label for multi inputs
        if(($term_depth > 1 && ($type !== 'radio') ) ){ 
            echo '<span> '.$index.' </span>';
        }elseif($term_depth > 1){
            echo soya_get_term_custom_field($term_id,'soya_lower_'.$index.'_depth_meta');
        }
       $feature_meta_key = $product->all_features->get_feature_meta_key_by_index($term_id, $index);
       $product->all_features->update_feature_meta($term_id, $index);
       $price = $product->all_features->get_feature_price($term_id);
        echo '<label><input type="'.$type.'" name="'.$term->slug.$prefix_index.'" value="" id="'.$term->slug.'_'.$index.'" min="'.$minimum_range.'" max="'.$maximum_range.'">'.$price.'</label>';
    }
  
}
add_action('soya_get_text_field', 'soya_wp_type_input', 10,2 );
add_action('soya_get_date_field', 'soya_wp_type_input', 10,2 );
add_action('soya_get_number_field', 'soya_wp_type_input', 10,2 );
add_action('soya_get_color_field', 'soya_wp_type_input', 10,2 );
add_action('soya_get_radio_field', 'soya_wp_type_input', 10,2 );
add_action('soya_get_range_field', 'soya_wp_type_input', 10,2 );

// call color input field
function soya_wp_check_input($term_id){
    $term = get_term($term_id, 'soya-range');
    $term_depth =  soya_get_term_custom_field($term_id, 'soya_depth_meta');
    //$options = soya_get_term_range($term_id, $term_depth);
    echo '<label>'.$term->name.' '.edit_term_link('edit '.$term->name, '', '', $term, false).'</label><br>';
   
    for ($index = 1;  $index<= $term_depth; $index++){
        
        echo '<input type="checkbox" name="'.$term->slug.'_'.$index.'[]" value="" id="'.$term->slug.'_'.$index.'">';
    }
  
}
add_action('soya_get_checkbox_field', 'soya_wp_check_input' );
