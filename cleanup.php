<?php

/* 
 * Clean up database when terms are not needed
 */

//Remove meta keys that not no longer exist
add_action('delete_term', 'soya_delete_term_meta_boxes');

function soya_delete_term_meta_boxes($term_id, $tt_id, $taxonomy, $deleted_term){
    
    if($taxonomy !== 'soya-range')
        return;
        
    $deleted_term_slug = $deleted_term->slug;
    $deleted_term_meta_name = $deleted_term_slug.'_'.$term_id;
    if(soya_is_term_child($term_id)){
        $parent_term_id = soya_get_term_parent_id($term_id);
        $parent_term = get_term($parent_term_id, 'soya-range');
        $parent_term_slug = $parent_term->slug;
        $deleted_term_meta_name = $parent_term_slug.'_'.$parent_term_id.'-'.$deleted_term_slug.'_'.$term_id;
    }  
    if(current_user_can('manage_options'))
        remove_meta_box($deleted_term_meta_name, 'soya_feature', 'normal');
}

function delete_meta_fields_not_needed($object_id, $tt_id){
   // if (get_post_type($object_id) === 'soya_feature' && (is_tax('soya-range', $tt_id))){
        $term = get_term($tt_id);
        $term_slug = $term->slug;
        $term_meta_key = $term_slug.'_'.$tt_id;
        if (soya_is_term_child($tt_id)){
            $term_parent_id = soya_get_term_parent_id($tt_id);
            $parent_term_slug = get_term($term_parent_id);
            $parent_term_depth = soya_get_term_custom_field($term_parent_id, 'soya_depth_meta');
            for($i = 1; $i <= $parent_term_depth; $i++){
                delete_post_meta($object_id, $i.'_'.$term_meta_key.'-'.$parent_term_slug.'_'.$term_parent_id);
            }
           // $term_meta_key = 
      //  }else{
            delete_post_meta($object_id, 'silicone-effect_200-400');
       // }
    }
    
}
add_action( 'delete_term_relationships', 'delete_meta_fields_not_needed');