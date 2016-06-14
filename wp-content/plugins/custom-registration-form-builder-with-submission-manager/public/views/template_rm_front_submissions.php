<?php
//echo "<pre>", var_dump($data);
/**
 * Plugin Template File[For Front End Submission Page]
 */
?>

<!-- setup initial tab -->
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("#rm_tabbing_container_front_sub").tabs({active: <?php echo $data->active_tab_index; ?>});
    });

    function get_tab_and_redirect(reqpagestr) {
        //alert(reqpage);
        var tab_index = jQuery("#rm_tabbing_container_front_sub").tabs("option", "active");
        var curr_url = window.location.href;
        var sign = '&';
        if (curr_url.indexOf('?') === -1)
            sign = '?';
        window.location.href = curr_url + sign + reqpagestr + '&rm_tab=' + tab_index;
    }
</script>

<?php
if (!$data->payments && !$data->submissions && $data->is_user !== true)
{
    ?>

    <div class="rmnotice-container"><div class="rmnotice"><?php echo RM_UI_Strings::get('MSG_NO_DATA_FOR_EMAIL'); ?></div></div>
    <?php
}
?>
<div class="rmagic rm_tabbing_container" id="rm_tabbing_container_front_sub"> 

    <!-----Operationsbar Starts-->

    <div class="operationsbar">
        <!--        <div class="rmtitle">Submissions</div>-->
        <div class="nav">
            <ul>
                <?php
                if ($data->is_user === true)
                {
                    ?>
                    <li><a href="#rm_my_details_tab"><?php echo RM_UI_Strings::get('LABEL_MY_DETAILS'); ?></a></li>
                    <?php
                }
                ?>
                <li><a href="#rm_my_sub_tab"><?php echo RM_UI_Strings::get('LABEL_MY_SUBS'); ?></a></li>
                <?php
                if ($data->payments)
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
    <!--------Operationsbar Ends----->

    <!-------Contentarea Starts----->

    <!----Table Wrapper---->

    <!-- User Page -->
    <?php
    if ($data->is_user)
    {
        ?>
        <div class="rm-submission" id="rm_my_details_tab">
            <div class="rm-user-details-card">
                <div class="rm-user-image-container">
                    <div class="rm-user-name"><?php echo $data->user->display_name; ?></div>
                    <div class="rm-user-image">
                        <?php
                        echo get_avatar($data->user->ID, 512, '', '', array('class' => 'rm-user'));
                        ?>
                    </div>
                </div>
                <div class="rm-user-fields-container">
                    <?php
                    if ($data->user->first_name)
                    {
                        ?>
                        <div class="rm-user-field-row">
                            <div class="rm-user-field-label"><?php echo RM_UI_Strings::get('FIELD_TYPE_FNAME'); ?>:</div>
                            <div class="rm-user-field-value"><?php echo $data->user->first_name; ?></div>
                        </div>
                        <?php
                    }
                    if ($data->user->last_name)
                    {
                        ?>

                        <div class="rm-user-field-row">
                            <div class="rm-user-field-label"><?php echo RM_UI_Strings::get('FIELD_TYPE_LNAME'); ?>:</div>
                            <div class="rm-user-field-value"><?php echo $data->user->last_name; ?></div>
                        </div>
                        <?php
                    }
                    if ($data->user->description)
                    {
                        ?>

                        <div class="rm-user-field-row">
                            <div class="rm-user-field-label"><?php echo RM_UI_Strings::get('LABEL_BIO'); ?>:</div>
                            <div class="rm-user-field-value"><?php echo $data->user->description; ?></div>
                        </div>
                        <?php
                    }
                    if (is_array($data->custom_fields) || is_object($data->custom_fields))
                        foreach ($data->custom_fields as $field_id => $sub)
                        {
                            $key = $sub->label;
                            $meta = $sub->value;

                            $meta = RM_Utilities::strip_slash_array(maybe_unserialize($meta));
                            ?>
                            <div class="rm-user-field-row">

                                <div class="rm-user-field-label"><?php echo $key; ?></div>
                                <div class="rm-user-field-value">
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
                                                <div class="rm-user-attachment">
                                                    <?php echo wp_get_attachment_link($sub, 'thumbnail', false, true, false); ?>
                                                    <div class="rm-user-attachment-field"><?php echo basename($att_path); ?></div>
                                                    <div class="rm-user-attachment-field"><a href="<?php echo $att_url; ?>"><?php echo RM_UI_Strings::get('LABEL_DOWNLOAD'); ?></a></div>
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
                        <?php }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="rmagic-table" id="rm_my_sub_tab">
        <?php
        if ($data->submission_exists === true)
        {
            ?>
            <table class="rm-table">
                <tr>
                    <th><?php echo RM_UI_Strings::get('LABEL_SR'); ?></th>
                    <th><?php echo RM_UI_Strings::get('LABEL_FORM'); ?></th>
                    <th><?php echo RM_UI_Strings::get('LABEL_DATE'); ?></th>
                    <th><?php echo RM_UI_Strings::get('LABEL_DOWNLOAD'); ?></th>
                </tr>
                <?php
                $i = 0;
                if ($data->submissions):
                    foreach ($data->submissions as $data_single):
                        ?>  
                        <tr>
                            <td id="<?php echo $data_single->submission_id; ?>"><?php echo $i++; ?></td>
                            <td><a href="?submission_id=<?php echo $data_single->submission_id; ?>"><?php echo $data_single->form_name; ?></a></td>
                            <td><?php echo RM_Utilities::localize_time($data_single->submitted_on, $data->date_format); ?></td>
                            <td><img onclick="document.getElementById('rmsubmissionfrontform<?php echo $data_single->submission_id; ?>').submit()" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/download-icon.png'; ?>"></td>
                        <form id="rmsubmissionfrontform<?php echo $data_single->submission_id; ?>" method="post">
                            <input type="hidden" value="<?php echo $data_single->submission_id; ?>" name="rm_submission_id">
                            <input type="hidden" value="rm_submission_print_pdf" name="rm_slug">
                        </form>    
                        </tr>
                        <?php
                    endforeach;
                else:

                endif;
                ?>
            </table>
            <?php
            /*             * ********** Pagination Logic ************** */
            $max_pages_without_abb = 10;
            $max_visible_pages_near_current_page = 3; //This many pages will be shown on both sides of current page number.

            if ($data->total_pages_sub > 1):
                ?>
                <ul class="rmpagination">
                    <?php
                    if ($data->curr_page_sub > 1):
                        ?>
                        <li onclick="get_tab_and_redirect('rm_reqpage_sub=1')"><a><?php echo RM_UI_Strings::get('LABEL_FIRST'); ?></a></li>
                        <li onclick="get_tab_and_redirect('rm_reqpage_sub=<?php echo $data->curr_page_sub - 1; ?>')"><a><?php echo RM_UI_Strings::get('LABEL_PREVIOUS'); ?></a></li>
                        <?php
                    endif;
                    if ($data->total_pages_sub > $max_pages_without_abb):
                        if ($data->curr_page_sub > $max_visible_pages_near_current_page + 1):
                            ?>
                            <li><a> ... </a></li>
                            <?php
                            $first_visible_page = $data->curr_page_sub - $max_visible_pages_near_current_page;
                        else:
                            $first_visible_page = 1;
                        endif;

                        if ($data->curr_page_sub < $data->total_pages_sub - $max_visible_pages_near_current_page):
                            $last_visible_page = $data->curr_page_sub + $max_visible_pages_near_current_page;
                        else:
                            $last_visible_page = $data->total_pages_sub;
                        endif;
                    else:
                        $first_visible_page = 1;
                        $last_visible_page = $data->total_pages_sub;
                    endif;
                    for ($i = $first_visible_page; $i <= $last_visible_page; $i++):
                        if ($i != $data->curr_page_sub):
                            ?>
                            <li onclick="get_tab_and_redirect('rm_reqpage_sub=<?php echo $i; ?>')"><a><?php echo $i; ?></a></li>
                        <?php else:
                            ?>
                            <li onclick="get_tab_and_redirect('rm_reqpage_sub=<?php echo $i; ?>')"><a class="active"?><?php echo $i; ?></a></li>
                        <?php
                        endif;
                    endfor;
                    if ($data->total_pages_sub > $max_pages_without_abb):
                        if ($data->curr_page_sub < $data->total_pages_sub - $max_visible_pages_near_current_page):
                            ?>
                            <li><a> ... </a></li>
                            <?php
                        endif;
                    endif;
                    ?>
                    <?php
                    if ($data->curr_page_sub < $data->total_pages_sub):
                        ?>
                        <li onclick="get_tab_and_redirect('rm_reqpage_sub=<?php echo $data->curr_page_sub + 1; ?>')"><a><?php echo RM_UI_Strings::get('LABEL_NEXT'); ?></a></li>
                        <li onclick="get_tab_and_redirect('rm_reqpage_sub=<?php echo $data->total_pages_sub; ?>')"><a><?php echo RM_UI_Strings::get('LABEL_LAST'); ?></a></li>
                        <?php
                    endif;
                    ?>
                </ul>
                <?php
            endif;
        }else
            echo RM_UI_Strings::get('MSG_NO_SUBMISSION_FRONT');
        ?>
    </div>
    <?php
    if ($data->payments):
        ?>
        <div class="rmagic-table" id="rm_my_pay_tab">


            <table class="rm-table">
                <tr>
                    <th><?php echo RM_UI_Strings::get('LABEL_DATE'); ?></th>
                    <th><?php echo RM_UI_Strings::get('LABEL_FORM'); ?></th>
                    <th><?php echo RM_UI_Strings::get('LABEL_AMOUNT'); ?></th>
                    <th><?php echo RM_UI_Strings::get('LABEL_TXN_ID'); ?></th>
                    <th><?php echo RM_UI_Strings::get('LABEL_STATUS'); ?></th>
                </tr>
                <?php
                for ($i = $data->offset_pay; $i < $data->end_offset_this_page; $i++):
                    ?>
                    <tr>
                        <td><?php echo RM_Utilities::localize_time($data->payments[$i]->posted_date, $data->date_format); ?></td>
                        <td><a href="?submission_id=<?php echo $data->payments[$i]->submission_id; ?>"><?php echo $data->form_names[$data->payments[$i]->submission_id]; ?></a></td>
                        <td><?php echo $data->payments[$i]->total_amount; ?></td>
                        <td><?php echo $data->payments[$i]->txn_id; ?></td>
                        <td><?php echo $data->payments[$i]->status; ?></td>
                    </tr>
                    <?php
                endfor;
                ?>
            </table>

            <?php
            /*             * ********** Pagination Logic ************** */
            $max_pages_without_abb = 10;
            $max_visible_pages_near_current_page = 3; //This many pages will be shown on both sides of current page number.

            if ($data->total_pages_pay > 1):
                ?>
                <ul class="rmpagination">
                    <?php
                    if ($data->curr_page_pay > 1):
                        ?>
                        <li onclick="get_tab_and_redirect('rm_reqpage_pay=1')"><a><?php echo RM_UI_Strings::get('LABEL_FIRST'); ?></a></li>
                        <li onclick="get_tab_and_redirect('rm_reqpage_pay=<?php echo $data->curr_page_pay - 1; ?>')"><a><?php echo RM_UI_Strings::get('LABEL_PREVIOUS'); ?></a></li>
                        <?php
                    endif;
                    if ($data->total_pages_pay > $max_pages_without_abb):
                        if ($data->curr_page_pay > $max_visible_pages_near_current_page + 1):
                            ?>
                            <li><a> ... </a></li>
                            <?php
                            $first_visible_page = $data->curr_page_pay - $max_visible_pages_near_current_page;
                        else:
                            $first_visible_page = 1;
                        endif;

                        if ($data->curr_page_pay < $data->total_pages_pay - $max_visible_pages_near_current_page):
                            $last_visible_page = $data->curr_page_pay + $max_visible_pages_near_current_page;
                        else:
                            $last_visible_page = $data->total_pages_pay;
                        endif;
                    else:
                        $first_visible_page = 1;
                        $last_visible_page = $data->total_pages_pay;
                    endif;
                    for ($i = $first_visible_page; $i <= $last_visible_page; $i++):
                        if ($i != $data->curr_page_pay):
                            ?>
                            <li onclick="get_tab_and_redirect('rm_reqpage_pay=<?php echo $i; ?>')"><a><?php echo $i; ?></a></li>
                        <?php else:
                            ?>
                            <li onclick="get_tab_and_redirect('rm_reqpage_pay=<?php echo $i; ?>')"><a class="active"><?php echo $i; ?></a></li>
                            <?php
                            endif;
                        endfor;
                        if ($data->total_pages_pay > $max_pages_without_abb):
                            if ($data->curr_page_pay < $data->total_pages_pay - $max_visible_pages_near_current_page):
                                ?>
                            <li><a> ... </a></li>
                            <?php
                        endif;
                    endif;
                    ?>
                    <?php
                    if ($data->curr_page_pay < $data->total_pages_pay):
                        ?>
                        <li onclick="get_tab_and_redirect('rm_reqpage_pay=<?php echo $data->curr_page_pay + 1; ?>')"><a><?php echo RM_UI_Strings::get('LABEL_NEXT'); ?></a></li>
                        <li onclick="get_tab_and_redirect('rm_reqpage_pay=<?php echo $data->total_pages_pay; ?>')"><a><?php echo RM_UI_Strings::get('LABEL_LAST'); ?></a></li>
                        <?php
                    endif;
                    ?>
                </ul>
            <?php endif; ?>

            <!-- 
                    <ul class="rmpagination">
                        <li><a href="#">«</a></li>
                        <li><a href="#">1</a></li>
                        <li><a class="active" href="#">2</a></li>
                        <li><a href="#">3</a></li>
                        <li><a href="#">4</a></li>
                        <li><a href="#">5</a></li>
                        <li><a href="#">6</a></li>
                        <li><a href="#">7</a></li>
                        <li><a href="#">»</a></li>
                    </ul>
            -->
            <!-- Pagination Ends    -->


        </div>   

        <?php
    endif;
    ?>

</div>
