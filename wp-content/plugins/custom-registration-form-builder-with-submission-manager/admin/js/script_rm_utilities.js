
/*function disable_roles(el){
 if(jQuery(el).attr('checked')){
 jQuery("#rm_form_should_user_pick-0").attr('checked',false);
 jQuery("#rm_user_role").removeAttr('disabled');
 }else{
 jQuery("#rm_user_role").attr('disabled','disabled');
 }

 }*/

/*function disable_auto_assign(el){
 if(jQuery(el).attr('checked')){
 jQuery("#rm_user_create-0").attr('checked',false);
 jQuery("#rm_role_label").removeAttr('disabled');
 jQuery(".rm_allowed_roles").removeAttr('disabled');
 jQuery("#rm_user_role").attr('disabled','disabled');
 }else{
 jQuery("#rm_role_label").attr('disabled','disabled');
 jQuery(".rm_allowed_roles").attr('disabled','disabled');
 }

 }*/

function delete_role(el,role){
    jQuery("#"+role).attr('checked','checked');
    jQuery.rm_do_action('rm_user_role_mananger_form','rm_user_role_delete');
}


/*
 Utitlity function to show hide elements on checkbox onchange events.
 */

function checkbox_toggle_elements(el,elIds,mode){
    var ids= elIds.split(',');
    if(mode === undefined)
        mode= 0;
    if(el.checked){
        for(i=0;i<ids.length;i++){
            if(mode==1)
                jQuery("#"+ids[i]).hide(800);
            else
                jQuery("#"+ids[i]).show(800);
        }
    }else{
        for(i=0;i<ids.length;i++){
            if(mode==1)
                jQuery("#"+ ids[i]).show(800);
            else
                jQuery("#"+ ids[i]).hide(800);
        }
    }
}

function checkbox_disable_elements(el,elIds,mode){
    var ids= elIds.split(',');
    var is_checked = jQuery(el).is(':checked');
    if(mode === 0){
        is_checked = !is_checked;
    }
    jQuery.map(ids, function(id){
        if(is_checked){
            jQuery('#' + id).attr('disabled', true);
        }else{
            jQuery('#' + id).attr('disabled', false);
        }
    });
}

function rm_append_field(tag, element) {
    jQuery(element).parents('#rm_action_container_id').prev().append("<" + tag + " class='appendable_options rm-deletable-options'>" + jQuery(element).parents('#rm_action_container_id').prev().children(tag + ".appendable_options").html() + "</" + tag + ">").children().children("input").last().val('');
}

function rm_load_page(element, page, get_var){
    get_var = typeof get_var !== 'undefined' ? get_var : 'form_id';
    window.location = '?page=rm_' + page + '&rm_' + get_var + '=' + jQuery(element).val();
}

function rm_load_page_multiple_vars(element, page, get_var, get_var_json){
    get_vars = get_var_json;

    var loc = '?page=rm_' + page;

    for (var key in get_vars) {
        if (get_vars.hasOwnProperty(key)) {
            loc += '&rm_'+key+'='+get_vars[key];
            //alert(key + " -> " + get_vars[key]);
        }
    }

    loc += '&rm_' + get_var + '=' + jQuery(element).val();

    //alert(loc);
    window.location = loc;
}

function rm_toggle_field_add_form_fields(element){
    var field_type = jQuery(element).val();

    var field_type_help_text = rm_get_help_text(field_type);
    jQuery('#rm_field_type_select_dropdown').parent().next('.rmnote').html(field_type_help_text);

    if (field_type)
        jQuery.field_add_form_manage(field_type);
}

