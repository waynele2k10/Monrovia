<?php
//echo '<pre>';print_r($data);die;
?>

<div class="rmagic">

    <!-----Operationsbar Starts----->

    <div class="operationsbar">
        <div class="rmtitle"><?php echo $data->user->data->display_name; ?></div>
        <div class="icons">
        </div>
        <div class="nav">
            <ul>
                <li><a href="<?php echo get_admin_url() . 'user-edit.php?user_id=' . $data->user->ID; ?>"><?php echo RM_UI_Strings::get('LABEL_EDIT'); ?></a></li>
                <?php
                if ($data->curr_user != $data->user->ID)
                {
                    ?>
                    <li onclick="jQuery.rm_do_action('form_user_page_action', 'rm_user_delete')"><a href="#"><?php echo RM_UI_Strings::get('LABEL_DELETE'); ?></a></li>
                    <?php
                }
                ?>
                <?php
                if ($data->user->rm_user_status != 1)
                {
                    if ($data->curr_user != $data->user->ID)
                    {
                        ?>
                        <li onclick="jQuery.rm_do_action('form_user_page_action', 'rm_user_deactivate')"><a href="#"><?php echo RM_UI_Strings::get('DEACTIVATE'); ?></a></li>
                        <?php
                    }
                } else
                {
                    ?>
                    <li onclick="jQuery.rm_do_action('form_user_page_action', 'rm_user_activate')"><a href="#"><?php echo RM_UI_Strings::get('ACTIVATE'); ?></a></li>
                    <?php
                }
                ?>
            </ul>
        </div>

    </div>
    <!--------Operationsbar Ends----->

    <!----User Area Starts---->

    <div class="rm-user-area">

        <div class="rm-user-info">
            <div class="rm-profile-image">
                <?php echo get_avatar($data->user->ID, 250); ?>
            </div>
            <div class="rm-profile-fields">

                <div class="rm-profile-field-row">

                    <div class="rm-field-label"><?php echo RM_UI_Strings::get('FIELD_TYPE_FNAME'); ?></div>
                    <div class="rm-field-value"><?php echo $data->user->first_name; ?></div>
                </div>

                <div class="rm-profile-field-row">

                    <div class="rm-field-label"><?php echo RM_UI_Strings::get('FIELD_TYPE_LNAME'); ?></div>
                    <div class="rm-field-value"><?php echo $data->user->last_name; ?></div>
                </div>

                <div class="rm-profile-field-row">

                    <div class="rm-field-label"><?php echo RM_UI_Strings::get('LABEL_EMAIL'); ?></div>
                    <div class="rm-field-value"><?php echo $data->user->user_email; ?></div>
                </div>

                <div class="rm-profile-field-row">

                    <div class="rm-field-label"><?php echo RM_UI_Strings::get('LABEL_ROLE'); ?></div>
                    <?php
                    foreach ($data->user->roles as $role)
                    {
                        $user_roles = RM_Utilities::user_role_dropdown();
                        ?>
                        <div class="rm-field-value"><?php echo $user_roles[$role]; ?></div>
                        <?php
                    }
                    ?>
                </div>

                <div class="rm-profile-field-row">

                    <div class="rm-field-label"><?php echo RM_UI_Strings::get('LABEL_BIO'); ?></div>
                    <div class="rm-field-value"><?php echo $data->user->description; ?></div>
                </div>

            </div>

        </div>
        <div class="rm_tabbing_container">

            <ul class="rm-profile-nav">
                <li class="rm-profile-nav-item"><a href="#rmfirsttabcontent"><?php echo RM_UI_Strings::get('LABEL_CUSTOM_FIELD'); ?></a></li>
                <li class="rm-profile-nav-item"><a href="#rmsecondtabcontent"><?php echo RM_UI_Strings::get('LABEL_SUBMISSIONS'); ?></a></li>
                <li class="rm-profile-nav-item"><a href="#rmthirdtabcontent"><?php echo RM_UI_Strings::get('LABEL_PAYMENTS'); ?></a></li>

            </ul>

            <div class="rm-user-content">
                <div class="rm-profile-fields" id="rmfirsttabcontent">

                    <?php
                    if (is_array($data->custom_fields) || is_object($data->custom_fields))
                        foreach ($data->custom_fields as $field_id => $sub)
                        {
                            $key = $sub->label;
                            $meta = $sub->value;

                            $meta = RM_Utilities::strip_slash_array(maybe_unserialize($meta));
                            ?>
                            <div class="rm-profile-field-row">

                                <div class="rm-field-label"><?php echo $key; ?></div>
                                <div class="rm-field-value">
                                    <?php
                                    if (is_array($meta) || is_object($meta))
                                    {
                                        if (isset($meta['rm_field_type']) && $meta['rm_field_type'] == 'File')
                                        {
                                            unset($meta['rm_field_type']);

                                            foreach ($meta as $sub)
                                            {

                                                $att_path = get_attached_file($sub);
                                                $att_url = wp_get_attachment_url($sub);
                                                ?>
                                                <div class="rm-submission-attachment">
                                                    <?php echo wp_get_attachment_link($sub, 'thumbnail', false, true, false); ?>
                                                    <div class="rm-submission-attachment-field"><?php echo basename($att_path); ?></div>
                                                    <div class="rm-submission-attachment-field"><a href="<?php echo $att_url; ?>"><?php echo RM_UI_Strings::get('LABEL_DOWNLOAD'); ?></a></div>
                                                </div>

                                                <?php
                                            }
                                        } else
                                        {
                                            $sub = implode(', ', $meta);
                                            echo $sub;
                                        }
                                    } else
                                    {
                                        echo $meta;
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        } else
                        echo "<div class='rmnotice'>" . RM_UI_Strings::get('MSG_NO_CUSTOM_FIELDS') . "</div>";
                    ?>

                </div>

                <table class="user-content" id="rmsecondtabcontent">

                    <?php
                    if (count($data->submissions) !== 0)
                    {
                        ?>

                        <th>#</th> <th><?php echo RM_UI_Strings::get('LABEL_FORM'); ?></th> <th><?php echo RM_UI_Strings::get('LABEL_DATE'); ?></th> <th>&nbsp;</th> <th>&nbsp;</th>

                        <?php
                        $i = 0;
                        foreach ($data->submissions as $sub)
                        {
                            $form_name = ($sub->form_name) ? : 'FORM DELETED'
                            ?>
                            <tr> <td><?php echo $i++; ?></td><td><?php echo $form_name; ?></td><td><?php echo $sub->submitted_on; ?></td><td><a href="?page=rm_submission_view&rm_submission_id=<?php echo $sub->submission_id; ?>&rm_form_id=<?php echo $sub->form_id; ?>"><img class="icon" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/view_form.png'; ?>"></a></td><td><a href="javascript:void(0)" class="rm_deactivated"><img class="icon" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/form_download.png'; ?>"></a></td>
                            <form id="rmsubmissionuserform<?php echo $sub->submission_id; ?>" method="post">
                                <input type="hidden" value="<?php echo $sub->submission_id; ?>" name="rm_submission_id">
                                <input type="hidden" value="rm_submission_print_pdf" name="rm_slug">
                            </form>
                            </tr>
                            <?php
                        }
                    } else
                    {
                        echo "<tr> <td class='rmnotice'>" . RM_UI_Strings::get('MSG_NO_SUBMISSIONS_USER') . "</td></tr>";
                    }
                    ?>

                </table>

                <table class="user-content" id="rmthirdtabcontent">
                    <?php
                    if (count($data->payments) != 0)
                    {
                        ?>
                        <th>#</th> <th><?php echo RM_UI_Strings::get('LABEL_FORM'); ?></th> <th><?php echo RM_UI_Strings::get('LABEL_DATE'); ?></th> <th><?php echo RM_UI_Strings::get('LABEL_PAYMENT'); ?></th> <th>&nbsp;</th>
                        <?php
                        $i = 0;
                        foreach ($data->payments as $payment)
                        {
                            ?>
                            <tr> <td><?php echo $i++; ?></td><td><?php echo $payment['form_name']; ?></td><td><?php echo $payment['payment']->posted_date; ?></td><td><?php echo $payment['payment']->status; ?></td><td><a href="?page=rm_submission_view&rm_submission_id=<?php echo $payment['submission_id']; ?>&rm_form_id=<?php echo $payment['form_id']; ?>"><img class="icon" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/view_form.png'; ?>"></a></td></tr>
                            <?php
                        }
                    } else
                        echo "<tr> <td class='rmnotice'>" . RM_UI_Strings::get('MSG_NO_PAYMENTS_USER') . "</td></tr>";
                    ?>
                </table>

            </div>
        </div>
    </div>

    <form id="form_user_page_action" method="post">
        <input type="hidden" name="rm_slug" value="" id="rm_slug_input_field">
        <input type="hidden" name="rm_users[]" value="<?php echo $data->user->ID; ?>">
    </form>

</div>
