<?php
//echo "<br>Data:<br><pre>", var_dump($data), "</pre>";
?>

    <!-----Operationsbar Starts----->

    <div class="rmagic">
        
<!-----Operations bar Starts----->
    
    <div class="operationsbar">
        <div class="rmtitle"><?php echo RM_UI_Strings::get("TITLE_USER_MANAGER"); ?></div>
        <div class="icons">
        <a href="admin.php?page=rm_options_user"><img alt="" src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'images/rm-user-accounts.png'; ?>"></a>
        </div>
        <div class="nav">
            <ul>
                <li><a href="user-new.php"><?php echo RM_UI_Strings::get("NEW_USER"); ?></a></li>
                    <li onclick="jQuery.rm_do_action('rm_user_manager_form','rm_user_activate')"><a href="javascript:void(0)"><?php echo RM_UI_Strings::get('ACTIVATE'); ?></a></li>
                    <li onclick="jQuery.rm_do_action('rm_user_manager_form','rm_user_deactivate')"><a href="javascript:void(0)"><?php echo RM_UI_Strings::get('DEACTIVATE'); ?></a></li>
                    <li onclick="jQuery.rm_do_action('rm_user_manager_form','rm_user_delete')"><a href="javascript:void(0)"><?php echo RM_UI_Strings::get('LABEL_DELETE'); ?></a></li>
            </ul>
        </div>

    </div>
    <!--------Operationsbar Ends----->

    <!-------Contentarea Starts----->
    <div class="rmagic-table">

<!----Sidebar---->

<div class="sidebar">

<form id="rm_user_manager_sideform"  action="" method="post">

    <div class="sb-filter">Search
        <form class="sb-search-form" method='post' action=''>
            
            <input type="text" class="sb-search" name="rm_to_search" value="<?php echo $data->query_string; ?>">
    </div>
    
    <!--div class="sb-search-keyword">David x</div-->

    
<div class="sb-filter">
<?php echo RM_UI_Strings::get("LABEL_TIME"); ?>
    <div class="filter-row"><input type="radio" onclick="document.getElementById('rm_user_manager_sideform').submit()" name="rm_interval" value="all"   <?php if ($data->interval == "all") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_ALL"); ?> </div>
    <div class="filter-row"><input type="radio" onclick="document.getElementById('rm_user_manager_sideform').submit()" name="rm_interval" value="today" <?php if ($data->interval == "today") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_TODAY"); ?> </div>
    <div class="filter-row"><input type="radio" onclick="document.getElementById('rm_user_manager_sideform').submit()" name="rm_interval" value="week"  <?php if ($data->interval == "week") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_THIS_WEEK"); ?></div>
    <div class="filter-row"><input type="radio" onclick="document.getElementById('rm_user_manager_sideform').submit()" name="rm_interval" value="month" <?php if ($data->interval == "month") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_THIS_MONTH"); ?></div>
    <div class="filter-row"><input type="radio" onclick="document.getElementById('rm_user_manager_sideform').submit()" name="rm_interval" value="year"  <?php if ($data->interval == "year") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_THIS_YEAR"); ?></div>

</div>

<div class="sb-filter">
<?php echo RM_UI_Strings::get("LABEL_STATUS"); ?>
    <!-- <div class="filter-row"><input type="checkbox" onclick='document.getElementById('rm_user_manager_sideform').submit()' name="user_status[]" value="1" <?php if ($data->status == "1" || $data->status == "3") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_ACTIVE"); ?></div>
    <div class="filter-row"><input type="checkbox" onclick='document.getElementById('rm_user_manager_sideform').submit()' name="user_status[]" value="2" <?php if ($data->status == "2" || $data->status == "3") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_PENDING"); ?></div> -->
    <div class="filter-row"><input type="radio" onclick="document.getElementById('rm_user_manager_sideform').submit()" name="rm_status" value="all"     <?php if ($data->status == "all") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_ALL"); ?></div>
    <div class="filter-row"><input type="radio" onclick="document.getElementById('rm_user_manager_sideform').submit()" name="rm_status" value="active"  <?php if ($data->status == "active") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_ACTIVE"); ?></div>
    <div class="filter-row"><input type="radio" onclick="document.getElementById('rm_user_manager_sideform').submit()" name="rm_status" value="pending" <?php if ($data->status == "pending") echo "checked"; ?>><?php echo RM_UI_Strings::get("LABEL_PENDING"); ?></div>

</div>
    

<!-- <div class="sb-filter">
<?php echo RM_UI_Strings::get("LABEL_MATCH_FIELD"); ?>
    <div class="filter-row">
        <select name="field_name">
        <option value="name"><?php echo RM_UI_Strings::get("LABEL_NAME"); ?></option>
        <option value="email"><?php echo RM_UI_Strings::get("LABEL_EMAIL"); ?></option>
        </select> </div>

        
    
    

