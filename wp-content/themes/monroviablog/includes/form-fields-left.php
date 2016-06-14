<?php
?>
<tr><td valign="top"><div class="field_label">first name</div></td><td><input type="text" name="first_name" value="<?php echo (isset($profile['first_name'])?$profile['first_name']:'')?>" class="field_text" validation_required="true" maxlength="40" /></td></tr>
<tr><td valign="top"><div class="field_label">last name</div></td><td><input type="text" name="last_name" value="<?php echo (isset($profile['last_name'])?$profile['last_name']:'')?>" class="field_text" validation_required="true" maxlength="40" /></td></tr>
<tr><td valign="top"><div class="field_label">firm name</div></td><td><input type="text" name="firm_name" value="<?php echo (isset($profile['firm_name'])?$profile['firm_name']:'')?>" class="field_text" validation_required="true" maxlength="255" /></td></tr>
<tr><td valign="top"><div class="field_label not_required">address</div></td><td><input type="text" name="address" value="<?php echo (isset($profile['address'])?$profile['address']:'')?>" class="field_text" maxlength="255" /></td></tr>
<tr><td valign="top"><div class="field_label">city</div></td><td><input type="text" name="city" value="<?php echo (isset($profile['city'])?$profile['city']:'')?>" class="field_text" validation_required="true" maxlength="255" /></td></tr>
<tr><td valign="top"><div class="field_label">state</div></td><td><div class="select-wrap"><select name="state" class="field_select field_state" validation_required="true">
<option value="">state</option>
<?php
	include($_SERVER['DOCUMENT_ROOT'].'/inc/state_field_options.php');
	output_state_select_options( $profile['state'] );
		?></select></div></td></tr>
<tr><td valign="top"><div class="field_label">zip</div></td><td>
<input type="text" name="zip" value="<?php echo (isset($profile['zip'])?$profile['zip']:'')?>" class="field_text field_zip" validation_required="true" validation_type="zip" maxlength="7" /></td></tr>
<tr><td valign="top"><div class="field_label">country</div></td><td><div class="select-wrap"><select name="country" class="field_select" validation_required="true"><?php display_designers_dropdown_options('countries', isset($profile['country'])?$profile['country']:''); ?></select></div></td></tr>
<tr><td valign="top"><div class="field_label">email address</div></td><td><input type="text" name="email" value="<?php echo (isset($profile['email'])?$profile['email']:'')?>" class="field_text" validation_required="true" validation_type="email" maxlength="255" /></td></tr>
<tr><td valign="top"><div class="field_label not_required">website address</div></td><td><input type="text" name="website" value="<?php echo (isset($profile['website'])?$profile['website']:'')?>" class="field_text" validation_type="url" maxlength="255" /></td></tr>
<tr><td valign="top"><div class="field_label">phone number</div></td><td><input type="text" name="phone" value="<?php echo (isset($profile['phone'])?$profile['phone']:'')?>" class="field_text" validation_required="true" validation_type="phone" maxlength="12" /></td></tr>
<tr><td valign="top"><div class="field_label not_required">fax number</div></td><td><input type="text" name="fax" value="<?php echo (isset($profile['fax'])?$profile['fax']:'')?>" class="field_text" validation_type="phone" maxlength="12" /></td></tr>

<tr><td colspan="2">
    <div class="field_label not_required" style="text-align:left;padding-bottom:4px;">membership affiliation: <!--<span style="font-weight:normal">(Please provide at least one.)</span>--></div>
    </td></tr>
<tr><td></td><td>
	<div class="membership_affiliation_checkboxes">
        <?php display_designers_checkboxes('membership_affiliation', 'membership_affiliation', isset($profile['membership_affiliation'])?explode(', ', $profile['membership_affiliation']):array()); ?>
        <div class="clear"></div>
        <label for="chk_membership_affiliation_other" style="white-space:nowrap;">Other/additional:</label>
        <div class="clear"></div>
        <input type="text" name="membership_affiliation_other" value="<?php echo (isset($profile['membership_affiliation_other'])?$profile['membership_affiliation_other']:'')?>" class="field_text" maxlength="255" style="width:150px;" />
    </div>
	</td>
</tr>

<tr><td valign="top"><div class="field_label">specialty</div></td><td><div class="select-wrap"><select name="specialty" class="field_select" validation_required="true"><option value="">Choose one</option><?php display_designers_dropdown_options('specialty', isset($profile['specialty'])?$profile['specialty']:''); ?></select></div></td></tr>
<!--<tr><td valign="top"><div class="field_label">services</div></td><td><select name="services" class="field_select" validation_required="true"><option value="">Choose one</option><?php display_designers_dropdown_options('services', isset($profile['services'])?$profile['services']:''); ?></select></td></tr>-->
<!--<tr><td valign="top"><div class="field_label">services</div></td><td><select name="services" class="field_select" validation_required="true"><option value="">Choose one</option><?php display_designers_dropdown_options('services', isset($profile['services'])?$profile['services']:''); ?></select></td></tr>-->

<tr><td colspan="2">
    <div class="field_label" style="text-align:left;padding-bottom:4px;">services: <span style="font-weight:normal">(Please provide at least one.)</span></div>
    </td></tr>
<tr><td></td><td>
    <div class="services_checkboxes">
        <?php display_designers_checkboxes('services', 'services', isset($profile['services'])?explode(', ', $profile['services']):array()); ?>
        <div class="clear"></div>        
        <div class="clear"></div>        
    </div>
    </td>
</tr>


<tr><td valign="top"><div class="field_label">My favorite Monrovia plant</div></td><td><input type="text" name="favorite_plant" id="favorite_plant" value="<?php echo (isset($profile['favorite_plant'])?$profile['favorite_plant']:'')?>" class="field_text" validation_required="true" maxlength="255" /></td></tr>
<tr><td valign="top"><div class="field_label">Why it's my favorite</div></td><td><textarea name="favorite_plant_why" class="field_textarea" validation_required="true"><?php echo (isset($profile['favorite_plant_why'])?$profile['favorite_plant_why']:'')?></textarea></td></tr>
<tr><td valign="top"><div class="field_label not_required">profile</div></td><td><textarea name="profile" class="field_textarea"><?php echo (isset($profile['profile'])?$profile['profile']:'')?></textarea></td></tr>