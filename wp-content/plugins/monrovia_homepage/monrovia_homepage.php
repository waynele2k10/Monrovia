<?php
/*
  Plugin Name: Monrovia Homepage Setting
  Plugin URI:  www.monrovia.com
  Description: Setting featured post
  Version:     1.0
  Author:      Waynele
  Author URI:  http://www.monrovia.com
 */

add_action('admin_menu', 'monrovia_homepage_admin_menu');

function monrovia_homepage_admin_menu() {
    add_submenu_page(
            'edit.php?post_type=homepage', 'Featured Post Settings', 'Featured Posts', 'manage_options', 'inc/monrovia-homepage-admin-featured-page.php', 'monrovia_homepage_admin_sub_page'
    );
}

function monrovia_homepage_admin_sub_page() {
    wp_register_style('monrovia_homepage', plugins_url('css/monrovia_homepage.css', __FILE__));
    wp_enqueue_style('monrovia_homepage');
    wp_enqueue_script('monrovia_homepage', plugin_dir_url(__FILE__) . 'js/monrovia_homepage.js', array('jquery'));
    wp_enqueue_script('jquery.simplePopup', plugin_dir_url(__FILE__) . 'js/jquery.simplePopup.js', array('jquery'));
    wp_enqueue_script('moment.min', plugin_dir_url(__FILE__) . 'js/moment.min.js', array('jquery'));
    wp_enqueue_script('combodate', plugin_dir_url(__FILE__) . 'js/combodate.js', array('jquery'));
    if (isset($_POST)) {
        if (isset($_POST['featured']['delete'])) {
            foreach ($_POST['featured']['delete'] as $key => $value) {
                $position = $value['position'];
                $date = $value['date'];
                delete_post_meta($key, 'monrovia_homepage_featured_position');
            }
        }
        // Read their posted value
        if (isset($_POST['featured']['item'])) {
            foreach ($_POST['featured']['item'] as $key => $value) {
                
                $position = $value['position'];
                $date = $value['date'];
                
                // Check data to set schedule post
                if ($date > current_time('Y-m-d H:i')) {
                    // update post date
                    $my_post = array(
                        'ID' => $key,
                        'post_date' => $date
                    );
                    wp_update_post($my_post);

                    // Set schedule post
                    global $wpdb;

                    if (!$post = get_post($key))
                        return;

                    $wpdb->update($wpdb->posts, array('post_status' => 'future'), array('ID' => $post->ID));

                    clean_post_cache($post->ID);

                    $old_status = $post->post_status;
                    $post->post_status = 'future';
                    wp_transition_post_status('future', $old_status, $post);
                } else {
                    $my_post = array(
                        'ID' => $key,
                        'post_date' => $date
                    );
                    wp_update_post($my_post);
                    wp_publish_post($key);
                }

                update_post_meta($key, 'monrovia_homepage_featured_position', $position);
            }
        }
        if (isset($_POST['featured']['main'])) {
            monrovia_homepage_set_active_post('main',$_POST['featured']['main']);
        }
        if (isset($_POST['featured']['left'])) {
            monrovia_homepage_set_active_post('left',$_POST['featured']['left']);
        }
        if (isset($_POST['featured']['center'])) {
            monrovia_homepage_set_active_post('center',$_POST['featured']['center']);
        }
        if (isset($_POST['featured']['right'])) {
            monrovia_homepage_set_active_post('right',$_POST['featured']['right']);
        }
    }
    ?>
    <div class="wrap">
        <h2>Featured Post Settings</h2>
        <form id="nav-menu-meta" class="nav-menu-meta" method="post" action="">
            <div id="nav-menus-frame" class="wp-clearfix">
                <div id="menu-settings-column" class="metabox-holder">
                    <div id="side-sortables" class="accordion-container">
                        <ul class="outer-border">
                            <li class="control-section accordion-section  add-post-type-page open" id="add-post-type-page">
                                <h3 class="hndle" tabindex="0">
                                    Posts					
                                </h3>
                                <div class="accordion-section-content " style="display: block;">
                                    <div class="inside">
                                        <div class="tabs-panel tabs-panel-active" id="tabs-panel-posttype-post-search">
                                            <p class="quick-search-wrap">
                                                <label for="quick-search-posttype-post" class="">Search</label>
                                                <input type="text" class="quick-search" value="" name="q" id="quick-search-posttype-post" autocomplete="off">
                                                <span class="spinner"></span>
                                            <ul id="sortable1" data-wp-lists="list:post" class="droptrue categorychecklist form-no-clear sortable"></ul>
                                            </p>
                                        </div>
                                    </div><!-- .inside -->
                                </div>
                            </li>
                        </ul><!-- .outer-border -->
                    </div>
                </div>
                <div id="menu-management-liquid">
                    <div id="menu-management">
                        <div class="menu-edit ">
                            <div id="nav-menu-header">
                                <div class="major-publishing-actions wp-clearfix">
                                    <h3 class="hndle" tabindex="0">Position:</h3>
                                </div><!-- END .major-publishing-actions -->
                            </div><!-- END .nav-menu-header -->
                            <div id="post-body">
                                <div id="post-body-content" class="wp-clearfix">
                                    <ul id="sortable2" class="container-sortable dropfalse1 sortable" data-name="main">
                                        <li class="sortable-feature">
                                            <h3 style="margin:0">
                                                Main Feature					
                                            </h3>
                                        </li>
                                        <?php
                                        monrovia_homepage_get_post_by_position('main');
                                        ?>
                                    </ul>

                                    <ul id="sortable3" class="container-sortable dropfalse1 sortable" data-name="left">
                                        <li class="sortable-feature">
                                            <h3 style="margin:0">
                                                Left Feature				
                                            </h3>
                                        </li>
                                        <?php
                                        monrovia_homepage_get_post_by_position('left');
                                        ?>
                                    </ul>

                                    <ul id="sortable4" class="container-sortable dropfalse1 sortable" data-name="center">
                                        <li class="sortable-feature">
                                            <h3 style="margin:0">
                                                Center Feature					
                                            </h3>
                                        </li>
                                        <?php
                                        monrovia_homepage_get_post_by_position('center');
                                        ?>
                                    </ul>
                                    <ul id="sortable5" class="container-sortable dropfalse1 sortable" data-name="right">
                                        <li class="sortable-feature">
                                            <h3 style="margin:0">
                                                Right Feature					
                                            </h3>
                                        </li>
                                        <?php
                                        monrovia_homepage_get_post_by_position('right');
                                        ?>
                                    </ul>
                                </div><!-- /#post-body-content -->
                            </div><!-- /#post-body -->
                            <div id="nav-menu-footer">
                                <div class="major-publishing-actions wp-clearfix">
                                    <p>Notes:</p>
                                    <p><span class="blue">Blue</span> highlight = Active post for this position.</p>
                                    <p><span class="red">Red</span> highlight = Scheduled post for this position.</p>
                                    <p>If you want to feature a post on the homepage, drag it into the appropriate slot. The scheduled (red) ones will automatically kick in when the date hits. If you want to make a post active immediately, click its radio button in the upper right then "Save Changes".</p>
                                </div><!-- END .major-publishing-actions -->
                            </div><!-- /#nav-menu-footer -->
                        </div><!-- /.menu-edit -->
                    </div><!-- /#menu-management -->
                </div>
            </div>
            <div id="box-value" style="display:none"></div>
            <?php submit_button(); ?>
        </form>
    </div>
    <div id="monrovia-popup" style="display:none">
        <input type="text" id="datetime24" data-format="YYYY-MM-DD HH:mm" data-template="YYYY / MM / DD     HH : mm" name="datetime" value="">
        <input class="submit-date button button-primary" type="button" value="Update" onclick="submitDate()">
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function () {
            var post = new Array();
            jQuery(".sortable .feature-post").each(function (index) {
                post.push(jQuery(this).attr('data-post-id'));
            });
            jQuery('#datetime24').combodate({
                minuteStep: 1,
                maxYear: 2017
            });
            var se_ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
            ajaxGetPost('');
            jQuery("#quick-search-posttype-post").keyup(function () {
                ajaxGetPost(jQuery("#quick-search-posttype-post").val())
            });

            function ajaxGetPost(keyword) {
                jQuery('ul#sortable1').html('');
                jQuery.ajax({
                    type: "POST",
                    dataType: "html",
                    url: se_ajax_url,
                    data: {
                        action: 'se_lookup',
                        q: keyword,
                        post: post
                    },
                    beforeSend: function () {
                        jQuery('#menu-settings-column .quick-search-wrap .spinner').addClass('is-active');
                    },
                    success: function (data) {
                        jQuery('ul#sortable1').html('');
                        jQuery('#menu-settings-column .quick-search-wrap .spinner').removeClass('is-active');
                        jQuery('ul#sortable1').append(data);

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        jQuery('#menu-settings-column .quick-search-wrap .spinner').removeClass('is-active');
                    }
                });
            }
            jQuery(document).on("click", ".container-sortable li .remove-post", function (e) {
                var position = jQuery(this).parent().parent().attr('data-name');
                var id = jQuery(this).parent().attr('data-post-id');
                var date = jQuery(this).parent().attr('data-post-date');
                var input_position = '<input name="featured[delete][' + id + '][position]" type="hidden" value="' + position + '">';
                var input_date = '<input name="featured[delete][' + id + '][date]" type="hidden" value="' + date + '">';
                jQuery('#box-value').append(input_position);
                jQuery('#box-value').append(input_date);
                jQuery(this).parent().remove();
            });
        });
        function submitDate() {
            //            var id = jQuery('#datetime24').attr('data-id');
            //            var date = jQuery('#datetime24').attr('value');
            //            var position = jQuery('#datetime24').attr('data-position');
            //            var input_id = '<input class="wait-submit" name="featured[item][][id]" type="hidden" value="' + id + '">';
            //            var input_position = '<input class="wait-submit" name="featured[item][][position]" type="hidden" value="' + position + '">';
            //            var input_date = '<input class="wait-submit" name="featured[item][][date]" type="hidden" value="' + date + '">';
            //            jQuery("#" + id).append(input_id);
            //            jQuery("#" + id).append(input_position);
            //            jQuery("#" + id).append(input_date);
            jQuery('#monrovia-popup .simplePopupClose').click();
        }
    </script>
    <?php
}

