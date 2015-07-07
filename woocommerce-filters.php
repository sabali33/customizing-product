<?php

/* 
 * A place where woocommerce is filtered
 */

// add html elements to product page
function add_gift_wrap_field() {
	echo '<table class="variations" cellspacing="0">
			<tbody>
				<tr>
					<td class="label"><label>Gift Wrap It</label></td>
					<td class="value">
						<label><input type="checkbox" name="option_gift_wrap" value="YES" /> This will add 100/- extra</label>						
					</td>
				</tr>	        					
		    </tbody>
		</table>';
}
add_action( 'woocommerce_before_add_to_cart_button', 'add_gift_wrap_field' );

function remove_post_custom_fields() {
	remove_meta_box( 'commentsdiv' , 'product' , 'normal' ); 
}
add_action( 'do_meta_boxes' , 'remove_post_custom_fields' );

/*

 * Woocommerce integration starts here
 *  */


function soya_woo_add_product_data_tab ( $product_types ) {

	$product_types['soya_variable']	= __('Customizing Product', 'soya-customizing-product');// Rename the additional information tab
        //print_r($product_types);
	return $product_types;

}
add_filter( 'product_type_selector', 'soya_woo_add_product_data_tab', 98 );
//Include a class for soya variable product.
add_action('plugins_loaded', 'soya_add_product_type');
function soya_add_product_type(){
    if(class_exists('WC_Product')){
        include_once  plugin_dir_path( __FILE__ ).'class-wc-product-soya-variable.php';
    }
}
//
add_action('woocommerce_product_data_tabs', 'soya_add_wc_product_data_tab');
function soya_add_wc_product_data_tab($tabs){
    $tabs['soya_variable'] = array(
        'label' =>  __('Customizing Product', 'soya-customize_product'),
        'target' => 'soya_variable_product',
        'class'  => array( 'soya_variation_tab', 'show_if_soya_variable', 'hide_if_simple', 'hide_if_grouped', 'hide_if_variable')
    );
    return $tabs;
}

//display soya variation panel
add_action('woocommerce_product_data_panels', 'soya_display_sv_panel');
function soya_display_sv_panel(){
    
   
    ?>
        
        <div id="soya_variable_product" class="panel woocommerce_options_panel ">
            <div class=" options_group show_if_soya_variable">
            <?php 
            // build query to get all features
            $args = array(
                'post_type'        => 'soya_feature',
                'orderby'          => 'title',
                'order'            => 'ASC'
            );
            
            $query = new WP_Query($args);
            
            if($query->have_posts()): 
              $options = [];
              foreach ($query->posts as $post){
                $options[$post->ID] = $post->post_title;
            }
                
            endif;
            woocommerce_wp_select(array('id'=>'_soya_features', 'label' => __('Select Features to Use', 'soya-customiza-product'), 'options' => $options));
            woocommerce_wp_text_input( array( 'id' => '_regular_price', 'label' => __( 'Regular Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price' ) );
            //woocommerce_wp_text_input( array( 'id' => '_sale_price', 'data_type' => 'price', 'label' => __( 'Sale Price', 'woocommerce' ) . ' ('.get_woocommerce_currency_symbol().')', 'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'woocommerce' ) . '</a>' ) );
            ?>
            </div>
        </div>
    <?php
}
add_action('woocommerce_process_product_meta_soya_variable', 'woocommerce_process_product_meta_soya_var_cb');

//Add default price field for a feature

// save features meta 
function woocommerce_process_product_meta_soya_var_cb($post_id){
    if(isset($_POST['_soya_features'])){
        update_post_meta($post_id, '_soya_features', esc_html($_POST['_soya_features']));
    }
 
}
//add_action('woocommerce_before_add_to_cart_button', 'soya_add_features_to_product_page');
add_action('woocommerce_single_product_summary', 'soya_add_features_to_product_page');
function soya_add_features_to_product_page() {
    global $product, $post;
    //make sure we are on soya variable product only
    if(!$product->is_type('soya_variable')):
        return;
    
    endif;
    $features_id = get_post_meta($post->ID, '_soya_features', true);
    if($features_id  === 'none'){
        return;
    }
    // get post range terms
    $features_terms = wp_get_object_terms($features_id, 'soya-range', array('orderby'=>'term_id'));
    
    if(is_array($features_terms)){
        echo '<ul class="features">';
        foreach ($features_terms as $index => $term){
            $field_type = soya_get_term_custom_field($term->term_id, 'soya_display_type_meta');
            echo    '<li>';
            soya_get_term_options($term->term_id, $field_type);
            echo '</li>';
        }
        
        echo '</ul>';
    }
   
   // global $product;
   // $cp = new WC_Product_Soya_Variable($product);
    //$product->set_regular_price(65);
   $product->all_features->set_quantity(400);
    $product->all_features->update_feature_meta(64, 1);
    $product->all_features->update_feature_meta(57, 1);
    $product->all_features->update_feature_meta(58, 1);
    $product->all_features->set_total_features_price();
    echo '<pre>';
    
    var_dump($product);    echo '</pre>';
}
function soya_get_term_options($term_id, $field_type){
    
    if (soya_term_has_children($term_id)){ 
        return;
    }
    do_action('soya_get_'.$field_type.'_field',  $term_id, $field_type);
    /*
      */
            //do_action('soya_term_options_'.$id, $options);
  
    
}