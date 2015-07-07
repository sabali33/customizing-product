/* 
 * Main js file for soya plugin

 */
jQuery(document).ready(function($){
 
    ////
    $('.soya-depth').blur(function(){
      
        var depth = $(this).val();
        var rangeType = $('.soya-range-type').val();
        var data = {
                action: 'soya_range',
                nonce: soya.nonce,
                depth:  depth,
                range_type: rangeType                
            };
            $.post(soya.ajax_url,   data,   function(response){
                $('#soya-add-depth-fields').html(response);
            });
    });
//alert('yeah');
    
    $('.soya-depth-type').change(function(){
        var type = $(this).val();
        var depth = $('.soya-depth').val();
        var data = {
                action: 'soya_update_edit',
                nonce:  soya.nonce,
                depth:  depth,
                type:   type                
            };
            $.post(soya.ajax_url,   data,   function(response){
                $('.depth-container').html(response);
            });
    });
    
   
});


