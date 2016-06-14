<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div class="rmagic rm_tabbing_container"> 

    <!-----Operationsbar Starts-->

    <div class="operationsbar">
        <!--        <div class="rmtitle">Submissions</div>-->
        <div class="nav">
            <ul>
                <li class="rm_back_button" onclick="window.history.back()"><a><?php echo '&larr; ' . RM_UI_Strings::get('LABEL_BACK'); ?></a></li>
                <li><a href="#rm_my_sub_tab"><?php echo RM_UI_Strings::get('LABEL_MY_SUB'); ?></a></li>
                <?php
                if ($data->payment)
                {
                    ?>

                    <li><a href="#rm_my_pay_tab"><?php echo RM_UI_Strings::get('LABEL_PAY_HISTORY'); ?></a></li>
                    <?php
                }
                if (!is_user_logged_in())
                {
                    ?>
                    <li class="rm-form-toggle" onclick="document.getElementById('rm_front_submissions_nav_form').submit()"><?php echo RM_UI_Strings::get('LABEL_LOG_OFF'); ?></li>
                    <?php
                }
                ?>
            </ul>
            <form method="post" id="rm_front_submissions_nav_form">
                <input type="hidden" name="rm_slug" value="rm_front_log_off">
            </form>


        </div>


    </div>
    <div class="rm-submission" id="rm_my_sub_tab">     

        <?php
        if ($data->form_is_unique_token)
        {
            ?>
            <div class="rm-submission-field-row">
                <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_UNIQUE_TOKEN_SHORT'); ?> :</div>
                <div class="rm-submission-value"><?php echo $data->submission->get_unique_token(); ?></div>
            </div>
            <?php
        }
        ?>

        <div class="rm-submission-field-row">
            <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_ENTRY_ID'); ?></div>
            <div class="rm-submission-value"><?php echo $data->submission->get_submission_id(); ?></div>
        </div>

        <div class="rm-submission-field-row">
            <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_ENTRY_TYPE'); ?></div>
            <div class="rm-submission-value"><?php echo $data->form_type; ?></div>
        </div>
        <?php
        if ($data->form_type_status == "1" && !empty($data->user))
        {
            $user_roles_dd = RM_Utilities::user_role_dropdown();
            ?>
            <div class="rm-submission-field-row">
                <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_USER_NAME'); ?></div>
                <div class="rm-submission-value"><?php echo $data->user->display_name; ?></div>
            </div>

            <div class="rm-submission-field-row">
                <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_USER_ROLES'); ?></div>
                <div class="rm-submission-value"><?php echo $user_roles_dd[(implode(',', $data->user->roles))]; ?></div>
            </div>

            <?php
        }
        ?>
        <?php
        $submission_data = $data->submission->get_data();
        if (is_array($submission_data) || $submission_data)
            foreach ($submission_data as $field_id => $sub):

                $sub_key = $sub->label;
                $sub_data = $sub->value;
                ?>

                <!--submission row block-->


                <div class="rm-submission-field-row">
                    <div class="rm-submission-label"><?php echo $sub_key; ?></div>
                    <div class="rm-submission-value">
                        <?php
                        //if submitted data is array print it in more than one row.

                        if (is_array($sub_data))
                        {

                            $i = 0;

                            //If submitted data is a file.

                            if (isset($sub_data['rm_field_type']) && $sub_data['rm_field_type'] == 'File')
                            {
                                unset($sub_data['rm_field_type']);

                                foreach ($sub_data as $sub)
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
                                $sub = implode(', ', $sub_data);
                                echo $sub;
                            }
                        } else
                        {
                            echo $sub_data;
                        }
                        ?>
                    </div>
                </div><!-- End of one submission block-->
                <?php
            endforeach;
        ?>
    </div>
    <?php
    if ($data->payment)
    {
        ?>
        <div class="rm-submission" id="rm_my_pay_tab"> 
            <?php
            if ($data->payment->log):
                ?>
                <div class="rm-submission-field-row">
                    <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_PAYER_NAME'); ?></div>
                    <div class="rm-submission-value"><?php
                        if (isset($data->payment->log['first_name']))
                            echo $data->payment->log['first_name'];
                        if (isset($data->payment->log['last_name']))
                            echo ' ' . $data->payment->log['last_name'];
                        ?></div>
                </div>
                <div class="rm-submission-field-row">
                    <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_PAYER_EMAIL'); ?></div>
                    <div class="rm-submission-value"><?php if (isset($data->payment->log['payer_email'])) echo $data->payment->log['payer_email']; ?></div>
                </div>
                <?php
            endif;
            ?>

            <div class="rm-submission-field-row">
                <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_INVOICE'); ?></div>
                <div class="rm-submission-value"><?php if (isset($data->payment->invoice)) echo $data->payment->invoice; ?></div>
            </div>
            <div class="rm-submission-field-row">
                <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_TAXATION_ID'); ?></div>
                <div class="rm-submission-value"><?php if (isset($data->payment->txn_id)) echo $data->payment->txn_id; ?></div>
            </div>
            <div class="rm-submission-field-row">
                <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_STATUS_PAYMENT'); ?></div>
                <div class="rm-submission-value"><?php if (isset($data->payment->status)) echo $data->payment->status; ?></div>
            </div>
            <div class="rm-submission-field-row">
                <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_PAID_AMOUNT'); ?></div>
                <div class="rm-submission-value"><?php if (isset($data->payment->total_amount)) echo $data->payment->total_amount; ?></div>
            </div>
            <div class="rm-submission-field-row">
                <div class="rm-submission-label"><?php echo RM_UI_Strings::get('LABEL_DATE_OF_PAYMENT'); ?></div>
                <div class="rm-submission-value"><?php if (isset($data->payment->posted_date)) echo RM_Utilities::localize_time($data->payment->posted_date, get_option('date_format')); ?></div>
            </div>
        </div>
        <?php
    }
    ?>


    <?php
    if ($data->notes && (is_object($data->notes) || is_array($data->notes)))
    {
        ?>
        <div class="rmsubtitle"><?php echo RM_UI_Strings::get('LABEL_ADMIN_NOTES'); ?></div>
        <?php
        foreach ($data->notes as $note)
        {
            ?>
            <div class="rm-submission-note" style="border-left: 4px solid #<?php echo maybe_unserialize($note->note_options)->bg_color; ?>">
                <div class="rm-submission-note-text"><?php echo $note->notes; ?></div>
                <div class="rm-submission-note-attribute">

                    <?php
                    echo RM_UI_Strings::get('LABEL_CREATED_BY') . " <b>" . $note->author . "</b> <em>" . RM_Utilities::localize_time($note->publication_date) . "</em>";
                    if ($note->editor)
                        echo " (" . RM_UI_Strings::get('LABEL_EDITED_BY') . " <b>" . $note->editor . "</b> <em>" . RM_Utilities::localize_time($note->last_edit_date) . "</em>";
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}
?>
</div>