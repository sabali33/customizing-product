<?php

/* 
 *Plugin Name: Advance Product Customization Plugin
 * Description: This plugin makes high level product customization. It is deal for allowing customers to do what ever
 * they want to do to a product and order it.
 */


include plugin_dir_path( __FILE__ ).'woocommerce-filters.php';
include plugin_dir_path( __FILE__ ).'functions.php';

include plugin_dir_path( __FILE__ ).'cleanup.php';


function soya_register_product_feature() {
    $labels = array(
		'name'               => _x( 'Features', 'post type general name', 'soya-customize-product' ),
		'singular_name'      => _x( 'Feature', 'post type singular name', 'soya-customize-product' ),
		'menu_name'          => _x( 'Features', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Feature', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'feature', 'soya-customize-product' ),
		'add_new_item'       => __( 'Add New Feature', 'soya-customize-product' ),
		'new_item'           => __( 'New Feature', 'soya-customize-product' ),
		'edit_item'          => __( 'Edit Feature', 'soya-customize-product' ),
		'view_item'          => __( 'View Feature', 'soya-customize-product' ),
		'all_items'          => __( 'All Features', 'soya-customize-product' ),
		'search_items'       => __( 'Search Features', 'soya-customize-product' ),
		'parent_item_colon'  => __( 'Parent Features:', 'soya-customize-product' ),
		'not_found'          => __( 'No features found.', 'soya-customize-product' ),
		'not_found_in_trash' => __( 'No features found in Trash.', 'soya-customize-product' )
	);
    $args = apply_filters('soya_filter_features_post_type_args',array(
      'public' => true,
      'labels'  => $labels,
      'taxonomies' => array('icategory'),
      'rewrite' => array('slug' => 'feature')
    ));
    register_post_type( 'soya_feature', $args );
}
add_action( 'init', 'soya_register_product_feature' );


// add rewrite rules
function soya_rewrite_flush() {
    // First, we "add" the custom post type via the above written function.
    // Note: "add" is written with quotes, as CPTs don't get added to the DB,
    // They are only referenced in the post_type column with a post entry, 
    // when you add a post of this CPT.
    soya_register_product_feature();

    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'soya_rewrite_flush' );

//remove rewrite rules upon deactivation
function soya_pluginprefix_deactivation() {
 
     
    // Clear the permalinks to remove our post type's rules
    flush_rewrite_rules();
 
}
register_deactivation_hook( __FILE__, 'soya_pluginprefix_deactivation' );

// filter post messages for features
add_filter( 'post_updated_messages', 'soya_features_updated_messages' );
/**
 * Book update messages.
 *
 * See /wp-admin/edit-form-advanced.php
 *
 * @param array $messages Existing post update messages.
 *
 * @return array Amended post update messages with new CPT update messages.
 */
function soya_features_updated_messages( $messages ) {
	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );

	$messages['book'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Feature updated.', 'soya-customize-product' ),
		2  => __( 'Custom field updated.', 'soya-customize-product' ),
		3  => __( 'Custom field deleted.', 'soya-customize-product' ),
		4  => __( 'Feature updated.', 'soya-customize-product' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Feature restored to revision from %s', 'soya-customize-product' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Feature published.', 'soya-customize-product' ),
		7  => __( 'Feature saved.', 'soya-customize-product' ),
		8  => __( 'Feature submitted.', 'soya-customize-product' ),
		9  => sprintf(
			__( 'Feature scheduled for: <strong>%1$s</strong>.', 'soya-customize-product' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i', 'soya-customize-product' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Feature draft updated.', 'soya-customize-product' )
	);

	if ( $post_type_object->publicly_queryable ) {
		$permalink = get_permalink( $post->ID );

		$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View feature', 'soya-customize-product' ) );
		$messages[ $post_type ][1] .= $view_link;
		$messages[ $post_type ][6] .= $view_link;
		$messages[ $post_type ][9] .= $view_link;

		$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
		$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview feature', 'soya-customize-product' ) );
		$messages[ $post_type ][8]  .= $preview_link;
		$messages[ $post_type ][10] .= $preview_link;
	}

	return $messages;
}
// add settings page to Features
add_action('admin_menu', 'soya_register_settings_submenu_page');

function soya_register_settings_submenu_page() {
	add_submenu_page( 'edit.php?post_type=soya_feature', 'Settings', 'Settings', 'manage_options', 'soya-feature-settings', 'soya_feature_submenu_page_callback' );
        add_action( 'admin_init', 'soya_register_mysettings' );
}        
function soya_register_mysettings(){
    register_setting( 'soya-settings-group', 'no_of_levels_to_price' );
    register_setting( 'soya-settings-group', 'no_of_dimensions' );
    register_setting( 'soya-settings-group', 'option_etc' );
}
        // callback for settings page
function soya_feature_submenu_page_callback(){
            echo '<div class="wrap">'
            . '<div id="icon-tools" class="icon32"></div>';
            echo '<h1>Settings</h1>';?>
            <form method="post" action="options.php">
    <?php settings_fields( 'soya-settings-group' ); ?>
    <?php do_settings_sections( 'soya-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php echo _e('Numbe of Levels to Price', 'soya-customize-product');?> </th>
        <td><input type="text" name="no_of_levels_to_price" value="<?php echo esc_attr( get_option('no_of_levels_to_price') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row"><?php echo _e('Numbe of Print Dimensions', 'soya-customize-product');?></th>
        <td><input type="text" name="no_of_dimensions" value="<?php echo esc_attr( get_option('no_of_dimensions') ); ?>" /></td>
        </tr>
        <?php
        echo '<pre>';
       // print_r(get_alloptions());
        echo '</pre>';
        ?>
        <tr valign="top">
        <th scope="row">Options, Etc.</th>
        <td><input type="text" name="option_etc" value="<?php echo esc_attr( get_option('option_etc') ); ?>" /></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>    
           <?php echo '</div>';
       // global $plugin_page;
       // print_r($plugin_page);
      //  add_settings_section( 'class', 'Class', 'soya_product_customize', $plugin_page );
}

//register taxonomy for Features post type
add_action('init', 'soya_feature_hierarchical_tax');


function soya_feature_hierarchical_tax(){
    $labels = array(
        'name'              => _x( 'Print Type', 'taxonomy general name' ),
        'singular_name'     => _x( 'Print Type', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Print types' ),
        'all_items'         => __( 'All Print Types' ),
        'parent_item'       => __( 'Parent Print Type' ),
        'parent_item_colon' => __( 'Parent Print Type:' ),
        'edit_item'         => __( 'Edit Print Type' ),
        'update_item'       => __( 'Update Print Type' ),
        'add_new_item'      => __( 'Add New Print Type' ),
        'new_item_name'     => __( 'New Print Type Name' ),
        'menu_name'         => __( 'Print Type' ),
    );
    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'rewrite'           => array( 'slug' => 'print-type' ),
        'show_ui'           => true,
        'show_admin_column' => true,
    );
    register_taxonomy('soya-print-type',array('soya_feature', 'product'), apply_filters('soya_filter_print_type_tax_args',$args));
    
}

// add a fix cost tax
add_action('init', 'soya_feature_fixed_cost_tax');
function soya_feature_fixed_cost_tax(){
     $labels = array(
        'name'              => _x( 'Fixed Cost', 'taxonomy general name' ),
        'singular_name'     => _x( 'Fixed Cost', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Fixed Costs' ),
        'all_items'         => __( 'All Fixed Costs' ),
        'parent_item'       => __( 'Parent Fixed Cost' ),
        'parent_item_colon' => __( 'Parent Fixed Cost:' ),
        'edit_item'         => __( 'Edit Fixed Cost' ),
        'update_item'       => __( 'Update Fixed Cost' ),
        'add_new_item'      => __( 'Add New Fixed Cost' ),
        'new_item_name'     => __( 'New Fixed Cost Name' ),
        'menu_name'         => __( 'Fixed Cost' ),
    );
    $args = array(
        'labels'            => $labels,
        'hierarchical'      => false,
        'rewrite'           => array( 'slug' => 'fixed-cost' ),
        'show_ui'           => true,
        'show_admin_column' => true,
    );
    register_taxonomy('soya-fixed-cost',array('soya_feature'), apply_filters('soya_filter_fixed_cost_tax_args',$args));
}

//Register taxonomy for Levels 
add_action('init', 'soya_ranges_hierarchical_tax');


function soya_ranges_hierarchical_tax(){
    $labels = array(
        'name'              => _x( 'Meta Field', 'soya-customize-product' ),
        'singular_name'     => _x( 'Meta Field', 'soya-customize-product' ),
        'search_items'      => __( 'Search Meta Fields', 'soya-customize-product' ),
        'all_items'         => __( 'All Meta Fields',    'soya-customize-product' ),
        'parent_item'       => __( 'Parent Meta Field', 'soya-customize-product' ),
        'parent_item_colon' => __( 'Parent Meta Field:', 'soya-customize-product' ),
        'edit_item'         => __( 'Edit Meta Field', 'soya-customize-product' ),
        'update_item'       => __( 'Update Meta Field',  'soya-customize-product' ),
        'add_new_item'      => __( 'Add New Meta Field', 'soya-customize-product' ),
        'new_item_name'     => __( 'New Meta Field Name',    'soya-customize-product' ),
        'menu_name'         => __( 'Meta Field', 'soya-customize-product'),
    );
    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'rewrite'           => array( 'slug' => 'meta-field' ),
        'show_ui'           => true,
        'show_admin_column' => true,
    );
    register_taxonomy('soya-range', array('soya_feature', 'product'), apply_filters('soya_filter_range_tax_args',   $args));
    
}
//add Custom fields to Range tax
function soya_add_range_tax_fields(){?>
<div class="form-field term-description-wrap">
    
    <p><?php echo _e('Range Type', 'soya-customize-product') ?></p>
    
    <select name="term_meta[soya_range_type_meta]" class="soya-range-type">
        <option value="class"><?php echo _e('Class', 'soya-customize-product'); ?></option>
        <option value="non-class"><?php echo _e('Non-Class', 'soya-customize-product'); ?></option>
    </select>
    <p></p>        
    <label for="soya-depth"><?php echo _e('Depth', 'soya-customize-product'); ?></label>
    
    <input type="number" name="term_meta[soya_depth_meta]" class="form-field soya-depth" value="" id="term_meta[soya_depth_meta]" min="1">
    
    <p><?php echo _e('The number of levels that this range would have.','soya-customize-product') ?></p>
    
    <input type="text" name="term_meta[soya_depth_name_meta]" class="form-field soya-depth" value="" id="term_meta[soya_depth_name_meta]" >
    
    <p><?php echo _e('A label given to this depth, eg Level, Size.','soya-customize-product') ?></p>
    
    <input type="text" name="term_meta[soya_depth_unit_meta]" class="form-field soya-depth" value="" id="term_meta[soya_depth_unit_meta]" >
    
    <p><?php echo _e('Unit of Measurement (Singular terms only), eg Pieces, metres.','soya-customize-product') ?></p>
    
    <div class="form-field" id="soya-add-depth-fields"></div>
    
    <?php do_action('soya_add_depth_fields');?>
</div>
<?php }
add_action('soya-range_add_form_fields',    'soya_add_range_tax_fields');

function soya_edit_range_tax_fields($term, $check = true){
    
    if(!$check){
        echo 'you are getting it right';
        return;
    }
        // print out edit form for custom range fields
        $t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" );
         print_r($term_meta);
        ?>
        <tr>
            <th>
                <?php echo _e('Range Type', 'soya-customize-product') ?>
            </th>
            <td>
                <?php $selected = esc_attr($term_meta['soya_range_type_meta']); ?>
                <select name="term_meta[soya_range_type_meta]" class="soya-depth-type">
                    <option <?php if($selected === 'class') echo 'selected="selected"'; ?> value="class"><?php echo _e('Class', 'soya-customize-product'); ?></option>
                    <option <?php if($selected === 'non-class') echo 'selected="selected"'; ?> value="non-class"><?php echo _e('Non-Class', 'soya-customize-product'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="soya-depth"><?php echo _e('Depth', 'soya-customize-product'); ?></label></th>
            <td> <input type="number" name="term_meta[soya_depth_meta]" class="form-field soya-depth" value="<?php echo esc_attr($term_meta['soya_depth_meta']); ?>" id="" min="1"></td>
        </tr>
        <tr>
            <th><label for="soya-depth-name"><?php echo _e('Depth Label', 'soya-customize-product'); ?></th>
            <td><input type="text" name="term_meta[soya_depth_name_meta]" class="form-field soya-depth" value="<?php echo esc_attr($term_meta['soya_depth_name_meta']); ?>" id="term_meta[soya_depth_name_meta]" ></td>
        </tr>
         <tr>
            <th><label for="soya-depth-unit"><?php echo _e('Unit of Measurement (Singular)', 'soya-customize-product'); ?></th>
            <td><input type="text" name="term_meta[soya_depth_unit_meta]" class="form-field soya-depth" value="<?php echo esc_attr($term_meta['soya_depth_unit_meta']); ?>" id="term_meta[soya_depth_unit_meta]" ></td>
            <label> <?php echo _e('Unit of Measurement in Singular term', 'soya-customize-product'); ?> </label>
        </tr>
    <?php
    $class_test = ($term_meta['soya_range_type_meta'] === 'class') ? true : false;
    if(($term_meta['soya_depth_meta'] > 0) && $class_test){?>
        <th><?php echo _e('Level','class','soya-customize-product');?></th>
        <th><?php echo _e('Lower','class','soya-customize-product');?></th>
        <th><?php echo _e('Upper','class','soya-customize-product');?></th>
        <tbody class="depth-container">
        <?php
        for($i = 1; $i <= $term_meta['soya_depth_meta'];    $i++){
        ?>
            <tr >
                    <td><?php printf(__('Level %s','class','soya-customize-product'),$i);?></td>
                    <td><input type="number" name="<?php printf('term_meta[soya_lower_%s_depth_meta]', $i); ?>" class="form-field soya-depth-count" value="<?php echo esc_attr($term_meta['soya_lower_'.$i.'_depth_meta']);?>" ></td>
                    <td><input type="number" name="<?php printf('term_meta[soya_upper_%s_depth_meta]', $i); ?>" class="form-field soya-depth-count" value="<?php echo esc_attr($term_meta['soya_upper_'.$i.'_depth_meta']); ?>" ></td>

            </tr> 
       <?php }

    }elseif(($term_meta['soya_depth_meta'] > 0) && !$class_test){
        echo '<tbody class="depth-container">';
         for($i = 1; $i <= $term_meta['soya_depth_meta'];    $i++){?>
            <tr>
                    <td><?php printf(__('Level %s','class','soya-customize-product'),$i);?></td>
                    <td><input type="text" name="<?php printf('term_meta[soya_lower_%s_depth_meta]', $i); ?>" class="form-field soya-depth-count" value="<?php echo esc_attr($term_meta['soya_lower_'.$i.'_depth_meta']);?>" ></td>
                    

            </tr>
            
   <?php }
    }?>
            <tr>
            <th>
                <?php echo _e('Display as', 'soya-customize-product') ?>
            </th>
            <td>
                <?php 
                $input_types = apply_filters('soya_term_input_types', array(
                    'text'      => 'Text',
                    'number'    => 'Number',
                    'color'     => 'Color',
                    'select'    => 'Select',
                    'date'      => 'Date',
                    'checkbox'  => 'Checkbox',
                    'radio'     => 'Radio',
                    'range'     => 'Range'
                    )
                );
                $selected = esc_attr($term_meta['soya_display_type_meta']); 
                
                ?>
                <select name="term_meta[soya_display_type_meta]" class="soya-display-type">
                    <?php 
                foreach ($input_types as $key => $value){?>
                    <option <?php if($selected === $key ) echo 'selected="selected"'; ?> value="<?php echo $key; ?>"><?php printf(__('%s', 'input-type' ,'soya-customize-product'), $value ); ?></option>  
              <?php  }
                    ?>
                   
                </select>
            </td>
        </tr>
        <tr>
            <th><?php echo _e('Position  on product page','soya-customize-product');?></th>
            <td><input type="number" name="term_meta[soya_term_postion_meta]" class="form-field soya-depth-count" value="<?php echo esc_attr($term_meta['soya_term_postion_meta']);?>" ></td>
                     
        </tr>
        <tr>
            <th><?php echo _e('Add for default pricing','soya-customize-product');?></th>
            <?php $default_status =  esc_attr($term_meta['soya_term_default_meta']);?>
            <td><label> <?php echo _e('Yes', 'soya-customize-product')?>
                    <input type="radio" name="term_meta[soya_term_default_meta]" class="form-field soya-depth-count" value="true" <?php if($default_status) echo 'checked'; ?> >
                </label>
            </td>
            <td><label> <?php echo _e('No', 'soya-customize-product')?>
                    <input type="radio" name="term_meta[soya_term_default_meta]" class="form-field soya-depth-count" value="false" <?php if( ! $default_status) echo 'checked';?> >
                </label>
            </td>
                     
        </tr>
    </tbody>
<?php }
add_action('soya-range_edit_form_fields',    'soya_edit_range_tax_fields');

//save custom fields for Range Tax
function soya_save_range_tax_fields($term_id){
    
    
    if ( isset( $_POST['term_meta'] ) ) {
           
		$t_id            =  $term_id;
		$term_meta       =  get_option( "taxonomy_$t_id" );
		$range_keys      =  array_keys( $_POST['term_meta'] );
                //$range_keys = array_diff($term_meta, $_POST['term_meta']);
                $new_term_meta   =  $_POST['term_meta'];
                $new_range_type  =  $new_term_meta['soya_range_type_meta'];
                $old_range_type  =  $term_meta['soya_range_type_meta'];
                $new_depth       =  $new_term_meta['soya_depth_meta'];
                $old_depth       =  $term_meta['soya_depth_meta'];
                
		foreach ( $range_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		
                //check to remove array elements that are not needed when non-class range is selected
                
                //set unwanted meta fields for post meta correction
                $diff = ($old_depth - $new_depth);
                $unwanted_term_meta_values = [];
               // $there_is_decrease = false;
                //for child terms
                if(($new_depth < $old_depth) && soya_is_term_child($term_id)){
                    $term_parent_id = soya_get_term_parent_id($term_id);
                    $parent_term_range_type = soya_get_term_custom_field($term_parent_id, 'soya_range_type_meta');
                    for($i = 1; $i<= $diff; $i++){
                        
                        $index_to_delete = $new_depth + $i;
                       
                        if($parent_term_range_type === 'class'){
                           // $upper = $term_meta['soya_upper_'.$index_to_delete.'_depth_meta'];
                           // $lower = $term_meta['soya_lower_'.$index_to_delete.'_depth_meta'];
                            $unwanted_term_meta_values[] = array('range_type' => 'class', 'index' => $index_to_delete);
                            unset($term_meta['soya_upper_'.$index_to_delete.'_depth_meta']);
                            unset($term_meta['soya_lower_'.$index_to_delete.'_depth_meta']);
                             
                        }else{
                            $unwanted_term_meta_values[] = array( 'index' =>$index_to_delete, 'range_type' => 'non-class');//.'-'.$term_meta['soya_lower_'.$index_to_delete.'_depth_meta'];
                            unset($term_meta['soya_lower_'.$index_to_delete.'_depth_meta']);
                        }
                        $unwanted_meta_term_fields[$term_id] = $unwanted_term_meta_values;
                    }
                    // assign unwanted meta term fields to a session variable so that they can deleted on features post type.
               // $_SESSION['unwanted_term_meta_fields'] = $unwanted_meta_term_fields; 
                do_action('delete_unwanted_range_values', $unwanted_meta_term_fields, 'child');
                //for parent terms
                }elseif (($new_depth < $old_depth) && !soya_is_term_child($term_id)) {
                    
                    $term = get_term($term_id, 'soya-range');
                    
                    $term_children = get_term_children($term_id, 'soya-range');
                    
                    $range_keys = [];
                    
                    for($i = 1; $i<= $diff; $i++){
                        
                        $index_to_delete = $new_depth + $i;
                      
                        $upper = ($old_range_type === 'class') ? $term_meta['soya_upper_'.$index_to_delete.'_depth_meta'] : '';
                        
                        $lower = $term_meta['soya_lower_'.$index_to_delete.'_depth_meta'];
                        
                        $range = empty($upper) ? $lower : $lower.'-'.$upper;
                        
                        $meta_key = empty($term_children) ? $term->slug.'_'.$range : soya_parent_meta_key($term->slug, $term_children, $range);
                        if(is_array($meta_key)){
                            $unwanted_term_meta_values  =  array_merge($unwanted_term_meta_values, $meta_key );
                        }else{
                        $unwanted_term_meta_values[] = $meta_key; //(is_array($meta_key)) ? array_merge($unwanted_term_meta_values, $meta_key ) : $unwanted_term_meta_values[] = $meta_key;
                        }
                        unset($term_meta['soya_lower_'.$index_to_delete.'_depth_meta']);
                            
                        if($old_range_type === 'class'){
                            
                            unset($term_meta['soya_upper_'.$index_to_delete.'_depth_meta']);
                        } 
                                             
                    }
                    $unwanted_meta_term_fields[$term_id] = $unwanted_term_meta_values;
               
                do_action('delete_unwanted_range_values', $unwanted_meta_term_fields, 'parent', $term_children);
        }  
        // check for change in class
        $unwanted_old_range_meta = [];
        if($old_range_type !== $new_range_type){
            $term = get_term($term_id, 'soya-range');
            $term_range_type = soya_get_term_custom_field($term_id, 'soya_range_type_meta');
            
            if(!soya_term_has_children($term_id) && !soya_is_term_child($term_id)){
                
                $unwanted_old_range_meta[$term_id] = soya_make_meta_keys($term_id);
            
            }elseif (soya_term_has_children($term_id)) {
                
                $unwanted_old_range_meta[$term_id] = soya_make_meta_keys($term_id);
               
            }
            do_action('delete_unwanted_range_values', $unwanted_old_range_meta, 'range_change');
          //  $_SESSION['unwanted_range_type'][$term_id] = $unwanted_old_range_meta;
            //Unset unwanted term meta fields
            $unwanted_meta_term_fields = [];
            
                 if(($old_range_type === 'class') && ($new_range_type ==='non-class')){
                     
                    for($i = 1; $i <= $term_meta['soya_depth_meta']; $i++){
                        
                        unset($term_meta['soya_upper_'.$i.'_depth_meta']);
                    }
                }
            
        }
        //check for changes in ranges 
        if( soya_term_has_children( $term_id ) ){
            // get ranges old ranges
            $old_ranges = soya_get_term_range($term_id, $old_depth);
            //get new meta ranges
            $new_ranges = [];//soya_get_term_range($term_id, $new_depth);
            for($j = 1; $j <= $new_depth; $j++){
                $lower_range = $new_term_meta['soya_lower_'.$j.'_depth_meta'];
                $upper_range = ($new_range_type === 'class') ? $new_term_meta['soya_upper_'.$j.'_depth_meta'] : '';
                $range = empty($upper_range) ? $lower_range : $lower_range.'-'.$upper_range;
                $new_ranges[] = $range;
            }
            // get unwanted ranges
            $unwanted_ranges = array_diff( $old_ranges, $new_ranges);
            //get meta keys of unwanted ranges
            $unwanted_meta_keys[$term_id] = soya_get_meta_keys_by_range($term_id, $unwanted_ranges);
            //do action to unwanted meta keys 
            //$_SESSION['unwanted_term_meta_fields'] = $unwanted_meta_keys;
            $term_children = get_term_children($term_id, 'soya-range');
            do_action('delete_unwanted_range_values', $unwanted_meta_keys, 'parent', $term_children);
            
        }     
        if(!soya_is_term_child($term_id) && !soya_term_has_children($term_id)){
            $term = get_term($term_id, 'soya-range');
            $old_ranges = soya_get_term_range($term_id, $old_depth);
            $new_ranges = [];
            for($j = 1; $j <= $new_depth; $j++){
                $lower_range = $new_term_meta['soya_lower_'.$j.'_depth_meta'];
                $upper_range = ($new_range_type === 'class') ? $new_term_meta['soya_upper_'.$j.'_depth_meta'] : '';
                $range = empty($upper_range) ? $lower_range : $lower_range.'-'.$upper_range;
                $new_ranges[] = $range;
            }
            $unwanted_ranges = array_diff($old_ranges, $new_ranges);
            
            $unwanted_meta_keys[$term_id] = soya_get_meta_keys_by_range($term_id, $unwanted_ranges);
            
            do_action('delete_unwanted_range_values', $unwanted_meta_keys, 'range_change');
        }
                // Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
    
}
add_action('edited_soya-range', 'soya_save_range_tax_fields', 10, 2);
add_action('create_soya-range', 'soya_save_range_tax_fields', 10, 2);

// add custom fields to print type to take the value of fixed cost associated with print-type.
function soya_add_fc_meta_field(){?>

    <?php do_action('soya_additional_tax_fields'); ?>
     <?php if(has_action('soya_add_various_fc')){
            do_action('soya_add_various_fc');
        }  else {?>
            
    <label for="Fixed cost"><?php echo _e('Fixed Cost', 'soya-customize-product'); ?></label>
    <input type="text" name="term_meta[soya_fc_meta]" class="form-field" value="" id="term_meta[soya_fc_meta]">

<?php }
}
add_action('soya-print-type_add_form_fields',  'soya_add_fc_meta_field', 10,2);

// save Fixed cost value
function soya_edit_fc_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>
    <tr>
        <th>
            <label for="term_meta[soya_fc_meta]"><?php _e( 'Fixed Cost', 'soya-customize-product' ); ?>
            
            </label>
        </th>
        <?php 
        if(has_action('soya_edit_various_fc')){
                 do_action('soya_edit_various_fc', $term_meta);
        }else{ ?>
         
       
        <td>
            <input type="text" name="term_meta[soya_fc_meta]" id="term_meta[soya_fc_meta]" value="<?php echo esc_attr( $term_meta['soya_fc_meta'] ) ? esc_attr( $term_meta['soya_fc_meta'] ) : ''; ?>">
            <p class="description"><?php _e( 'Enter a value for this field','soya-customize-product' ); ?></p>
        </td>
        <?php  
        do_action('soya_add_extra_fields',  $t_id);
        }?>
    </tr>                    
<?php
}
add_action( 'soya-print-type_edit_form_fields', 'soya_edit_fc_meta_field', 10, 2 );

//save fc meta values
function soya_save_fc_meta_value( $term_id ) {
  
	if ( isset( $_POST['term_meta'] ) ) {
           
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
}  
add_action( 'edited_soya-print-type', 'soya_save_fc_meta_value', 10, 2 );  
add_action( 'create_soya-print-type', 'soya_save_fc_meta_value', 10, 2 );

// add settings for Features post type

//add custom fields to Features
add_action( 'add_meta_boxes', 'soya_add_levels_box' );

function soya_add_levels_box() {
    
    //Get range terms that are associated with current post being edited
    $terms_unique_names = soya_get_terms_unique_name();
    $no_of_terms = count($terms_unique_names);
    
    
    $child_terms = [];
    
    $parent_terms = [];
    //keep record of all terms made meta keys so that meta keys can be deleted if 
    // if their terms are no longer needed
   
   
    
    if($no_of_terms > 0){
    //global $all_meta_terms;
    $all_meta_terms = [];
        foreach ( $terms_unique_names as $key => $value ) {
            $term_tax_id_length   = strlen($key);
            $term_tax_id          = substr($key, ($term_tax_id_length-2));
            $term_tax_id          = intval($term_tax_id);
            if(soya_is_term_child($term_tax_id)){
                
                $child_term_id    = $term_tax_id;
                $child_term_key   = $key;
                $all_meta_terms[] = $child_term_id;
                $child_term_label = $value;
                
                $the_parent       = soya_get_term_parent_id($child_term_id);
                
                $child_terms[] = array( 
                    'id'     => $child_term_id, 
                    'key'    => $child_term_key, 
                    'label'  => $child_term_label, 
                    'parent' => $the_parent
                    );
            }  else {
                $parent_term_label =    $value;
                $parent_term_id    =    $term_tax_id;
                $parent_term_key   =    $key;
                $all_meta_terms[]  =    $parent_term_id;
                $parent_terms[]    =    array( 'id' => $parent_term_id, 'key' => $parent_term_key, 'label' => $parent_term_label);
            }
        }
        //get the number of levels for the post  term child
        $depth = soya_get_term_custom_field($child_term_id, 'soya_depth_meta');
        // get child depth
       
        
    }
    
    //if a post term has a child then we need to have dimensional custom fields registered
    if(isset($child_terms) && !empty($child_terms)):
        
        foreach ($child_terms as $child_term){
             
    
        $depth_levels          =    soya_get_term_depth_levels_by_id($child_term['id'], $depth); 

        $prefix_child_term_id  =    ($child_term['id']) ? '-'.$child_term['id'] : '';
       
        /* 
         * get the parent object of the the child term
         */
        $parent_term_id     =   $child_term['parent'];
        $parent_term        =   get_term($child_term['parent'], 'soya-range');
        $parent_slug        =   $parent_term->slug;
        $parent_term_key    =   $parent_slug.'_'.$parent_term_id;
        $parent_term_label  =   $parent_term->name;
        
            foreach($depth_levels as $index => $labels){
                $index             =    $index + 1;
                $prefix_label      =    ($labels) ? ' <small> - '.$labels.'</small>' : '';
                $prefix_child_key  =    ($child_term['key']) ? '-'.$child_term['key'].$index : '';
                
                add_meta_box(
                    $parent_term_key.$prefix_child_key,            // Unique ID
                    $child_term['label'].$prefix_label,      // Box title
                    'soya_display_custom_box',  // Content callback
                    'soya_feature',
                    'normal',
                    '',
                    $child_term['parent'].$prefix_child_term_id.'-'.$index
                        
                );
            }
        }
    endif;
        
    if( isset($parent_terms) ):
            
        
    foreach ($parent_terms as $index => $parent_term){
        
        //check to exclude parent that have children from this.
        if(identify_parent_term_with_child($child_terms, $parent_term['id'])){

            add_meta_box(
                    $parent_term['key'],
                    $parent_term['label'],
                    'soya_display_custom_box',  // Content callback
                    'soya_feature',
                    'normal',
                    '',
                    $parent_term['id'].'-'.$index
                    );
        }
        
    }
    endif;
    if(empty($parent_terms) && empty($child_terms)){
        add_meta_box(
                'soya_meta_fields_counter',
                __('Please use Meta fieds to the left to add fields', 'soya-customize-product'),
                'soya_display_default_custom_box',  // Content callback
                'soya_feature',
                'normal',
                '',
                $all_meta_terms
                );
    }
}
function soya_display_custom_box($post, $cargs){
   // include plugin_dir_path( __FILE__ ).'/custom-fields.php';
    //call a nonce field for identification
    
    wp_nonce_field( 'soya_meta_box', 'soya_meta_box_nonce' );
    
    if(isset($cargs['args']))
        $current_term_id    =   '';
        if(preg_match('/-/', $cargs['args'])):
                    
            $parent_child_ids = explode ('-', $cargs['args']);
            if(count($parent_child_ids) === 3):
                $child_id         =   $parent_child_ids[1];
                $parent_id        =   $parent_child_ids[0];
                $index            =   $parent_child_ids[2];
                $index            =   $index.'-';
                $current_term_id  = $parent_id;
            elseif(count($parent_child_ids) === 2):
                                
                $parent_id        =  $parent_child_ids[0];
               // $index           =  $parent_child_ids[1];
                $current_term_id  =  $parent_id;
                    
                           
           endif;
        
        endif;
        
        /**

         * Get range term depth
         *    
         */
       
        echo '<table>';
       
        $current_range_term  =  soya_loop_term_depth( $current_term_id );
        
        $current_term_label  =  soya_get_term_custom_field( $current_term_id, 'soya_depth_name_meta' );
        
        $current_term_type   =  soya_get_term_custom_field(  $current_term_id, 'soya_range_type_meta' );
        
        $label_prefix        =  (  $current_term_label ) ?  $current_term_label  : __( 'Qty: ','quantity', 'soya-customize-product' );
        
        //Check and set meta unique keys for child terms
        
            
        if( !empty( $child_id  ) ){
            
            $child_term              =  get_term( $child_id, 'soya-range' );
            
            $child_term_slug         =  $child_term->slug;
            
            $child_term_slug         =  $child_term_slug.'-'; 
            
            $prefix_child_term_slug  =  '_'.$child_term->slug;
        }  else {
            $index = '';
        }  
            
        foreach ($current_range_term as $key => $label){
           
            ?>
    
    <tr>
        <th><?php printf(__('%s', 'soya-customize-product'), $label ); ?></th>
        <td><input type="text" value="<?php echo esc_html(get_post_meta($post->ID, $index.$child_term_slug.$key, true)); ?>" name="<?php echo $index.$child_term_slug.$key; ?>" min="-1"/></td>
       <?php echo $key; ?>
    </tr>
    <?php
           
     //add hook to extend form
             
     do_action('soya_add_extra_meta_fields'.$prefix_child_term_slug, $cargs['args'], $post->ID );
      
 }
 //global $soya_post_terms;
 

     ?>
    </table>
    <?php
     echo '<pre>';
    //var_dump($_SESSION['unwanted_term_meta_fields']);
   //var_dump(soya_get_term_depth_levels_by_id(57, 3));
    //var_dump(get_post_meta(2377));
     
     echo '</pre>';
     
     }


function soya_display_default_custom_box($post, $cargs){
    print_r($cargs);
    echo '<pre>';
       var_dump(get_post_meta(2377));
      // var_dump($_SESSION['post_terms']);
       echo '</pre>';
}


/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function soya_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['soya_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['soya_meta_box_nonce'], 'soya_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['soya_feature'] ) && 'page' == $_POST['soya_feature'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	} 

	/* OK, it's safe for us to save the data now. */
	
	// Make sure that it is set.
       
        // get post terms ids
        $post_terms_ids = soya_get_post_terms_ids($post_id);
        
        //set up all post terms ranges
        
        $parent_post_terms_ranges = [];
        $child_post_terms_ranges = [];
        if(isset($post_terms_ids)){
            foreach ($post_terms_ids as $post_term_id){
                
                if(soya_is_term_child($post_term_id)):
                    // We want only parents ranges and slugs.
                    $child_term_parent_id       =   esc_attr(soya_get_term_parent_id($post_term_id));
                    $post_term_range            =   soya_loop_term_depth($child_term_parent_id);
                    $post_child_term            =   get_term($post_term_id, 'soya-range');
                   // $post_term_depth = count($post_term_range);
                    $post_term_depth            =   soya_get_term_custom_field($post_term_id, 'soya_depth_meta');
                    $post_term_slug             =   $post_child_term->slug;
                    $post_term_range['depth']   =   $post_term_depth;
                    $post_term_range['slug']    =   $post_term_slug;
                    $child_post_terms_ranges[]  =   $post_term_range;
                else:
                    $post_term_range            =   soya_loop_term_depth($post_term_id); 
                    $parent_post_terms_ranges[] =   $post_term_range;
                endif;
            }
        }
                    
        //get keys from post terms ranges
        $all_meta_keys = [];
        $index = '';
        //Iterate for parent meta keys
        if(isset($parent_post_terms_ranges)):
            
        
            foreach ($parent_post_terms_ranges as $post_term_range){

                foreach ($post_term_range as $key => $label){
                    $all_meta_keys[] = $key;
                }
            }
        endif;
         // Build meta keys for child terms
        
        if(isset($child_post_terms_ranges)){
            //$index = '';
           // $slug = '';
            foreach ($child_post_terms_ranges as $index => $post_term_range){
                
                //get Depth and slug for each child term
                $indexi                =    $post_term_range['depth'];
                $post_child_term_slug  =    $post_term_range['slug'];
                // loop through each term's depth and get indices for meta keys
                
                for($i = 1; $i <= $indexi; $i++){
                    
                    //remove slug and depth from range arrange
                    unset($post_term_range['slug']);
                    unset($post_term_range['depth']);
                    foreach ($post_term_range as $key => $label){
                        
                       // if($key != 'slug'):
                            $all_meta_keys[] = $i.'-'.$post_child_term_slug.'-'.$key;

                       // check for added fixed meta fields and save them too
                        if(has_action('soya_add_extra_meta_fields_'.$post_child_term_slug)){
                            
                            $all_meta_keys[] = $i.'-'.'fixed-cost-'.$post_child_term_slug;
                        }
                    } 
                }
               
            }
                
        }
        // Apply filters
         $all_meta_keys = apply_filters('soya_filter_meta_keys', $all_meta_keys, $post_terms_ids);
            // Sanitize user input.
            foreach ($all_meta_keys as $key){
              
                //check for data
                if(isset($_POST[$key])) 
                    
                    $value = sanitize_text_field( $_POST[$key] );
                    
                    update_post_meta( $post_id, $key, $value );
            }
            //Check if a meta key is relevant and delete them
            $before_post_update_post_meta_keys  =   $_SESSION['post_terms'];
            
            $unwanted_meta_keys = array_diff($before_post_update_post_meta_keys, $all_meta_keys);
            
            foreach ($unwanted_meta_keys as $meta_key){
                
                delete_post_meta($post_id, $meta_key);
            }
             unset($_SESSION['post_terms']);

           
        
}
add_action( 'save_post', 'soya_save_meta_box_data' );