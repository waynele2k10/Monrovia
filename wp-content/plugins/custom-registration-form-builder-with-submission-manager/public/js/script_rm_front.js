/**
 * FILE for all the javascript functionality for the front end of the plugin
 */
/* For front end OTP widget */

var rm_append_other_option = function(element){
    if(jQuery(element).children("input[type=checkbox]").is(":checked")){        
        var obj_op = jQuery(element).siblings("#rm_appendable_textbox");
        obj_op.slideDown();
        obj_op.children("input[type=text]").attr('disabled',false);
    }
    else{
        var obj_op = jQuery(element).siblings("#rm_appendable_textbox");
        obj_op.slideUp();
        obj_op.children("input[type=text]").attr('disabled',true);
    }

};

var rm_call_otp = function (event) {
    
    if (event.keyCode == 13) {
        
        var otp_key_status= jQuery("#rm_otp_login #rm_otp_kcontact").is(":visible");
        var data = {
            'action': 'rm_set_otp',
            'rm_otp_email': jQuery("#rm_otp_econtact").val(),
            'rm_slug' : 'rm_front_set_otp'
        };
        if(otp_key_status)
        {
            data.rm_otp_key = jQuery("#rm_otp_login #rm_otp_kcontact").val();
        }
        
        jQuery.post(ajax_url, data, function (response) {
            var responseObj = jQuery.parseJSON(response);
            if(responseObj.error==true){
                jQuery("#rm_otp_login .rm_f_notifications .rm_f_error").hide().html(responseObj.msg).slideDown('slow');
                jQuery("#rm_otp_login .rm_f_notifications .rm_f_success").hide();
                //jQuery("#rm_otp_login " + responseObj.hide).hide('slow');
            }else{
                jQuery("#rm_otp_login .rm_f_notifications .rm_f_error").hide();
                jQuery("#rm_otp_login .rm_f_notifications .rm_f_success").hide().html(responseObj.msg).slideDown('slow');
                jQuery("#rm_otp_login " + responseObj.show).show('slow');
                
                if(responseObj.reload){
                    location.reload();
                }
            }
        });
    }
};

/*All the functions to be hooked on the front end at document ready*/
jQuery(document).ready(function(){
    jQuery('.rm_tabbing_container').tabs();
    jQuery("#rm_f_mail_notification").show('fast',function(){
        jQuery("#rm_f_mail_notification").fadeOut(3000);
    });
    jQuery(document).ajaxStart(function(){
            jQuery("#rm_f_loading").show();
            });
    jQuery(document).ajaxComplete(function(){
            jQuery("#rm_f_loading").hide();
            }); 
});

/*launches all the functions assigned to an element on click event*/
        
function performClick(elemId,s_id,f_id) {
   var elem = document.getElementById(elemId);
   if(elem && document.createEvent) {
      var evt = document.createEvent("MouseEvents");
      evt.initEvent("click", true, false);
      elem.dispatchEvent(evt);
    }
}


function rm_append_field(tag, element_id) {
       jQuery('#'+element_id).append("<" + tag + " class='appendable_options'>" + jQuery('#'+element_id).children(tag + ".appendable_options").html() + "</" + tag + ">");
}

function rm_delete_appended_field(element, element_id) {
    if (jQuery(element).parents("#".element_id).children(".appendable_options").length > 1)
        jQuery(element).parent(".appendable_options").remove();
}
//(function( $ ) {
//	'use strict';
//
//	/**
//	 * All of the code for your public-facing JavaScript source
//	 * should reside in this file.
//	 *
//	 * Note: It has been assumed you will write jQuery code here, so the
//	 * $ function reference has been prepared for usage within the scope
//	 * of this function.
//	 *
//	 * This enables you to define handlers, for when the DOM is ready:
//	 *
//	 * $(function() {
//	 *
//	 * });
//	 *
//	 * When the window is loaded:
//	 *
//	 * $( window ).load(function() {
//	 *
//	 * });
//	 *
//	 * ...and/or other possibilities.
//	 *
//	 * Ideally, it is not considered best practise to attach more than a
//	 * single DOM-ready or window-load handler for a particular page.
//	 * Although scripts in the WordPress core, Plugins and Themes may be
//	 * practising this, we should strive to set a better example in our own work.
//	 */
//
//})( jQuery );
