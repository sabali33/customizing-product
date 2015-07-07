<?php

/* 
 * This is a file that runs various WordPress fucntions.
 */

// include fields functions
include plugin_dir_path( __FILE__ ).'fields-functions.php';


//Register and localize scripts

function soya_enqueue_scripts($hook){
  //  if(('post-new.php' === $hook) || ('post.php' === $hook)){
        wp_enqueue_script('jquery');
        //wp_enqueue_script('vertical-tabs', 'http://code.jquery.com/ui/1.9.1/jquery-ui.js');
        wp_enqueue_script('soya-js', plugin_dir_url( __FILE__ ).'js/soya-js.js', array('jquery'));
       
        wp_register_style( 'soya_wp_admin_css',  plugin_dir_url( __FILE__ ).'css/soya-style.css', false, '1.0.0' );
      //  wp_enqueue_style( 'soya_wp_admin_css' 
        $soya_nonce = wp_create_nonce( 'soya-ajax-nonce' );
        $data = array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'nonce'   =>  $soya_nonce,
          
      );
        wp_localize_script('soya-js',   'soya',   $data);

       // return;
  //  }
    
}
add_action('admin_enqueue_scripts','soya_enqueue_scripts');

//receive ajax request on a server
add_action('wp_ajax_soya_range',    'soya_process_ajax_request');
function soya_process_ajax_request(){
    check_ajax_referer('soya-ajax-nonce', 'nonce');
    if(isset($_POST)){
      $depth = $_POST['depth'];
      $class_test = ($_POST['range_type'] === 'class') ? true : false;
      echo '<table>';
      if($class_test){
      ?>
      <th><?php echo _e('Level','class','soya-customize-product');?></th>
      <th><?php echo _e('Lower','class','soya-customize-product');?></th>
      <th><?php echo _e('Upper','class','soya-customize-product');?></th>
      <?php
      for ($i = 1;  $i <= $depth; $i++){?>
        <tr>
            <td><?php printf(__('Level %s','class','soya-customize-product'),$i);?></td>
            <td><input type="number" name="<?php echo 'term_meta[soya_lower_'.$i.'_depth_meta]'; ?>" class="form-field soya-depth-count" value="" ></td>
            <td><input type="number" name="<?php echo 'term_meta[soya_upper_'.$i.'_depth_meta]'; ?>" class="form-field soya-depth-count" value="" ></td>

        </tr> 
      <?php  }
     }else{
            for($i = 1; $i <= $depth;   $i++){
            ?>
               <tr>
               <td><?php printf(__('Level %s','class','soya-customize-product'),$i);?></td>
               <td><input type="number" name="<?php echo 'term_meta[soya_lower_'.$i.'_depth_meta]'; ?>" class="form-field soya-depth-count" value="" ></td>


           </tr> 
   <?php

            }
         }
       //soya_display_custom_box();
   }
   die();
      
  
}

// Prepare range for displaying
function soya_process_range_depth($term_id){
    
    $term_meta = get_option('taxonomy_'.$term_id);
               
    $processed_parent_depth = soya_loop_term_depth( $term_meta);
    
   
    
    // Check for children ranges
    if(!empty(get_term_children($term_id, 'soya-range'))){
        
        $children = get_term_children($term_id, 'soya-range');
        //  print_r($children);
       $processed_child_depth =  soya_process_child_ranges($children);
    
    }
    // all of them
    $all_depth = array('parent' => $processed_parent_depth, 'children' => $processed_child_depth);
            
    return $all_depth;
}