function rm_get_help_text(ftype){

    switch(ftype)
    {
        case 'Textbox':return 'Simple single line text field.';
        case 'HTMLP':return 'A read only paragraph field useful for displaying multiple lines of text inside form between input fields.';
        case 'HTMLH':return 'Large size read only text useful for creating custom headings.';
        case 'Select':return 'Allows user to choose a value from multiple predefined options displayed as drop down list.';
        case 'Radio':return 'Allows user to choose a value from multiple predefined options displayed as radio boxes.';
        case 'Textarea':return 'This allows user to input multiple lines of text as value.';
        case 'Checkbox':return 'Allows user to choose more than one value from multiple predefined options displayed as drop down list.';
        case 'jQueryUIDate':return 'Allows users to pick a date from graphical calendar or enter manually.';
        case 'Email':return 'Email  An additional email field. Please note, primary email field always appears in the form and cannot be removed.';
        case 'Number':return 'Allows user to input value in numbers.';
        case 'Country':return 'A drop down list of all countries appears to the user for selection.';
        case 'Timezone':return 'A drop down list of all time-zones appears to the user for selection.';
        case 'Terms':return 'Useful for adding terms and conditions to the form. User must select the check box to continue with submission if you select “Is Required” below.';
        case 'File':return 'Display a field to the user for attaching files from his/ her computer to the form.';
        case 'Price':return 'Adds payment to the form. Payment fields are separately defined in “Price Fields” section of RegistrationMagic. This field type allows you to insert one of the fields defined there.';
        case 'Repeatable':return 'Allows user to add extra text field boxes to the form for submitting different values. Useful where a field requires multiple user input  values. ';
        case 'Fname':return 'This field is connected directly to WordPress’ User area First Name field. ';
        case 'Lname':return 'This field is connected directly to WordPress’ User area Last Name field. ';
        case 'BInfo':return 'This field is connected directly to WordPress’ User area Bio field. It allows inserting multiple lines of text. ';
        default: return 'Select  or change type of the field if not already selected.';
    }
}

function rm_get_help_text_price_field(ftype){

    switch(ftype)
    {
        case 'fixed':return 'For setting fixed price payment with the form';
        case 'multisel':return 'Allow user to pick multiple items with individual prices. Price will calculated as cumulative for the selection for payment.';
        case 'dropdown':return 'Allows user to pick a single item from multiple items with individual prices.';
        case 'userdef':return 'Allows user to enter his/ her own price for payment with the form. Useful for accepting donations etc.';
        default: return 'Select  or change type of the price field if not already selected.';
    }
}

function rm_sort_forms(element,req_page){
    var val = jQuery(element).val();
    if(val === 'form_name')
        window.location = '?page=rm_form_manage&rm_sortby=' + val + '&rm_descending=false&rm_reqpage='+req_page;
    else
        window.location = '?page=rm_form_manage&rm_sortby=' + val + '&rm_reqpage='+req_page;


}

function rm_toggle_visiblity(element) {
    console.log(jQuery(element).val());
}

function rm_toggle_visiblity_pricing_fields(element) {
    field_type = jQuery(element).val();
    var field_type_help_text = rm_get_help_text_price_field(field_type);
    jQuery('#id_paypal_field_type_dd').parent().next('.rmnote').html(field_type_help_text);

    jQuery.setup_pricing_fields_visibility(field_type);


}

function rm_toggle_visiblity_layouts(element) {
    jQuery.setup_layouts_visibility(jQuery(element).val());
}

function rm_delete_appended_field(element, element_id) {
    if (jQuery(element).parents("#".element_id).children(".appendable_options").length > 1)
        jQuery(element).parent(".appendable_options").remove();
}

function rm_setup_google_charts(){

    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart', 'bar']});

    // Set a callback to run when the Google Visualization API is loaded.

    //Since callback functions are defined in the templates, always
    //check for the existance of function beforehand so that javascript does not fail for other pages.
    if (typeof drawConversionChart == 'function')
        google.charts.setOnLoadCallback(drawConversionChart);

    if (typeof drawBrowserUsageChart == 'function')
        google.charts.setOnLoadCallback(drawBrowserUsageChart);

    if (typeof drawConversionByBrowserChart == 'function')
        google.charts.setOnLoadCallback(drawConversionByBrowserChart);

    if (typeof drawMultipleFieldCharts == 'function')
        google.charts.setOnLoadCallback(drawMultipleFieldCharts);
}