</div> -->


<!-- <ul><li><a href="?page=rm_user_manage"><?php echo RM_UI_Strings::get("LABEL_RESET"); ?></a></li></ul> -->
<div class="filter-row"><a href="?page=rm_user_manage"><input type="button" name="Reset" value="RESET"></a><input type="submit" name="Search" value="Search"></div>

    </form>
    
</div>

    <form method="post" action="" name="rm_user_manage" id="rm_user_manager_form">
                <input type="hidden" name="rm_slug" value="" id="rm_slug_input_field">
    <table>
        <tr>
            <th>&nbsp;</th>
            <th><?php echo RM_UI_Strings::get("IMAGE"); ?></th>
            <th><?php echo RM_UI_Strings::get("LABEL_NAME"); ?></th>
            <th><?php echo RM_UI_Strings::get("LABEL_EMAIL"); ?></th>
            <th><?php echo RM_UI_Strings::get("LABEL_STATUS"); ?></th>
            <th><?php echo RM_UI_Strings::get("ACTION"); ?></th>
        </tr>
        <!--********************************-->

        <?php
        if(is_array($data->users) || is_object($data->users))
               foreach($data->users as $user):  ?>
        <tr>
            <td><input class="rm_checkbox_group" type="checkbox" <?php echo get_current_user_id()==$user->ID?'disabled':''; ?> value="<?php echo $user->ID; ?>" name="rm_users[]"></td>
            <td><div class="tableimg">
                    <a href="?page=rm_user_view&user_id=<?php echo $user->ID; ?>">
                        <?php echo get_avatar( $user->ID); ?>
                    </a>

                </div></td>
               
            <td><?php echo $user->first_name; ?></td>
            <td><?php echo $user->user_email; ?></td>
            <td><?php echo $user->user_status; ?></td>
            <td><a href="?page=rm_user_view&user_id=<?php echo $user->ID; ?>"><?php echo RM_UI_Strings::get("VIEW"); ?></a></td>
        </tr>
        
        <?php
        endforeach;
        ?>

    </table>
        </form>
</div>

<!-- 
    <?php if ($data->total_pages > 1): ?>
        <ul class="rmpagination">
            <?php
            if($data->curr_page > 1):?>
                <li><a href="?page=<?php echo $data->rm_slug ?>&rm_reqpage=<?php echo $data->curr_page - 1; ?>">«</a></li>
                <?php
            endif;

            ?>
           <li class="rm_pagination_text"><?php echo $data->curr_page.' of '.$data->total_pages;?></li>

            <?php
            if($data->curr_page < $data->total_pages):?>
                <li><a href="?page=<?php echo $data->rm_slug ?>&rm_reqpage=<?php echo $data->curr_page + 1; ?>">»</a></li>
                <?php
            endif;?>
        </ul>
    <?php endif; ?>

 -->




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
                <li><a href="?page=<?php echo $data->rm_slug ?>&rm_status=<?php echo $data->status; ?>&rm_interval=<?php echo $data->interval; ?>&rm_reqpage=1"><?php echo RM_UI_Strings::get('LABEL_FIRST'); ?></a></li>
                <li><a href="?page=<?php echo $data->rm_slug ?>&rm_status=<?php echo $data->status; ?>&rm_interval=<?php echo $data->interval; ?>&rm_reqpage=<?php echo $data->curr_page - 1; ?>"><?php echo RM_UI_Strings::get('LABEL_PREVIOUS'); ?></a></li>
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
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_status=<?php echo $data->status; ?>&rm_interval=<?php echo $data->interval; ?>&rm_reqpage=<?php echo $i; ?>"><?php echo $i; ?></a></li> 
                <?php else:
                    ?>
                    <li><a class="active" href="?page=<?php echo $data->rm_slug ?>&rm_status=<?php echo $data->status; ?>&rm_interval=<?php echo $data->interval; ?>&rm_reqpage=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_status=<?php echo $data->status; ?>&rm_interval=<?php echo $data->interval; ?>&rm_reqpage=<?php echo $data->curr_page + 1; ?>"><?php echo RM_UI_Strings::get('LABEL_NEXT'); ?></a></li>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_status=<?php echo $data->status; ?>&rm_interval=<?php echo $data->interval; ?>&rm_reqpage=<?php echo $data->total_pages; ?>"><?php echo RM_UI_Strings::get('LABEL_LAST'); ?></a></li>
                <?php
            endif;
            ?>
        </ul>
        <?php endif; ?>

 </div>