// Prepare child ranges
function soya_process_child_ranges($child_term_ids){
    $all_children_terms = [];
    if(count($child_term_ids) === 1){ 
        $term_meta = get_option('taxonomy_'.$child_term_ids[0]);
                       
        $all_children_terms = soya_loop_term_depth( $term_meta );
        
    }elseif (count($child_term_ids > 1)) {
        if(!is_array($child_term_ids))            return;
      
        foreach($child_term_ids as $child_term_id){
            
            $term_meta = get_option('taxonomy_'.$child_term_id);
            
            $all_children_terms[] = soya_loop_term_depth($term_meta);
        } 
    }
    // return $depth;
    return $all_children_terms;
      
    
}
function soya_loop_term_depth( $term_id ){
        
       
    $processed_depth = [];
    $term = get_term($term_id, 'soya-range');
    $term_slug = $term->slug;
    $depth_meta = esc_html(soya_get_term_custom_field($term_id, 'soya_depth_meta'));
    $depth_meta = !empty($depth_meta) ? $depth_meta : 2 ;
        
    //$range_name = esc_html( soya_get_term_custom_field( $term_id, 'soya_depth_name_meta' ) );
    $depth_unit = soya_get_term_custom_field( $term_id, 'soya_depth_unit_meta');    
    $range = (soya_get_term_custom_field( $term_id,'soya_range_type_meta') === 'class') ? true : false;
    
    if( $depth_meta <= 0)        return;
    
    if($range){
        for( $i = 1; $i <= $depth_meta; $i++){
            $lower_value = soya_get_term_custom_field( $term_id,'soya_lower_'.$i.'_depth_meta');
            $upper_value = soya_get_term_custom_field( $term_id,'soya_upper_'.$i.'_depth_meta');
            //$depth_unit = (($lower_value === 1) || ($upper_value > 1)) ? $depth_unit.'s' : $depth_unit;
            $processed_depth[$term_slug.'_'.$lower_value.'-'.$upper_value] .= sprintf(__(' %2$s to %3$s%1$s', 'soya-customize-product'), $depth_unit, $lower_value, $upper_value);
        }
    }  else {
        for($i = 1; $i <= $depth_meta; $i++){
            
            $lower_value = soya_get_term_custom_field( $term_id,'soya_lower_'.$i.'_depth_meta');
            //$depth_unit = ( $lower_value === 1 ) ? $depth_unit : $depth_unit.'s';
            $processed_depth[$term_slug.'_'.$i] .= sprintf(__('%3$s %1$s %2$s', 'soya-customize-product'), $lower_value, $depth_unit, $range_name);
        }
    }
    
    return $processed_depth;
    
}

//process range edit page Ajax
function soya_process_edit_ajax(){
    check_ajax_referer('soya-ajax-nonce', 'nonce');
    if(isset($_POST)){
      $depth = $_POST['depth'];
    //  soya_edit_range_tax_fields('', false);
      die();
    }
}
add_action('wp_ajax_soya_update_edit', 'soya_process_edit_ajax');

function soya_build_custom_meta($terms){
    
   if(isset($terms['children'])){
                foreach ($terms['children'] as $child_term){
                   soya_build_single_meta_box($child_term);
                   // print_r($child_term);  
                    //}
                }
            }
}
function soya_build_single_meta_box($single_term){
    foreach ($single_term as $key => $value){
        //include plugin_dir_path( __FILE__ ).'/custom-fields.php';
    }
}
// get term unique name for terms used for custom meta box
function soya_get_terms_unique_name(){
   
    $all_terms = soya_convert_terms_to_array();
    $terms_unique_name = [];
    
    foreach ($all_terms as $term){
        $term_id = $term['term_id'];
        $label = $term['name'];
        $term_slug = $term['slug'];
        $terms_unique_name[$term_slug.'_'.$term_id] = $label;
                
    }
    return $terms_unique_name;
   
}

/*

 * Convert terms objects to array
 *  */
function soya_convert_terms_to_array(){
    global $post;
    $post_tax_object = wp_get_post_terms($post->ID,'soya-range');
   
    $post_terms = [];
    if(isset($post_tax_object)){  
        foreach ($post_tax_object as $indexi => $nw_term){
            foreach ($nw_term as $key => $value){
                $post_terms[$indexi][$key] = $value;
            }

        }
    }  
     //   $subset = array_intersect($post_terms, $all_terms);
        
        return $post_terms;
}

// check if a range  term is parent
function soya_is_term_child($term_id){
    $term = get_term($term_id, 'soya-range');
    if($term->parent)
        return true;
    
}
// Get the parent id of a child term
function soya_get_term_parent_id($child_term_id){
    $term = get_term($child_term_id, 'soya-range');
    return $term->parent;
}
// return a single meta value of a term field
function soya_get_term_custom_field($term_id, $meta_name){
    $term_meta = get_option('taxonomy_'.$term_id);
    $meta_value = $term_meta[$meta_name];
    return $meta_value;
}

