<?php

/* 
 * A class for product features
 * 
 */
class Soya_feature{
    
    public  $features_ids;
    public  $all_features = [];
    public  $feature_price;
    public  $default_features = [];
    public  $default_qty = 50;
    public  $range;
    public  $total_features_price;
    public function __construct($product_features_ids) {
        $this->features_ids = $product_features_ids;
        
        $this->set_all_features_ids($product_features_ids);
        $this->set_default_features(2);
        
       // $this->features_terms = $this->get_all_features_ids($product_features_ids);
    }
    public function set_all_features_ids($product_features_ids){
            
        $features_terms = wp_get_post_terms($product_features_ids, 'soya-range');
        // get defaults before
        $all_features = [];
        foreach ($features_terms as $feature){
            
           
             $all_features[$feature->term_id] = array( 'slug' => $feature->slug);
            
           
        }
            $all_features = apply_filters('soya_all_product_features', $all_features);
            
            $this->all_features = $all_features;       
                   
    }
    
    public function get_all_features_ids(){
        return $this->all_features;
    }
    //get priced features
    public function set_default_features($index){
        
        foreach ($this->get_all_features_ids() as $id => $feature){
            
            $feature_default_status = soya_get_term_custom_field($id, 'soya_term_default_meta');
             
            if($feature_default_status){
                //declare priced feaures 
                $this->default_features[$id] = array( 'slug' => $feature['slug']);
                // set quantity
                $this->set_quantity($this->get_quantity());
                //set default features price 
                $this->set_priced_feature_meta_keys($id, $index);
                // set default total price 
               // $this->set_total_features_price();
                 
            }
        }
    }
    public function get_all_priced_features(){
        return $this->default_features;
    }

    public function get_field_value($term_id){
        
    }

    public function set_priced_feature_meta_keys( $term_id, $index ){
        
           
            if(soya_term_has_children($term_id)){
                return;
            }elseif(soya_is_term_child($term_id)){
                // set range
                $this->set_feature_range( $term_id );
                // set feature meta key
                $this->set_feature_meta_key($term_id, $index );
                // set feature price
                $this->set_feature_price($term_id);
                
                
              
            }elseif (!soya_term_has_children($term_id) && !soya_is_term_child($term_id)) {
                
                $this->set_feature_meta_key($term_id, $index);
                
                $this->set_feature_price($term_id);
                
            
        }
          
    }
    public function set_feature_meta_key($term_id, $index ){
        
        $feature_meta_key = $this->get_feature_meta_key_by_index($term_id, $index);
       
        $this->default_features[$term_id]['feature_meta_key'] = $feature_meta_key;
                     
       
    }
    // Get feature meta key by index
    public function get_feature_meta_key_by_index($term_id, $index, $range=true){
    
    $term  =  get_term($term_id, 'soya-range');
    if(soya_term_has_children($term_id)){
        return;
        }elseif(soya_is_term_child($term_id)){
                                
            $parent_term_id = soya_get_term_parent_id($term_id);
            $parent_term = get_term($parent_term_id, 'soya-range');
            $range_value =  ($range) ? '_'.$this->get_feature_range() : '';
            
            $feature_meta_key = $index.'-'.$term->slug.'-'.$parent_term->slug.$range_value;
                    
                                        
        }  else {
                $feature_meta_key = $term->slug.'_'.$index;
        }
        return $feature_meta_key;
    }
    // get feature meta key
    public function get_feature_meta_key($term_id){
        return $this->default_features[$term_id]['feature_meta_key'];
    }
    public function set_quantity($qty){
      //  $qty_filtered = apply_filters('soya_default_quantity', $qty);
        $this->default_qty = $qty;
    }

    public function get_quantity(){
        return $this->default_qty;
    }
    public function set_feature_price($term_id){
        
        $feature_price = get_post_meta($this->features_ids, $this->default_features[$term_id]['feature_meta_key'], true);
        $feature_price = floatval(esc_html($feature_price));
        $this->default_features[$term_id]['default_price'] = $feature_price;
       
    }
    public function get_feature_price($term_id){
        return $this->default_features[$term_id]['default_price'];
    }

    public function set_feature_range($term_id){
      //  $set_range = '';
        if(soya_is_term_child($term_id)){
            
           $ranges = $this->get_parent_ranges($term_id);
           
           foreach ($ranges as $key => $range){
        
                $key = intval($key);
                
                /*
                * get the first range at which quantity is greater than or equal to. The position of comparing
                * the two values is important
                */
                
                if ($this->default_qty >= $key  ){
                    $this->range = $range;
                   // break;
                    
                }
               
           }    
         //return $this->default_qty;   
        }  else {
        return false;    
        }
       
    }
    // get range
    function get_feature_range(){
       
       return $this->range;
    }
    // Get all levels of parent
    public function get_parent_ranges($term_id){
        if (!soya_is_term_child($term_id)){
            return false;
        }
        $ranges = [];
        $parent_term_id = soya_get_term_parent_id($term_id);
        
        $depth = soya_get_term_custom_field( $parent_term_id, 'soya_depth_meta' );
        
        for($i = 1; $i <= $depth; $i++){
            $lower = soya_get_term_custom_field( $parent_term_id, 'soya_lower_'.$i.'_depth_meta' );
            
            $upper = soya_get_term_custom_field( $parent_term_id, 'soya_upper_'.$i.'_depth_meta' );
            
            $ranges[$lower]  = !empty($lower && $upper ) ? $lower.'-'.$upper : 0 ;
        }
        return $ranges;
        //return $this->default_qty;
    }
    public function update_feature_meta($term_id, $index){
      
            //update range
            $this->set_feature_range($term_id);
            // update meta key
            $this->set_feature_meta_key($term_id, $index);
            //update price
            $this->set_feature_price($term_id);
       
    }
    public function get_feature_meta($term_id){
        return $this->default_features[$term_id];
    }
    //get price by field key
    public function get_feature_price_by_field_key($field_key){
        $field_key_arr = explode('_', $field_key);
        $feature_id = intval($field_key_arr[1]);
        return $this->default_features[$feature_id]['default_price'];
    }
    // get total price for all features
    public function set_total_features_price(){
             $total_features_price = 0;
             foreach($this->default_features as $id => $feature){
                $total_features_price += $feature['default_price'];
             }
             $this->total_features_price = $total_features_price;
    } 
              /*
  public $features = [];
  
  public function __construct($features) {
      $this->features = $features;
  }*/
}
