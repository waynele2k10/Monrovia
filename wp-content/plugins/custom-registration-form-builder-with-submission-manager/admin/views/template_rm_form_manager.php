<?php
/**
 * @internal Template File [Form Manager]
 *
 * This file renders the form manager page of the plugin which shows all the forms
 * to manage delete edit or manage
 */
?>
<div class="shortcode_notification"><p class="rm-notice-para"><?php echo RM_UI_Strings::get('RM_LOGIN_HELP'); ?></p></div>
<div class="rmagic">


    <!--  Operations bar Starts  -->
    <form name="rm_form_manager" id="rm_form_manager_operartionbar" class="rm_static_forms" method="post" action="">
        <input type="hidden" name="rm_slug" value="" id="rm_slug_input_field">
        <div class="operationsbar">
            <div class="rmtitle"><?php echo RM_UI_Strings::get('TITLE_FORM_MANAGER'); ?></div>
            <div class="icons">
                <a href="?page=rm_options_general"><img alt="" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/general-settings.png'; ?>"></a>
            </div>
            <div class="nav">
                <ul>
                    <li><a href="admin.php?page=rm_form_add"><?php echo RM_UI_Strings::get('LABEL_ADD_NEW'); ?></a></li>
                    <li onclick="jQuery.rm_do_action('rm_form_manager_operartionbar', 'rm_form_duplicate')"><a href="javascript:void(0)"><?php echo RM_UI_Strings::get('LABEL_DUPLICATE'); ?></a></li>
                    <li onclick="jQuery.rm_do_action_with_alert('<?php echo RM_UI_Strings::get('ALERT_DELETE_FORM'); ?>', 'rm_form_manager_operartionbar', 'rm_form_remove')"><a href="javascript:void(0)"><?php echo RM_UI_Strings::get('LABEL_REMOVE'); ?></a></li>
                    <li class="rm-form-toggle">Sort by<select onchange="rm_sort_forms(this, '<?php echo $data->curr_page; ?>')">
                            <option value=null><?php echo RM_UI_Strings::get('LABEL_SELECT'); ?></option>
                            <option value="form_name"><?php echo RM_UI_Strings::get('LABEL_NAME'); ?></option>
                            <option value="form_id"><?php echo RM_UI_Strings::get('FIELD_TYPE_DATE'); ?></option>
                        </select></li>
                </ul>
            </div>
        </div>
        <input type="hidden" name="rm_selected" value="">
    </form>

    <!--  *****Operations bar Ends****  -->

    <!--  ****Content area Starts****  -->

    <div class="rmagic-cards">

        <div class="rmcard">
            <?php
            $form = new Form("rm_form_quick_add");
            $form->configure(array(
                "prevent" => array("bootstrap", "jQuery"),
                "action" => ""
            ));
            $form->addElement(new Element_HTML('<div class="rm-new-form">'));
            $form->addElement(new Element_Hidden("rm_slug", 'rm_form_quick_add'));
            $form->addElement(new Element_Textbox('', "form_name", array("id" => "rm_form_name", "required" => 1)));
            $form->addElement(new Element_Button(RM_UI_Strings::get('LABEL_CREATE_FORM'), "submit", array("id" => "rm_submit_btn", "onClick" => "jQuery.prevent_quick_add_form(event)", "class" => "rm_btn", "name" => "submit")));
            $form->addElement(new Element_HTML('</div>'));
            $form->render();
            ?></div>
        <?php
        if (is_array($data->data) || is_object($data->data))
            foreach ($data->data as $entry)
            {
                ?><?php // echo $entry; ?>

                <div id="<?php echo $entry->form_id; ?>" class="rmcard">

                    <div class="cardtitle">
                        <input class="rm_checkbox" type="checkbox" name="rm_selected_forms[]" value="<?php echo $entry->form_id; ?>"><?php echo $entry->form_name; ?></div>
                    <div class="rm-last-submission">
                        <b><?php echo RM_UI_Strings::get('LABEL_REGISTRATIONS'); ?> <a href="?page=rm_submission_manage&rm_form_id=<?php echo $entry->form_id; ?>">(<?php echo $entry->count; ?>)</a></b></div>

                    <?php
                    if ($entry->count > 0)
                    {
                        foreach ($entry->submissions as $submission)
                        {
                            ?>
                            <div class="rm-last-submission">

                                <?php
                                echo $submission->gravatar . ' ' . RM_Utilities::localize_time($submission->submitted_on);
                                ?>
                            </div>
                            <?php
                        }
                    } else
                        echo '<div class="rm-last-submission">' . RM_UI_Strings::get('MSG_NO_SUBMISSION') . '</div>';
                    ?>
                    <div class="rm-form-shortcode"><b>[RM_Form id='<?php echo $entry->form_id; ?>']</b></div>
                    <div class="rm-form-links">
                        <div class="rm-form-row"><a href="admin.php?page=rm_form_add&rm_form_id=<?php echo $entry->form_id; ?>"><?php echo RM_UI_Strings::get('SETTINGS'); ?></a></div>
                        <div class="rm-form-row"><a href="admin.php?page=rm_field_manage&rm_form_id=<?php echo $entry->form_id; ?>"><?php echo RM_UI_Strings::get('LABEL_FIELDS'); ?></a></div>
                    </div>

                </div>
                <?php
            } else
            echo "<h4>" . RM_UI_Strings::get('LABEL_NO_FORMS') . "</h4>";
        ?>
    </div>
    <?php if ($data->total_pages > 1): ?>
        <ul class="rmpagination">
            <?php if ($data->curr_page > 1): ?>
                <li><a href="?page=<?php echo $data->rm_slug ?>&rm_reqpage=<?php echo $data->curr_page - 1;
        if ($data->sort_by) echo'&rm_sortby=' . $data->sort_by;if (!$data->descending) echo'&rm_descending=' . $data->descending; ?>">«</a></li>
                <?php
            endif;
            for ($i = 1; $i <= $data->total_pages; $i++):
                if ($i != $data->curr_page):
                    ?>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_reqpage=<?php echo $i;
            if ($data->sort_by) echo'&rm_sortby=' . $data->sort_by;if (!$data->descending) echo'&rm_descending=' . $data->descending; ?>"><?php echo $i; ?></a></li>
                <?php else:
                    ?>
                    <li><a class="active" href="?page=<?php echo $data->rm_slug ?>&rm_reqpage=<?php echo $i;
            if ($data->sort_by) echo'&rm_sortby=' . $data->sort_by;if (!$data->descending) echo'&rm_descending=' . $data->descending; ?>"><?php echo $i; ?></a></li> <?php
                endif;
            endfor;
            ?>
            <?php if ($data->curr_page < $data->total_pages): ?>
                <li><a href="?page=<?php echo $data->rm_slug ?>&rm_reqpage=<?php echo $data->curr_page + 1;
        if ($data->sort_by) echo'&rm_sortby=' . $data->sort_by;if (!$data->descending) echo'&rm_descending=' . $data->descending; ?>">»</a></li>
            <?php endif;
        ?>
        </ul>
<?php endif; ?>

    <div class="rm-rating-banner">

        <div class="rm-rating-banner-icon"><img width="85" height="50" alt="United States" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/rm-rating-banner.png'; ?>">
        </div>

        <div class="rm-banner-review"> <?php echo RM_UI_Strings::get('MSG_LIKED_RM'); ?> 
            <a href="https://wordpress.org/support/view/plugin-reviews/custom-registration-form-builder-with-submission-manager" target="blank"> <?php echo RM_UI_Strings::get('MSG_CLICK_TO_REVIEW'); ?></a>.</div>

    </div>

</div>