//get Depth level label by term id. It returns an array labels for terms

function soya_get_term_depth_levels_by_id($term_id, $depth){
  $range_type = soya_get_term_custom_field($term_id, 'soya_range_type_meta');
 // $range_name = soya_get_term_custom_field($term_id, 'soya_depth_name_meta');
  
  $range_type_bool = ($range_type === 'class') ? true : false;
  $processed_depth = [];
  if($range_type_bool){
        for( $i = 1; $i <= $depth; $i++){
            
            $lower_value = soya_get_term_custom_field($term_id, 'soya_lower_'.$i.'_depth_meta');
            
            $upper_value = soya_get_term_custom_field($term_id, 'soya_upper_'.$i.'_depth_meta');
            //$depth_unit = (($lower_value === 1) || ($upper_value > 1)) ? $depth_unit.'s' : $depth_unit;
            $processed_depth[] .= sprintf(__(' %2$s to %3$s%1$s', 'soya-customize-product'), $depth_unit, $lower_value, $upper_value);
        }
    }  else {
        for($i = 1; $i <= $depth; $i++){
            
            $lower_value = soya_get_term_custom_field($term_id, 'soya_lower_'.$i.'_depth_meta');
            //$depth_unit = ( $lower_value === 1 ) ? $depth_unit : $depth_unit.'s';
            $processed_depth[] = sprintf(__('%3$s %1$s %2$s', 'soya-customize-product'), $lower_value, $depth_unit, $range_name);
        }
    }
    return $processed_depth;
}
//Get last two chars of a string
function soya_get_last_two_char($str){
    $str_length = strlen($str);
    $last_two_char = substr($str, ($str_length-2));
    return $last_two_char;
}

function identify_parent_term_with_child(array $child_terms, $parent_term_id){
    $checker = true;
    foreach ( $child_terms as $child_term){
        if( $child_term['parent'] === $parent_term_id){
            $checker = false;
        }else{
            $checker = true;
        }
    }
    return $checker;
}

//get post terms ids
function soya_get_post_terms_ids($post_id){
    $post_terms = wp_get_post_terms($post_id, 'soya-range');
    $post_terms_ids = [];
    if(isset($post_terms)){
        foreach ($post_terms as $post_term){
            $post_terms_ids[] = $post_term->term_id;
        }
    }
    return $post_terms_ids;
}
function soya_add_mold_cost_field($arg, $post_id){ 
    $parent_child_ids = explode('-', $arg);
    if(count($parent_child_ids) === 3):
               
                $index     = $parent_child_ids[2];
                $index = $index.'-';
               
                           
            endif;
    ?>
    <tr>
            <th><?php echo _e('Mold Cost', 'soya-customize-product'); ?> </th>
            <td>
               
                <input type="number" value="<?php echo get_post_meta($post_id, $index.'fixed-cost-dimension', true); ?>" name="<?php echo $index.'fixed-cost-dimension'?>"/>
                
            </td>
            
        </tr>
    <?php
}
//add_action('soya_add_extra_meta_fields_dimension' , 'soya_add_mold_cost_field', 10, 2);
// to be visited( not complete)
function soya_filter_meta_keys_callback($meta_keys, $post_terms_ids){
    if (is_array($post_terms_ids)){
        foreach ($post_terms_ids as $term_id){
            $term = get_term($term_id, 'soya-range');
            if($term->slug === 'dimension'){
                $depth = soya_get_term_custom_field($term_id, 'soya_depth_meta');
                for( $i = 1; $i <= $depth; $i++){
                    $meta_keys[$i.'-fixed-cost-dimension'];
                }
            }
        
        }
    }
    return $meta_keys;
}
//add_filter('soya_filter_meta_keys', 'soya_filter_meta_keys_callback', 10, 2);

