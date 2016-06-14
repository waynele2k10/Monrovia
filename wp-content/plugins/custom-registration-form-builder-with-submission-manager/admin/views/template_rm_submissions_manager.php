
<div class="rmagic">
    <?php
    ?>
    <!-----Operations bar Starts-->

    <div class="operationsbar">
        <div class="rmtitle"><?php echo RM_UI_Strings::get("TITLE_SUBMISSION_MANAGER"); ?></div>
        <div class="icons">
            <a href="?page=rm_options_manage"><img alt="" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/global-settings.png'; ?>"></a>

        </div>
        <div class="nav">
            <ul>
                <li><a class="rm_deactivated" href="javascript:void(0)"><?php echo RM_UI_Strings::get("LABEL_EXPORT_ALL"); ?></a></li>

                <li onclick="jQuery.rm_do_action('rm_submission_manager_form', 'rm_submission_remove')"><a href="javascript:void(0)"><?php echo RM_UI_Strings::get("LABEL_DELETE"); ?></a></li>

                <li class="rm-form-toggle"><?php
                    if (count($data->forms) !== 0)
                    {
                        echo RM_UI_Strings::get('LABEL_DISPLAYING_FOR');
                        ?>
                        <select id="rm_form_dropdown" name="form_id" onchange = "rm_load_page(this, 'submission_manage')">
                            <?php
                            foreach ($data->forms as $form_id => $form)
                                if ($data->form_id == $form_id)
                                    echo "<option value=$form_id selected>$form</option>";
                                else
                                    echo "<option value=$form_id>$form</option>";
                            ?>
                        </select>
                        <?php
                    }
                    ?>
                </li>
            </ul>
        </div>

    </div>
    <!--  Operations bar Ends----->


    <!-------Content area Starts----->

    <?php
    if (count($data->forms) === 0)
    {
        ?><div class="rmnotice-container">
            <div class="rmnotice">
        <?php echo RM_UI_Strings::get('MSG_NO_FORM_SUB_MAN'); ?>
            </div>
        </div><?php
    } elseif ($data->submissions || $data->interval != 'all' || $data->searched)
    {
        ?>
        <div class="rmagic-table">


            <!----Sidebar---->
            <!--
                    <div class="sidebar">
                        <div class="sb-filter">Search
                            <form class="sb-search-form" method='post' action=''>
                                <input type='hidden' name='rm_slug' value='rm_user_search'>
                                <input type="text" class="sb-search" name="rm_to_search">
                            </form>
                        </div>
            -->
            <!--div class="sb-search-keyword">David x</div-->

            <div class="sidebar">
                <div class="sb-filter">
    <?php echo RM_UI_Strings::get("LABEL_TIME"); ?>
                    <div class="filter-row"><input type="radio" onclick='rm_load_page_multiple_vars(this, "submission_manage", "interval",<?php echo json_encode(array('form_id' => $data->form_id)); ?>)' name="filter_between" value="all"   <?php if ($data->interval == "all") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_ALL"); ?> </div>
                    <div class="filter-row"><input type="radio" onclick='rm_load_page_multiple_vars(this, "submission_manage", "interval",<?php echo json_encode(array('form_id' => $data->form_id)); ?>)' name="filter_between" value="today" <?php if ($data->interval == "today") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_TODAY"); ?> </div>
                    <div class="filter-row"><input type="radio" onclick='rm_load_page_multiple_vars(this, "submission_manage", "interval",<?php echo json_encode(array('form_id' => $data->form_id)); ?>)' name="filter_between" value="week"  <?php if ($data->interval == "week") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_THIS_WEEK"); ?></div>
                    <div class="filter-row"><input type="radio" onclick='rm_load_page_multiple_vars(this, "submission_manage", "interval",<?php echo json_encode(array('form_id' => $data->form_id)); ?>)' name="filter_between" value="month" <?php if ($data->interval == "month") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_THIS_MONTH"); ?></div>
                    <div class="filter-row"><input type="radio" onclick='rm_load_page_multiple_vars(this, "submission_manage", "interval",<?php echo json_encode(array('form_id' => $data->form_id)); ?>)' name="filter_between" value="year"  <?php if ($data->interval == "year") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_THIS_YEAR"); ?></div>

                </div>

                <div class="sb-filter">
    <?php echo RM_UI_Strings::get("LABEL_MATCH_FIELD"); ?>
                    <form action="" method="post">
                        <div class="filter-row">
                            <select name="rm_field_to_search">
                                <?php
                                foreach ($data->fields as $f)
                                {
                                    if ($f->field_type !== 'File' && $f->field_type !== 'HTMLH' && $f->field_type !== 'HTMLP')
                                    {
                                        ?>
                                        <option value="<?php echo $f->field_id; ?>" <?php if ($data->to_search->id === $f->field_id) echo "selected"; ?>><?php echo $f->field_label; ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="filter-row"><input type="text" name="rm_value_to_serach" class="sb-search" value="<?php echo $data->to_search->value; ?>"></div>
                        <div class="filter-row"><input type="submit" name="submit" value="Search"></div>
                    </form>
                </div>


            </div>

            <!--*******Side Bar Ends*********-->

            <form method="post" action="" name="rm_submission_manage" id="rm_submission_manager_form">
                <input type="hidden" name="rm_slug" value="" id="rm_slug_input_field">
                <table>
                    <?php if ($data->submissions)
                    {
                        ?>
                        <tr>
                            <th>&nbsp;</th>

                            <?php
                            //echo "<pre>";var_dump($data->submissions);die();


                            $field_names = array();
                            $i = $j = 0;

                            for ($i = 0; $j < 4; $i++):
                                if ((isset($data->fields[$i]->field_type) && $data->fields[$i]->field_type != 'File' && $data->fields[$i]->field_type !== 'HTMLH' && $data->fields[$i]->field_type !== 'HTMLP' ) || !isset($data->fields[$i]->field_type))
                                {

                                    $label = isset($data->fields[$i]->field_label) ? $data->fields[$i]->field_label : null;
                                    ?><th><?php echo $label; ?></th>

                                    <?php
                                    $field_names[$j] = isset($data->fields[$i]->field_id) ? $data->fields[$i]->field_id : null;
                                    $j++;
                                }

                            endfor;
                            ?>

                            <th><?php echo RM_UI_Strings::get("ACTION"); ?></th></tr>

                        <?php
                        if (is_array($data->submissions) || is_object($data->submissions))
                            foreach ($data->submissions as $submission):

                                $submission->data_us = RM_Utilities::strip_slash_array(maybe_unserialize($submission->data));
                                ?>
                                <tr>
                                    <td><input class="rm_checkbox_group" type="checkbox" value="<?php echo $submission->submission_id; ?>" name="rm_selected[]"></td>

                                    <?php
                                    for ($i = 0; $i < 4; $i++):

                                        $value = null;

                                        foreach ($submission->data_us as $key => $sub_data)
                                            if ($key == $field_names[$i])
                                                $value = $sub_data->value;
                                        ?>

                                        <td><?php
                                            if (is_array($value))
                                                $value = implode(', ', $value);

                                            echo $value;
                                            ?></td>

                                        <?php
                                    endfor;
                                    ?>
                                    <td><a href="?page=rm_submission_view&rm_submission_id=<?php echo $submission->submission_id; ?>"><?php echo RM_UI_Strings::get("VIEW"); ?></a></td>
                                </tr>

                                <?php
                            endforeach;
                        ?>
                    <?php
                    }elseif ($data->searched)
                    {
                        ?>
                        <tr><td>
                        <?php echo RM_UI_Strings::get('MSG_NO_SUBMISSION_MATCHED'); ?>
                            </td></tr>
                            <?php } else
                            {
                                ?>
                        <tr><td>
        <?php echo RM_UI_Strings::get('MSG_NO_SUBMISSION_SUB_MAN_INTERVAL'); ?>
                            </td></tr>
        <?php }
        ?>
                </table>
            </form>
        </div>
        <?php
        /*         * ********** Pagination Logic ************** */
        $max_pages_without_abb = 10;
        $max_visible_pages_near_current_page = 3; //This many pages will be shown on both sides of current page number.

        if ($data->total_pages > 1):
            ?>
            <ul class="rmpagination">
                <?php
                if ($data->curr_page > 1):
                    ?>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->form_id; ?>&rm_interval=<?php echo $data->interval; ?>&rm_field_to_search=<?php echo $data->to_search->id; ?>&rm_value_to_serach=<?php echo $data->to_search->value; ?>&rm_reqpage=1"><?php echo RM_UI_Strings::get('LABEL_FIRST'); ?></a></li>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->form_id; ?>&rm_interval=<?php echo $data->interval; ?>&rm_field_to_search=<?php echo $data->to_search->id; ?>&rm_value_to_serach=<?php echo $data->to_search->value; ?>&rm_reqpage=<?php echo $data->curr_page - 1; ?>"><?php echo RM_UI_Strings::get('LABEL_PREVIOUS'); ?></a></li>
                    <?php
                endif;
                if ($data->total_pages > $max_pages_without_abb):
                    if ($data->curr_page > $max_visible_pages_near_current_page + 1):
                        ?>
                        <li><a> ... </a></li>
                        <?php
                        $first_visible_page = $data->curr_page - $max_visible_pages_near_current_page;
                    else:
                        $first_visible_page = 1;
                    endif;

                    if ($data->curr_page < $data->total_pages - $max_visible_pages_near_current_page):
                        $last_visible_page = $data->curr_page + $max_visible_pages_near_current_page;
                    else:
                        $last_visible_page = $data->total_pages;
                    endif;
                else:
                    $first_visible_page = 1;
                    $last_visible_page = $data->total_pages;
                endif;
                for ($i = $first_visible_page; $i <= $last_visible_page; $i++):
                    if ($i != $data->curr_page):
                        ?>
                        <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->form_id; ?>&rm_interval=<?php echo $data->interval; ?>&rm_field_to_search=<?php echo $data->to_search->id; ?>&rm_value_to_serach=<?php echo $data->to_search->value; ?>&rm_reqpage=<?php echo $i; ?>"><?php echo $i; ?></a></li> 
                    <?php else:
                        ?>
                        <li><a class="active" href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->form_id; ?>&rm_interval=<?php echo $data->interval; ?>&rm_field_to_search=<?php echo $data->to_search->id; ?>&rm_value_to_serach=<?php echo $data->to_search->value; ?>&rm_reqpage=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php
                    endif;
                endfor;
                if ($data->total_pages > $max_pages_without_abb):
                    if ($data->curr_page < $data->total_pages - $max_visible_pages_near_current_page):
                        ?>
                        <li><a> ... </a></li>
                        <?php
                    endif;
                endif;
                ?>
                <?php
                if ($data->curr_page < $data->total_pages):
                    ?>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->form_id; ?>&rm_interval=<?php echo $data->interval; ?>&rm_field_to_search=<?php echo $data->to_search->id; ?>&rm_value_to_serach=<?php echo $data->to_search->value; ?>&rm_reqpage=<?php echo $data->curr_page + 1; ?>"><?php echo RM_UI_Strings::get('LABEL_NEXT'); ?></a></li>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->form_id; ?>&rm_interval=<?php echo $data->interval; ?>&rm_field_to_search=<?php echo $data->to_search->id; ?>&rm_value_to_serach=<?php echo $data->to_search->value; ?>&rm_reqpage=<?php echo $data->total_pages; ?>"><?php echo RM_UI_Strings::get('LABEL_LAST'); ?></a></li>
                <?php
            endif;
            ?>
            </ul>
            <?php
        endif;
    }else
    {
        ?><div class="rmnotice-container">
            <div class="rmnotice">
        <?php echo RM_UI_Strings::get('MSG_NO_SUBMISSION_SUB_MAN'); ?>
            </div>
        </div>
    <?php
}
?>
    <div class="rm-upgrade-note">
        <div class="rm-upgrade-note-title"><?php echo RM_UI_Strings::get('LABEL_WANT_MORE'); ?></div>
        <div class="rm-upgrade-note-content"><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/blue-cog.png'; ?>"><?php echo RM_UI_Strings::get('MSG_BUY_PRO_SUB_MAN'); ?>
            <span class="rm-upgrade-note-emphasis">
                <a href="http://registrationmagic.com/?download_id=317&amp;edd_action=add_to_cart" target="_blank"><?php echo RM_UI_Strings::get('LABEL_CLICK_HERE'); ?></a>
            </span><?php echo RM_UI_Strings::get('LABEL_TO_UPGRADE'); ?></div>

    </div>

</div>