add_action('wp_ajax_se_lookup', 'se_lookup');
add_action('wp_ajax_nopriv_se_lookup', 'se_lookup');

function se_lookup() {
    global $wpdb;
    $search = like_escape($_REQUEST['q']);
    if (!empty($search)) {
        $query = 'SELECT ID,post_title,post_date,post_status FROM ' . $wpdb->posts . '
        WHERE ID NOT IN (\''.implode($_REQUEST["post"], '\',\'').'\') AND post_title LIKE \'' . $search . '%\'
        AND post_type = \'post\'
        AND post_status IN(\'publish\',\'future\')  
        ORDER BY post_title ASC';
    } else {
        $query = 'SELECT ID,post_title,post_date,post_status FROM ' . $wpdb->posts . '
        WHERE ID NOT IN (\''.implode($_REQUEST["post"], '\',\'').'\') AND post_type = \'post\'
        AND post_status IN(\'publish\',\'future\')  
        ORDER BY post_date DESC LIMIT 10';
    }

    if (count($wpdb->get_results($query)) > 0) {
        foreach ($wpdb->get_results($query) as $row) {
            $post_title = $row->post_title;
            $id = $row->ID;
            $post_date = $row->post_date;
            $post_status = $row->post_status;
            if ($post_status == "future") {
                echo '<li class="future" id="' . $id . '" data-post-status="' . $post_status . '" data-post-id="' . $id . '" data-post-date="' . stripslashes($post_date) . '">' . $post_title . '<span class="post_date">' . $post_date . '</span></li>';
            } else {
                echo '<li class="publish" id="' . $id . '" data-post-status="' . $post_status . '" data-post-id="' . $id . '" data-post-date="' . stripslashes($post_date) . '">' . $post_title . '<span class="post_date">' . $post_date . '</span></li>';
            }
        }
    } else {
        echo 'No results found.';
    }

    die();
}

function monrovia_homepage_get_post_by_position($position) {
    $args = array(
        'orderby' => 'date',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => 'monrovia_homepage_featured_position',
                'value' => $position,
                'compare' => '=',
            ),
        ),
    );
    $the_query = new WP_Query($args);

// The Loop
    if ($the_query->have_posts()) {
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $post_title = get_the_title();
            $id = get_the_ID();
            $post_date = get_the_date('Y-m-d H:i');
            $post_status = get_post_status();
            if ($post_status == "future") {
                echo '<li class="future feature-post" id="' . $id . '" data-post-status="' . $post_status . '" data-post-id="' . $id . '" data-post-date="' . stripslashes($post_date) . '">' . $post_title . '<span class="remove-post">X</span><span class="post_date">' . $post_date . '</span></li>';
            } else {
                echo '<li class="publish feature-post" id="' . $id . '" data-post-status="' . $post_status . '" data-post-id="' . $id . '" data-post-date="' . stripslashes($post_date) . '">' . $post_title . '<span class="remove-post">X</span><span class="mark-active"><input type="radio" class="mark-active" name="featured['.$position.']" value="'.$id.'"></span><span class="post_date">' . $post_date . '</span></li>';
            }
        }
    } else {
        // no posts found
    }
    /* Restore original Post Data */
    wp_reset_postdata();
}

function monrovia_homepage_check_active_post($position, $post_id) {
    $args = array(
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page ' => 1,
        'paged' => 1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'monrovia_homepage_featured_position',
                'value' => $position,
                'compare' => '=',
            ),
        ),
    );
    $the_query = new WP_Query($args);
    if ($the_query->have_posts()) {
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $id = get_the_ID();
            $post_date = get_the_date('Y-m-d H:i');
            if ($id == $post_id) {
                return false;
            }
            return $post_date;
        }
    }
    wp_reset_postdata();
    return false;
}

function monrovia_homepage_set_active_post($position,$post_id) {
    $post_date = monrovia_homepage_check_active_post($position,$post_id);
    if ($post_date) {
        $dateTime = new DateTime($post_date);
        $dateTime->modify('+1 minutes');
        $my_post = array(
            'ID' => $post_id,
            'post_date' => $dateTime->format('Y-m-d H:i')
        );
        wp_update_post($my_post);
        wp_publish_post($post_id);
    }
}