function soya_get_prev_post_terms($post_id){
   // $_SESSION['soya'] = $post_id; 
    $post_terms_before = wp_get_post_terms($post_id, 'soya-range');
    //$_SESSION['post_terms'] = $post_term_before;
    //retrieve meta keys before just before updating
    $before_post_meta_keys = [];
    $parent_terms_keys = [];
    $child_terms_parent_keys = [];
    foreach ($post_terms_before as $term){
        $term_id = $term->term_id;
        $term_slug = $term->slug;
        if(soya_is_term_child($term_id)){
            $term_parent_id = soya_get_term_parent_id($term_id);
            $term_parent_range = soya_loop_term_depth($term_parent_id);
            $child_term_depth = soya_get_term_custom_field($term_id, 'soya_depth_meta');
            $term_parent_range['slug'] = $term_slug;
            $term_parent_range['depth'] = $child_term_depth;
            $child_terms_parent_keys [] = $term_parent_range;
        }else{
            $term_parent_range = soya_loop_term_depth($term_id);
            $parent_terms_keys[] = $term_parent_range;
        }
    }
    // form the meta keys
    if(isset($child_terms_parent_keys)){
        foreach ($child_terms_parent_keys as $term_parent_range){
            $term_depth = $term_parent_range['depth'];
            $term_slug = $term_parent_range['slug'];
            for($i = 1; $i<= $term_depth; $i++){
                unset($term_parent_range['slug']);
                unset($term_parent_range['depth']);
                foreach ($term_parent_range as $key => $label ){
                    $before_post_meta_keys[] = $i.'-'.$term_slug.'-'.$key;
                }
            }
            
        }
    }
    if(isset($parent_terms_keys)){
        foreach ($parent_terms_keys as $term_parent_range){
            foreach ($term_parent_range as $key => $label){
                $before_post_meta_keys[] = $key;
            }
        }
    }
    $_SESSION['post_terms'] = $before_post_meta_keys;
    
}
add_action('pre_post_update', 'soya_get_prev_post_terms');
// start a session to track post terms and delete un wanted post terms


add_action('admin_init', 'soya_start_session');

function soya_start_session(){
    
      session_start();
      //unset($_SESSION['soya']);
}
function soya_is_term_range($term_id){
    $range = soya_get_term_custom_field($term_id, 'soya_range_type_meta');
    $check = ($range === 'class') ? true : false;
    return $check;
}
//is term parent
function soya_term_has_children($term_id){
    $term_children = get_term_children($term_id, 'soya-range');
    if(!empty($term_children)){
       return true; 
    }  else {
        return false;
    }
}

function soya_get_term_range($term_id, $indices){
    $range = [];
    if(is_array($indices)){
        if(soya_is_term_range($term_id)){
            foreach ($indices as $index){
                $lower_range = soya_get_term_custom_field($term_id, 'soya_lower_'.$index.'_depth_meta');
                $upper_range = soya_get_term_custom_field($term_id, 'soya_upper_'.$index.'_depth_meta');
                $range[] = $lower_range.'-'.$upper_range; 
            }

        }else{
            foreach ($indices as $index){
                $lower_range = soya_get_term_custom_field($term_id, 'soya_lower_'.$index.'_depth_meta');
                $range[] = $lower_range;
            }
        }
    
    }else{
        for($i = 1; $i <= $indices; $i++){
            $lower_range = soya_get_term_custom_field($term_id, 'soya_lower_'.$i.'_depth_meta');
            $upper_range = (soya_is_term_range($term_id)) ? soya_get_term_custom_field($term_id, 'soya_upper_'.$i.'_depth_meta') : '';
            $range[$i] = empty($upper_range) ? $lower_range : $lower_range.'-'.$upper_range; 
        }
    }
    return $range;
}
// callback to delete unwanted values
function soya_delete_unwanted_range_values_callback($unwanted_meta_keys, $term_relationship, $child_terms_id){
    
    if($term_relationship !== 'parent'){
        return;
    }
    $term_id = key($unwanted_meta_keys);
        
        //$_SESSION['unwanted_term_meta_fields'] = $child_terms_id;
    //set terms ids to query
    
    $terms_id = !empty($child_terms_id) ? $child_terms_id : $term_id ;  
    
        $args = array(
            'post_type' => 'soya_feature',
            
            'tax_query' => array(
                    array(
                            'taxonomy' => 'soya-range',
                            'terms'    => $terms_id
                        )
                )
        );
        $query = new Wp_Query($args);

        if($query->have_posts()):
            while ($query->have_posts()): $query->the_post();

                foreach ($unwanted_meta_keys[$term_id] as  $meta_key){
                    
                    if(soya_meta_key_exists($meta_key, get_the_ID())){
                       
                        delete_post_meta(get_the_ID(), $meta_key);
                    }
                            

                }
            endwhile;
        endif;
    
   
}
add_action('delete_unwanted_range_values', 'soya_delete_unwanted_range_values_callback', 10, 3);

