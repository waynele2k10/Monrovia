<?php
/**
 * @internal Plugin Template File [Field Manager]
 *
 * This is the plugin view file for fields manager. The view of forms field manager
 * is rendered from this file.
 *
 * use $data for data related to the view
 */
?>

<div class="rmagic">

    <!-----Operationsbar Starts-->
    <form method="post" id="rm_field_manager_form">
        <input type="hidden" name="rm_slug" value="" id="rm_slug_input_field">
        <div class="operationsbar">
            <div class="rmtitle"><?php echo RM_UI_Strings::get("TITLE_FORM_FIELD_PAGE"); ?></div>
            <div class="icons">
                <a href="?page=rm_form_add&rm_form_id=<?php echo $data->form_id; ?>"><img alt="" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/general-settings.png'; ?>"></a>
            </div>
            <div class="nav">
                <ul>
                    <li><a href="?page=rm_field_add&rm_form_id=<?php echo $data->form_id; ?>&rm_field_type"><?php echo RM_UI_Strings::get('LABEL_ADD_NEW'); ?></a></li>
                    <li onclick="jQuery.rm_do_action('rm_field_manager_form', 'rm_field_duplicate')"><a href="javascript:void(0)"><?php echo RM_UI_Strings::get('LABEL_DUPLICATE'); ?></a></li>
                    <li onclick="jQuery.rm_do_action('rm_field_manager_form', 'rm_field_remove')"><a href="javascript:void(0)"><?php echo RM_UI_Strings::get('LABEL_REMOVE'); ?></a></li>
                    <li class="rm-form-toggle"><?php echo RM_UI_Strings::get('LABEL_FILTER_BY'); ?>
                        <select id="rm_form_dropdown" name="form_id" onchange = "rm_load_page(this, 'field_manage')">
                            <?php
                            foreach ($data->forms as $form_id => $form)
                                if ($data->form_id == $form_id)
                                    echo "<option value=$form_id selected>$form</option>";
                                else
                                    echo "<option value=$form_id>$form</option>";
                            ?>
                        </select></li>
                </ul>
            </div>

        </div>
        <!--------Operationsbar Ends----->

        <!----Field Selector Starts---->

        <div class="rm-field-selector rm_tabbing_container">
            <div class="">
                <ul class="field-tabs">
                    <li class="field-tabs-row"><a href="#rm_common_fields_tab" class="rm_tab_links" id="rm_special_fields_tab_link"><?php echo RM_UI_Strings::get("LABEL_COMMON_FIELDS"); ?></a></li>  
                    <li class="field-tabs-row"><a href="#rm_special_fields_tab" class="rm_tab_links" id="rm_special_fields_tab_link"><?php echo RM_UI_Strings::get("LABEL_SPECIAL_FIELDS"); ?></a></li>
                    <li class="field-tabs-row"><a href="#rm_profile_fields_tab" class="rm_tab_links" id="rm_special_fields_tab_link"><?php echo RM_UI_Strings::get("LABEL_PROFILE_FIELDS"); ?></a></li>
                </ul>
            </div>
            <div class="field-selector-pills">
                <div id="rm_common_fields_tab">
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Textbox"><?php echo RM_UI_Strings::get("FIELD_TYPE_TEXT"); ?></a></div>  
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Select"><?php echo RM_UI_Strings::get("FIELD_TYPE_DROPDOWN"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Radio"><?php echo RM_UI_Strings::get("FIELD_TYPE_RADIO"); ?></a></div>  
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Textarea"><?php echo RM_UI_Strings::get("FIELD_TYPE_TEXTAREA"); ?></a></div>  
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Checkbox"><?php echo RM_UI_Strings::get("FIELD_TYPE_CHECKBOX"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=HTMLH"><?php echo RM_UI_Strings::get("FIELD_TYPE_HEADING"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=HTMLP"><?php echo RM_UI_Strings::get("FIELD_TYPE_PARAGRAPH"); ?></a></div>
                </div>
                <div id="rm_special_fields_tab">
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=jQueryUIDate"><?php echo RM_UI_Strings::get("FIELD_TYPE_DATE"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Email"><?php echo RM_UI_Strings::get("FIELD_TYPE_EMAIL"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Number"><?php echo RM_UI_Strings::get("FIELD_TYPE_NUMBER"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Country"><?php echo RM_UI_Strings::get("FIELD_TYPE_COUNTRY"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Timezone"><?php echo RM_UI_Strings::get("FIELD_TYPE_TIMEZONE"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Terms"><?php echo RM_UI_Strings::get("FIELD_TYPE_T_AND_C"); ?></a></div>
                    <div class="rm_button_like_links"><a class="rm_deactivated" href="javascript:void(0)"><?php echo RM_UI_Strings::get("FIELD_TYPE_FILE"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Price"><?php echo RM_UI_Strings::get("FIELD_TYPE_PRICE"); ?></a></div>
                    <div class="rm_button_like_links"><a class="rm_deactivated" href="javascript:void(0)"><?php echo RM_UI_Strings::get("FIELD_TYPE_RAPEAT"); ?></a></div>
                </div>
                <div id="rm_profile_fields_tab">
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Fname"><?php echo RM_UI_Strings::get("FIELD_TYPE_FNAME"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=Lname"><?php echo RM_UI_Strings::get("FIELD_TYPE_LNAME"); ?></a></div>
                    <div class="rm_button_like_links"><a href="?page=rm_field_add&amp;rm_form_id=<?php echo $data->form_id; ?>&amp;rm_field_type=BInfo"><?php echo RM_UI_Strings::get("FIELD_TYPE_BINFO"); ?></a></div>
                </div>
            </div>

        </div>

        <!----Slab View---->
        <ul class="rm-field-container" id="rm_sortable_form_fields">
            <?php
            if ($data->fields_data)
            {
                $i = 0;
                foreach ($data->fields_data as $field_data)
                {
                    ?>
                    <li id="<?php echo $field_data->field_id ?>">
                        <div class="rm-slab">
                            <div class="rm-slab-grabber">
                                <span class="rm_sortable_handle">
                                    <img alt="" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/rm-drag.png'; ?>">
                                </span>
                            </div>
                            <div class="rm-slab-content">
                                <input type="checkbox" name="rm_selected[]" value="<?php echo $field_data->field_id; ?>" <?php if ($field_data->is_field_primary == 1) echo "disabled"; ?>>
                                <span><?php echo $field_data->field_label; ?></span>
                                <span><?php echo RM_UI_Strings::get('LABEL_TYPE'); ?> = <?php echo $data->field_types[$field_data->field_type] ?></span>

                            </div>
                            <div class="rm-slab-buttons">

                                <a href="<?php echo '?page=rm_field_add&rm_form_id=' . $data->form_id . '&rm_field_type=' . $field_data->field_type . '&rm_field_id=' . $field_data->field_id . '"'; ?>"><?php echo RM_UI_Strings::get("LABEL_EDIT"); ?></a>

                                <?php
                                //var_dump($field_data->is_field_primary);die;
                                if ($field_data->is_field_primary == 1)
                                {
                                    ?>
                                    <a href="javascript:void(0)" class="rm_deactivated"><?php echo RM_UI_Strings::get("LABEL_DELETE"); ?></a>

                                    <?php
                                } else
                                {
                                    ?>

                                    <a href="<?php echo '?page=rm_field_manage&rm_form_id=' . $data->form_id . '&rm_field_id=' . $field_data->field_id . '&rm_action=delete"'; ?>"><?php echo RM_UI_Strings::get("LABEL_DELETE"); ?></a>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </li>

                    <?php
                }
            } else
            {
                echo RM_UI_Strings::get('NO_FIELDS_MSG');
            }
            ?>
        </ul>
    </form>

    <div class="rm-upgrade-note">
        <div class="rm-upgrade-note-title"><?php echo RM_UI_Strings::get('LABEL_WANT_MORE'); ?></div>
        <div class="rm-upgrade-note-content"><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/blue-cog.png'; ?>"><?php echo RM_UI_Strings::get('MSG_BUY_PRO_FIELDS'); ?>
            <span class="rm-upgrade-note-emphasis">
                <a href="http://registrationmagic.com/?download_id=317&amp;edd_action=add_to_cart" target="_blank"><?php echo RM_UI_Strings::get('LABEL_CLICK_HERE'); ?></a>
            </span><?php echo RM_UI_Strings::get('LABEL_TO_UPGRADE'); ?></div>

    </div>
</div>
