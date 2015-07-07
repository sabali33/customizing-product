<?php

/* 
 * This contains custom fields for Features post type.
 */
  $no_of_levels = get_option('no_of_levels_to_price');
  wp_nonce_field( 'soya_meta_box', 'soya_meta_box_nonce' );
  //var_dump($post);
  ?>
    
<h2><b><?php echo $value; ?></b></h2>
         
     <table>
        
           <th><?php echo _e('Levels','class','soya-customize-product');?></th>
          
            <th><?php echo _e('Cost Price','soya-customize-product');?></th>
    
            <tr>
                <td><?php printf(__("%s",'soya-customize-product'), $value); ?></td>
                <td><input type="text" name="<?php echo $key ?>" value="<?php echo get_post_meta($post->ID, $key , true); ?>" id="<?php echo $key; ?>"/></td>
                
            </tr>
          <?php
 
     echo '</table>';
     