function delete_unwanted_range_values_callback($unwanted_indices, $term_relationship){
    
    if($term_relationship !== 'child'){
        return;
    }
  
    // Prepare meta keys from args
    $term_id = key($unwanted_indices);
    $term = get_term($term_id, 'soya-range');
    $term_parent_id = soya_get_term_parent_id($term_id);
    $parant_term = get_term($term_parent_id, 'soya-range');
    $parent_depth = soya_get_term_custom_field($term_parent_id, 'soya_depth_meta');
    $post_meta_keys = [];
    foreach ($unwanted_indices[$term_id] as $value){
        if(!is_array($value)){
            return;
            
        }
            extract($value);
             //loop each meta key through the size of the parent depth
            for($i = 1; $i<= $parent_depth; $i++){
            
            $parent_lower = soya_get_term_custom_field($term_parent_id, 'soya_lower_'.$i.'_depth_meta');
            $parent_upper = soya_get_term_custom_field($term_parent_id, 'soya_upper_'.$i.'_depth_meta');
            $parent_upper_prefix = ($range_type === 'class') ? '-'.$parent_upper : '';
            
            $meta_key  = $index.'-'.$term->slug.'-'.$parant_term->slug.'_'.$parent_lower.$parent_upper_prefix;
            $post_meta_keys[] = $meta_key;
           }
              
    }
    //get all post of this term and delete unwanted meta keys
    $args = array(
	'post_type' => 'soya_feature',
	'tax_query' => array(
		array(
			'taxonomy' => 'soya-range',
                        'terms'    => $term_id
                    )
            )
        );
        $query = new Wp_Query($args);
        
        if($query->have_posts()):
            //loop posts of the term
            while ($query->have_posts()): $query->the_post();
            //iterate through the meta keys
            foreach ($post_meta_keys as  $meta_key){
               
                if(soya_meta_key_exists($meta_key, get_the_ID())){
                    
                    delete_post_meta(get_the_ID(), $meta_key);
                }
                  
            }
            endwhile;
        endif;
     
}
add_action('delete_unwanted_range_values', 'delete_unwanted_range_values_callback', 11, 2);

function delete_unwanted_range_values_callbk($unwanted_fields, $check){
    if($check !== 'range_change'){
        return;
    }
    $term_id = key($unwanted_fields);
     
    $args = array(
	'post_type' => 'soya_feature',
	'tax_query' => array(
		array(
			'taxonomy' => 'soya-range',
                        'terms'    => $term_id
                    )
            )
        );
        $query = new Wp_Query($args);
        
        if($query->have_posts()):
            //loop posts of the term
            while ($query->have_posts()): $query->the_post();
            //iterate through the meta keys
            foreach ($unwanted_fields[$term_id] as  $meta_key){
                
                if(soya_meta_key_exists($meta_key, get_the_ID())){
             
                    delete_post_meta(get_the_ID(), $meta_key);
                }
                  
            }
            endwhile;
        endif;
        
  //   foreach ($unwanted_fields[$term_id] as $range_value){
         
  //   }
  //  $_SESSION['unwanted_term_meta_fields'] = $unwanted_fields;
    
}
add_action('delete_unwanted_range_values', 'delete_unwanted_range_values_callbk', 11, 2);

// function to return child terms meta keys
function soya_parent_meta_key($parent_term_slug, $child_term_ids, $parent_range){
    $meta_keys = [];
    if(is_array($child_term_ids)){
        foreach ($child_term_ids as $term_id){
           $child_term = get_term($term_id, 'soya-range');
           $child_term_depth = soya_get_term_custom_field($term_id, 'soya_depth_meta');
           for($i = 1; $i <= $child_term_depth; $i++){
               $meta_keys[] = $i.'-'.$child_term->slug.'-'.$parent_term_slug.'_'.$parent_range;
           }
        }
    }
    return $meta_keys;
}

