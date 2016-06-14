(function (RM_jQ) {
    'use strict';

    /*
     * This function is fired on ready event
     * Activates when document is loaded completely
     *
     * @returns {undefined}
     */



    RM_jQ(function () {

        var chart_obj = RM_jQ(".rm-box-graph");

        rm_setup_google_charts();




        //To implement sorting operation using drag and drop
        //Just have to put id 'sortable' on the element you want to scroll.
        //jQuery UI sortable is used



        var checked_el_ids = [];

        RM_jQ('.rm_sortable_elements').sortable({
            axis: 'y',
            opacity: 0.7,
            handle: '.rm_sortable_handle'
        });

        RM_jQ('#rm_sortable_form_fields').sortable({
            axis: 'y',
            opacity: 0.7,
            handle: '.rm_sortable_handle',
            update: function (event, ui) {
                var list_sortable = RM_jQ(this).sortable('toArray');

                var data = {
                    action: 'rm_sort_form_fields',
                    'rm_slug': 'rm_field_set_order',
                    data: list_sortable
                };

                RM_jQ.post(ajaxurl, data, function (response) {
                    void(0);
                });
            }
        });



        //tabbing operation
        RM_jQ('.rm_tabbing_container').tabs();



        //hide fields on add_field form
        var field_type = RM_jQ('#rm_field_type_select_dropdown').val();

        if (field_type)
            RM_jQ.field_add_form_manage(field_type);

        //Set appropriate help text.
        var field_type_help_text = rm_get_help_text(field_type);
        RM_jQ('#rm_field_type_select_dropdown').parent().next('.rmnote').html(field_type_help_text);

        RM_jQ('.rm_toggle_deactivate').click(function (e) {
            if (!RM_jQ('.rm_checkbox').is(':checked')) {
                e.preventDefault();
            }
        });

        field_type = RM_jQ('#id_paypal_field_type_dd').val();

        if (field_type)
            RM_jQ.setup_pricing_fields_visibility(field_type);

        var field_type_help_text = rm_get_help_text_price_field(field_type);
        jQuery('#id_paypal_field_type_dd').parent().next('.rmnote').html(field_type_help_text);

        var theme = RM_jQ('#theme_dropdown').val();

        if (theme)
            RM_jQ.setup_layouts_visibility(theme)

        RM_jQ('.rm_checkbox').click(function () {
            checked_el_ids.push(RM_jQ(this).parent('.card').attr('id'));
            RM_jQ('.rm_actions').attr('disabled', false);
        });




        RM_jQ('#rm_form_manager_operartionbar').submit(function () {
            var i = [];
            RM_jQ.map(RM_jQ("input[name='rm_selected_forms[]']"), function (value, index) {
                if (RM_jQ(value).is(":checked")) {
                    i.push(RM_jQ(value).val());
                }
            });
            RM_jQ("input[name='rm_selected']").val(JSON.stringify(i));
        });

        RM_jQ(document).ajaxStart(function () {
            RM_jQ("#rm_f_loading").show();
        });
        RM_jQ(document).ajaxComplete(function () {
            RM_jQ("#rm_f_loading").hide();
        });



    });






    /**
     * function to delete a forms field
     *
     * @var  field_id   id of the field to delete
     */
    RM_jQ.delete_form_field = function (field_id) {

        var data = {
            action: 'rm_delete_form_field',
            data: field_id
        };

        RM_jQ.post(ajaxurl, data, function (response) {
            console.log(response);
        });
    };

    RM_jQ(document).ready(function () {



        RM_jQ(".rm_checkbox_group").change(function () {
            if (RM_jQ(this).attr('checked')) {
                RM_jQ(".rm_action_bar .rm_action_btn").attr('disabled', false);
            }

        });

        RM_jQ("#rm_editor_add_form").change(function () {
            tinymce.execCommand('mceFocus', false, 'content');
            if (RM_jQ(this).val() != 0) {
                if (RM_jQ(this).val() === '__0')
                    var shortcode = "[RM_Login]";
                else
                    var shortcode = "[RM_Form id='" + RM_jQ(this).val() + "']";

                if (typeof send_to_editor == 'function')
                    send_to_editor(shortcode);
                else
                    tinyMCE.get('content').execCommand('mceInsertContent', false, shortcode);

            }

        });


        RM_jQ("#rm_editor_add_email").change(function () {
            //tinymce.execCommand('mceFocus',false,'form_email_content');
            if (RM_jQ(this).val() != 0) {
                var shortcode = "{{" + RM_jQ(this).val() + "}}";

                if (typeof send_to_editor == 'function')
                    send_to_editor(shortcode);
                else
                    tinyMCE.get('form_email_content').execCommand('mceInsertContent', false, shortcode);

            }

        });

        RM_jQ("#mce_rm_mail_body").change(function () {
            tinymce.execCommand('mceFocus', false, 'rm_mail_body');
            if (RM_jQ(this).val() != 0) {
                var shortcode = "{{" + RM_jQ(this).val() + "}}";

                if (typeof send_to_editor == 'function')
                    send_to_editor(shortcode);
                else
                    tinyMCE.get('rm_mail_body').execCommand('mceInsertContent', false, shortcode);


            }

        });




    });


    RM_jQ.prevent_quick_add_form = function (event) {
        var f_name = RM_jQ('#rm_form_name').val().toString().trim();
        if (f_name === "" || !f_name) {

            RM_jQ('#rm_form_name').fadeIn(100).fadeOut(1000, function () {
                RM_jQ('#rm_form_name').css("border", "");
                RM_jQ('#rm_form_name').fadeIn(10);
                RM_jQ('#rm_form_name').val('');
            });
            RM_jQ('#rm_form_name').css("border", "1px solid #FF6C6C");
            event.preventDefault();
        }
    };

    RM_jQ.prevent_field_add = function (event, rm_msg) {
        RM_jQ('.rm_prevent_empty').each(function(){
        var f_name = RM_jQ(this).val().toString().trim();
        if (f_name === "" || !f_name) {

            RM_jQ('.rm_prevent_empty').fadeIn(100).fadeOut(1000, function () {
                RM_jQ('.rm_prevent_empty').css("border", "");
                RM_jQ('.rm_prevent_empty').fadeIn(10);
                RM_jQ('.rm_prevent_empty').val('');
            });
            RM_jQ(this).css("border", "1px solid #FF6C6C");
            RM_jQ('#rm_jqnotice_text').html(rm_msg);
            RM_jQ('#rm_jqnotice_row').show();
            event.preventDefault();
        } else
            RM_jQ('#rm_jqnotice_text').html('');
        
        });

    };


    //Email listing
//    RM_jQ.remove_email = function (elem){
//        var id = RM_jQ(elem).attr('id');
//        var mailbox_id = id.substr(2);
//        RM_jQ(elem).closest("#"+mailbox_id).remove();
//    };
//
//    RM_jQ.add_email_field = function aef (initialcounter){
//        aef.counter = ++aef.counter || initialcounter;
//        var newemail = RM_jQ('#id_rm_add_email_tb').val();
//        var t = "<div id='id_test_"+aef.counter+"'><input class='rm_options_resp_email' type='email' name='resp_emails[]' value='"+newemail+"' readonly='true'></input><div class='x_remove_resp_email' id='x_id_test_"+aef.counter+"' onclick='jQuery.remove_email(this)'>X</div></div>";
//        var x = RM_jQ(document.createElement("div")).attr("id","xxxxxxx");
//        x.after().html(t);
//        x.appendTo('#id_rm_admin_emails_container');
//        RM_jQ('#id_rm_add_email_tb').val("");
//    };


    /**
     * function to hide field_add form fields according to field type
     *
     * @var  field_type   type of the field to be added
     */
    RM_jQ.field_add_form_manage = function (field_type) {

        var all_elem = RM_jQ(".rm_static_field");
        RM_jQ(".rm_sub_heading").show();
        RM_jQ(".rm_check").hide();
        all_elem.attr('disabled', false);
        all_elem.parents(".rmrow").show();
        all_elem.removeClass("rm_prevent_empty");
        RM_jQ("#rm_field_value_paragraph, #rm_field_value_options_textarea, #rm_field_value_heading, #rm_field_value_options_sortable, #rm_field_value_file_types, #rm_field_value_pricing").attr('required', false);
        RM_jQ("#rm_jqnotice_row").hide();

        switch (field_type) {
            case 'Textbox' :
            case 'Fname' :
            case 'Lname' :
                var object = RM_jQ(".rm_field_value, .rm_textarea_type, .rm_options_type_fields, #rm_field_is_read_only-0");
                break;

            case 'HTMLP' :
                var object = RM_jQ(".rm_input_type, .rm_field_value, #rm_field_show_on-0").not("#rm_field_value_paragraph");
                var val_field = RM_jQ("#rm_field_value_paragraph");
                RM_jQ(".rm_sub_heading").hide();
                break;

            case 'HTMLH' :
                var object = RM_jQ(".rm_input_type, .rm_field_value, #rm_field_show_on-0").not("#rm_field_value_heading");
                var val_field = RM_jQ("#rm_field_value_heading");
                RM_jQ(".rm_sub_heading").hide();
                break;

            case 'Select' :
                var object = RM_jQ(".rm_text_type_field, .rm_field_value, .rm_textarea_type, #rm_field_default_value_sortable").not("#rm_field_value_options_textarea");
                var val_field = RM_jQ("#rm_field_value_options_textarea");
                break;

            case 'Radio' :
                var object = RM_jQ(".rm_text_type_field, .rm_field_value, .rm_textarea_type, #rm_field_default_value_sortable").not("#rm_field_value_options_sortable");
                var val_field = RM_jQ("#rm_field_value_options_sortable");
                break;

            case 'Textarea' :
            case 'BInfo' :
                var object = RM_jQ(".rm_field_value, .rm_options_type_fields, #rm_field_is_read_only-0");
                break;

            case 'Checkbox' :
                var object = RM_jQ(".rm_text_type_field, .rm_field_value, .rm_textarea_type, #rm_field_default_value").not("#rm_field_value_options_sortable");
                var val_field = RM_jQ("#rm_field_value_options_sortable");
                RM_jQ(".rm_check").show();

                break;

            case 'jQueryUIDate' :
            case 'Email' :
            case 'Number' :
            case 'Country' :
            case 'Timezone' :
                var object = RM_jQ(".rm_static_field").not(".rm_required, #rm_field_is_required-0");
                break;

            case 'Repeatable' :
                var object = RM_jQ(".rm_field_value, .rm_textarea_type, .rm_options_type_fields, #rm_field_placeholder, #rm_field_is_read_only-0");
                break;

            case 'Terms' :
                var object = RM_jQ(".rm_static_field").not(".rm_required, #rm_field_is_required-0, #rm_field_value_terms");
                var val_field = RM_jQ("#rm_field_value_terms");
                break;

            case 'File' :
                var object = RM_jQ(".rm_static_field, #rm_field_default_value").not(".rm_required, #rm_field_is_required-0, #rm_field_value_file_types");
                //var val_field = RM_jQ("#rm_field_value_file_types");
                break;

            case 'Price' :
                var object = RM_jQ(".rm_static_field").not(".rm_required, #rm_field_is_required-0, #rm_field_value_pricing");
                var val_field = RM_jQ("#rm_field_value_pricing");
                break;

//            case 'jQueryUIDate' :
//                var object = RM_jQ(".rm_static_field").not(".rm_required, #rm_field_is_required-0");
//                break;

            default :
                var object = RM_jQ(".rm_static_field").not("#rm_field_type_select_dropdown");


        }

        object.parents(".rmrow").hide();
        object.attr('disabled', true);

        if (field_type === 'HTMLP' || field_type === 'HTMLH' /*|| field_type === 'File'*/ || field_type === 'Price' || field_type === 'Checkbox' || field_type === 'Radio' || field_type === 'Select') {
            val_field.attr('required', true);
            val_field.addClass("rm_prevent_empty");
        }

        if (field_type === 'Fname' || field_type === 'Lname' || field_type === 'BInfo') {
            RM_jQ("#rm_field_show_on-0").attr('checked', false);
            RM_jQ("#rm_field_show_on-0").attr('readonly', true);
            RM_jQ("#rm_field_show_on-0").parents(".rmrow").hide();
        }

        var rm_other_box = RM_jQ("#rmaddotheroptiontextdiv");
        if (field_type === 'Checkbox') {
            rm_other_box.show();
            rm_other_box.siblings('#rm_action_field_container').addClass('rm_shrink_div');
        } else {
            rm_other_box.hide();
            rm_other_box.siblings('#rm_action_field_container').removeClass('rm_shrink_div');
        }

    };





    RM_jQ.setup_pricing_fields_visibility = function (field_type) {

        var all_elem = RM_jQ(".rm_static_field");
        all_elem.removeClass("rm_prevent_empty");
        
        switch (field_type) {

            case 'fixed':
                RM_jQ('#id_block_fields_for_dd_multisel').find('input').prop('required', false);
                RM_jQ('#id_paypal_field_value_no').prop('required', true);
                RM_jQ('#id_paypal_field_value_no').addClass('rm_prevent_empty');
                RM_jQ('#id_block_fields_for_dd_multisel').hide();
                RM_jQ('#id_block_fields_for_fixed').show();
                break;

            case 'multisel':
            case 'dropdown':
                RM_jQ('#id_block_fields_for_dd_multisel').find('input').prop('required', true);
                RM_jQ('#id_block_fields_for_dd_multisel').find('input').addClass("rm_prevent_empty");
                RM_jQ('#rm_append_option').removeClass("rm_prevent_empty"); //Remove class from "click to append" box
                RM_jQ('#id_paypal_field_value_no').prop('required', false);
                RM_jQ('#id_block_fields_for_dd_multisel').show();
                RM_jQ('#id_block_fields_for_fixed').hide();
                break;

            case 'userdef':
                RM_jQ('#id_block_fields_for_dd_multisel').find('input').prop('required', false);
                RM_jQ('#id_paypal_field_value_no').prop('required', false);
                RM_jQ('#id_block_fields_for_dd_multisel').hide();
                RM_jQ('#id_block_fields_for_fixed').hide();
                break;
        }

    };


    RM_jQ.setup_layouts_visibility = function (theme) {

        switch (theme) {

            case 'matchmytheme':
                RM_jQ('#layout_two_columns_container').hide();
                break;

            case 'classic':
                RM_jQ('#layout_two_columns_container').show();
                break;
        }

    };


    /**
     * Function to define some form actions by setting 'rm_slug'
     *
     * @param {string} form_id   id attribute of the form to be submitted.
     * @param {string} slug      value of rm_slug to be set
     */

    RM_jQ.rm_do_action = function (form_id, slug) {

        var form = RM_jQ("form#" + form_id);

        form.children('input#rm_slug_input_field').val(slug);

        form.submit();

    };



    RM_jQ.rm_append_textbox_other = function (elem) {
        RM_jQ("#rmaddotheroptiontextboxdiv").show();
        RM_jQ("#rm_field_is_other_option").val(1);
    };

    RM_jQ.rm_delete_textbox_other = function (elem) {
        RM_jQ("#rmaddotheroptiontextboxdiv").hide();
        RM_jQ("#rm_field_is_other_option").val('');
    };

    /**
     * Function to define some form actions by setting 'rm_slug'.
     * But also provide an JS confirmation before proceeding.
     *
     * @param {string} alert   message to show as alert
     * @param {string} form_id   id attribute of the form to be submitted.
     * @param {string} slug      value of rm_slug to be set
     */

    RM_jQ.rm_do_action_with_alert = function (alert, form_id, slug) {

        if (confirm(alert)) {

            var form = RM_jQ("form#" + form_id);

            form.children('input#rm_slug_input_field').val(slug);

            form.submit();
        }

    };

    RM_jQ.rm_invertcolor = function (element) {
        var a = element.css("background-color");
        var b = element.css("color");

        element.css('color', a);
        element.css('background-color', b);
    };

    RM_jQ.rm_test_smtp_config = function () {

        var data = {
            'action': 'rm_test_smtp_config',
            'test_email': RM_jQ("#id_rm_test_email_tb").val(),
            'smtp_host': RM_jQ("#id_rm_smtp_host_tb").val(),
            'SMTPAuth': RM_jQ("#id_rm_smtp_auth_cb-0").val(),
            'Port': RM_jQ("#id_rm_smtp_port_num").val(),
            'Username': RM_jQ("#id_rm_smtp_username_tb").val(),
            'Password': RM_jQ("#id_rm_smtp_password_tb").val(),
            'SMTPSecure': RM_jQ("#id_rm_smtp_enctype_dd").val(),
            'From': RM_jQ("#id_rm_from_email_tb").val(),
            'FromName': RM_jQ("#id_rm_from_tb").val()
        };

        RM_jQ.post(ajaxurl, data, function (response) {
            RM_jQ("#rm_smtp_test_response").html(response);
            RM_jQ("#rm_smtp_test_response").removeClass();
            RM_jQ("#rm_smtp_test_response").addClass('rm_response rm_' + response.toLowerCase());
        });
    };

})(jQuery);





