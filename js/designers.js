function remove_social_network_option(obj)
{
	$(obj.parentNode).remove();
}

var social_network_urls = Array();
social_network_urls['Facebook'] = 'http://www.facebook.com/';
social_network_urls['Twitter'] = 'http://twitter.com/';
social_network_urls['YouTube'] = 'http://www.youtube.com/';
social_network_urls['LinkedIn'] = 'http://www.linkedin.com/';
social_network_urls['Pinterest'] = 'http://www.pinterest.com/';
social_network_urls['Houzz'] = 'http://www.houzz.com/';

function change_social_network_type(obj)
{
	social_network_url = social_network_urls[obj.options[obj.selectedIndex].value];

	var children = $(obj.parentNode).childNodes;
	for(i=0; i<children.length; i++)
	{
		if ( (children[i].tagName == 'LABEL') && (children[i].className == 'prepend_text') )
			children[i].innerHTML  = social_network_url;

		if ( children[i].tagName == 'INPUT' )
			change_social_network(children[i]);
	}
}

function change_social_network(obj)
{
	var social_network_type = '';
	var children = $(obj.parentNode).childNodes;
	for(i=0; i<children.length; i++)
	{
		if ( children[i].tagName == 'SELECT' )
			social_network_type = children[i].options[children[i].selectedIndex].value;
	}

	obj.value = obj.value.replace(social_network_urls[social_network_type], "");
}

function clearFileInputField(tagId) {	
    document.getElementById(tagId).innerHTML = document.getElementById(tagId).innerHTML;
    //console.log('removed');
}
function writeNewFileLabel(id, value){	
	var splitArray = value.split('\\');
	var indexArray = splitArray.length -1;
	$(id).value = splitArray[indexArray];	
}



Event.observe(window,'load',function(){

	//update file names as chosen


	// CUSTOM FIELD VALIDATION
	if(get_field('user_name')){
		get_field('user_name').custom_validation = function(){
			if(!is_alphanumeric(this.value)){
				this.error_message = 'User names can contain<br />only letters and numbers.';
				return false;
			}
			if(this.value.length<4){
				this.error_message = 'User names must be at least<br />four characters long.';
				return false;
			}
			// GARDEN GATEWAY FORMAT
			if(this.value.match(/^[x][\d]+$/)){
				this.error_message = 'User names cannot be in this format: x1234.';
				return false;
			}
			return true;
		}
	}
	if(get_field('password')){
		get_field('password').custom_validation = function(){
			if(this.value.contains(' ')){
				this.error_message = 'Passwords cannot contain spaces.';
				return false;
			}
			if(this.value.length<6){
				this.error_message = 'Your password must be at least<br />6 characters in length.';
				return false;
			}
			return true;
		}
	}
	if(get_field('password_confirm')){
		get_field('password_confirm').custom_validation = function(){
			if(this.value!=get_field('password').value){
				this.error_message = 'The passwords specified do not match.';
				return false;
			}
			return true;
		}
	}

	if(get_field('new_password')){
		get_field('new_password').custom_validation = function(){
			if(this.value.contains(' ')){
				this.error_message = 'Passwords cannot contain spaces.';
				return false;
			}
			if(this.value&&this.value.length<6){
				this.error_message = 'Your new password must be at<br />least 6 characters in length.';
				return false;
			}
			return true;
		}
	}
	if(get_field('new_password_reenter')){
		get_field('new_password_reenter').custom_validation = function(){
			if(this.value!=get_field('new_password').value){
				this.error_message = 'The passwords specified do not match.';
				return false;
			}
			return true;
		}
	}
	var form = $('create_profile_form')||$('edit_profile_form');
	if(form){
		form.custom_validation = function(){			
			var ret = true;
			if ( $$('[name="new_password_reenter"]').length ){
				var errorMsgPass = document.getElementById('errors-pass');
				if($$('[name="new_password"]')[0].value !== $$('[name="new_password_reenter"]')[0].value){
					$$('[name="new_password_reenter"]')[0].addClassName('error_validation');				
					errorMsgPass.innerHTML = 'Your password does not match.';
					errorMsgPass.style.display = "block";
					ret = false;
				}else{
					$$('[name="new_password_reenter"]')[0].removeClassName('error_validation');
					errorMsgPass.style.display = "none";
				}
			}
			if(!$$('[name="expertise[]"]:checked').length){
				$$('.expertise_checkboxes')[0].addClassName('error_validation');
				ret = false;
			}else{
				$$('.expertise_checkboxes')[0].removeClassName('error_validation');
			}
			if(!$$('[name="services[]"]:checked').length){
				$$('.services_checkboxes')[0].addClassName('error_validation');
				ret = false;
			}else{
				$$('.services_checkboxes')[0].removeClassName('error_validation');
			}
			if (window.File && window.FileReader && window.FileList && window.Blob) {

				var errorMsgFirm = document.getElementById('errors-firm-images');
				var showErrsFirm = false;

				firmLogoId = document.getElementsByName('firm_logo_new');
				var firmLogo = firmLogoId[0];
				if( firmLogo.files.length === 1){						
						var size2 = firmLogo.files[0].size;
						if ( size2 > 512000){														
							ret = false;
							errorMsgFirm.innerHTML = 'Firm logo file size exceeds maximum.';
							errorMsgFirm.style.display = "block";
							showErrsFirm = true;
						}
				}
				if(showErrsFirm === false){
					errorMsgFirm.style.display = "none";
				}

			}
			if (window.File && window.FileReader && window.FileList && window.Blob) {
				//files to be checked
				var picsId = new Array("portfolio_new[0]","portfolio_new[1]","portfolio_new[2]","portfolio_new[3]","portfolio_new[4]","portfolio_new[5]" );
				var errorMsg = document.getElementById('errors-images');
				var showErrs = false;

				for (var i=0;i<picsId.length;i++){
					var thisId = document.getElementsByName(picsId[i]);
					var target = thisId[0];					
					if( target.files.length === 1){						
						var size = target.files[0].size;
						if ( size > 1048576){												
							errorMsg.innerHTML = 'Portfolio Image #'+(i+1)+' file size exceeds maximum';
							errorMsg.style.display = "block";
							ret = false;
							showErrs = true;
						}
					}
				}    	 
				//hide errors 	    	
				if ( showErrs === false){
					errorMsg.style.display = "none";
				}
			}	
			/*if(!get_field('membership_affiliation_other').value.strip()&&!$$('[name="membership_affiliation[]"]:checked').length){
				$$('.membership_affiliation_checkboxes')[0].addClassName('error_validation');
				ret = false;
			}else{
				$$('.membership_affiliation_checkboxes')[0].removeClassName('error_validation');
			}*/
			return ret;
		}
	}
});