// Check if meta key exist
function soya_meta_key_exists($meta_key, $post_id){
   $post_meta_keys = get_post_meta($post_id);
   $check = array_key_exists($meta_key, $post_meta_keys) ? true : false;
   return $check;
}
//Delete meta keys before a term is deleted
function soya_make_meta_keys($term_id){
    $term = get_term($term_id, 'soya-range');
    $meta_keys = [];
    //For parent terms with children
    if(soya_term_has_children($term_id)):
        $term_depth = soya_get_term_custom_field($term_id, 'soya_depth_meta');
    
        $term_ranges = soya_get_term_range($term_id, $term_depth);
        
        $child_terms_id = get_term_children($term_id, 'soya-range');
    
        foreach ($child_terms_id as $child_term_id){
            $child_term = get_term($child_term_id, 'soya-range');
            $child_term_depth = soya_get_term_custom_field($child_term_id, 'soya_depth_meta');
            for( $i = 1; $i <= $child_term_depth; $i++){
                
                foreach($term_ranges as $range){
                    
                $meta_keys[] = $i.'-'.$child_term->slug.'-'.$term->slug.'_'.$range;
                
                }
            }
        }
    endif;
    //if term is child
    if(soya_is_term_child($term_id)):
        $term_parent_id    =    soya_get_term_parent_id($term_id);
        $parent_term       =    get_term($term_parent_id, 'soya-range');
        $parent_term_depth =    soya_get_term_custom_field($term_parent_id, 'soya_depth_meta');
        $parent_ranges     =    soya_get_term_range($term_parent_id, $parent_term_depth);
        $term              =    get_term($term_id, 'soya-range'); 
        $term_depth = soya_get_term_custom_field($term_id, 'soya_depth_meta');
        for( $i = 1; $i <=$term_depth; $i++){
            
            foreach ($parent_ranges as $range){
                
                $meta_keys[] = $i.'-'.$term->slug.'-'.$parent_term->slug.'_'.$range;
                
            }
        }
    endif;
    // For no child term parent
    
    if(!soya_term_has_children($term_id) && !soya_is_term_child($term_id)){
       $term = get_term($term_id, 'soya-range');
       $term_depth = soya_get_term_custom_field($term_id, 'soya_depth_meta');
       $term_ranges = soya_get_term_range($term_id, $term_depth);
       foreach ($term_ranges as $range){
           $meta_keys[] = $term->slug.'_'.$range;
       }
    }
    return $meta_keys;
}
// delete meta keys for all post that belong to term before deleting the term.
function soya_delete_term_post_meta_keys($term_id, $tax){
    if($tax !== 'soya-range'){
        return;
    }
    $meta_keys[$term_id] = soya_make_meta_keys($term_id);
    if(soya_term_has_children($term_id) && !soya_is_term_child($term_id)){
        $term_children = get_term_children($term_id, 'soya-range');
        
        do_action('delete_unwanted_range_values', $meta_keys, 'parent', $term_children);
    }else{
            
        do_action('delete_unwanted_range_values', $meta_keys, 'range_change');
    }
    
}
add_action('pre_delete_term', 'soya_delete_term_post_meta_keys', 10, 2);

function soya_get_meta_keys_by_range($term_id, array $ranges){
    $meta_keys = [];
   
    $term = get_term($term_id, 'soya-range');
    $term_children = get_term_children($term_id, 'soya-range'); 
    if(!empty($term_children)){
        foreach ($term_children as $child_term_id){
            $child_term = get_term($child_term_id, 'soya-range');
            $child_term_depth = soya_get_term_custom_field($child_term_id, 'soya_depth_meta');
            for ($i = 1; $i<= $child_term_depth; $i++){
                foreach ($ranges as $range){
                    $meta_keys[] = $i.'-'.$child_term->slug.'-'.$term->slug.'_'.$range;
                }
            }
        }
    }else{
        // for non-child parent term
        foreach ($ranges as $range){
        $meta_keys[] = $term->slug.'_'.$range;  
        }
    }
    return $meta_keys;
}
//test
function deleted_term_relationships_callback($object_id, $tt_id){
    $_SESSION['unwanted_term_meta_fields'] = $object_id;
}
add_action('deleted_term_relationships', 'deleted_term_relationships_callback');


