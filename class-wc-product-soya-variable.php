<?php
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
 
/**
  8:  * Variable Product Class
  9:  *
 10:  * The WooCommerce product class handles individual product data.
 11:  *
 12:  * @class       WC_Product_Soya_Variable
 13:  * @version     1.0.0
 14:  * @package     WooCommerce/Classes/Products
 15:  * @category    Class
 16:  * @author      Eliasu Abraman
 17:  */
include plugin_dir_path( __FILE__ ).'class-features.php';
    
    class WC_Product_Soya_Variable extends WC_Product {
        /** 
         * Variables of Soya Variable
         */
        private $_features_id;
        public $all_features;





        /**
     27:      * Constructor
     28:      *
     29:      * @param mixed $product
     30:      */
        public function __construct( $product ) {
             $this->product_type = 'soya_variable';
             parent::__construct( $product );
             //grap and assign product features ID
             $this->_features_id = get_post_meta($product->ID, '_soya_features', true);
             // get all features assigned to product features
            $features = new Soya_feature($this->_features_id);
            //make sure all features are updated
          //  parent::$all_features->update_feature_meta(64, 1);
            //set total features price
            $features->set_total_features_price();
            $this->set_regular_price();
            
            $this->all_features = $features;
           //add_filter('woocommerce_get_regular_price', array($this, 'set_regular_price'));
         }
        public function get_feature_price($term_slug){
            
            if( ! is_term($term_slug, 'soya-range'))
                return;
            if(soya_term_has_children($term_slug))
                return;
           // $this->feature_price = get_post_meta($this->_features_id, $this->get_feature_meta_key($term_slug), true);
        }
        function get_all_product_features(){
            return $this->all_features;
        }


        //Get a meta key from term
        


        // set regular price
        public function set_regular_price(){
             $total_features_price = 0;
             $all_features = $this->get_all_product_features();
             foreach($all_features->default_features as $id => $feature){
                //$total_features_price += $feature['default_price'];
                 $price = $all_features->set_feature_price($id);
                 $total_features_price += $price;
             }
            // print_r($price);
             $this->regular_price = $total_features_price;
             //return $price;
        }
         public function get_regular_price(){
             return $this->all_features->total_features_price;
         }
         
    }


//add_filter( 'product_type_selector', 'soya_add_custom_product' );
global $product;
new WC_Product_Soya_Variable($product);