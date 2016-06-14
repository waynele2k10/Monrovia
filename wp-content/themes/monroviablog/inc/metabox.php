<?php
/* Meta box for home page */

function monroviablog_home_meta_box_markup($object) {
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
    ?>
    <div>
        <?php
        $checkbox_value = get_post_meta($object->ID, "main-feature-checkbox", true);

        if ($checkbox_value == "") {
            ?>
            <input name="main-feature-checkbox" type="checkbox" value="true">
            <?php
        } else if ($checkbox_value == "true") {
            ?>  
            <input name="main-feature-checkbox" type="checkbox" value="true" checked>
            <?php
        }
        ?>
        <label for="main-feature-checkbox">Main Feature</label><br>
        <?php
        $checkbox_value = get_post_meta($object->ID, "left-feature-checkbox", true);

        if ($checkbox_value == "") {
            ?>
            <input name="left-feature-checkbox" type="checkbox" value="true">
            <?php
        } else if ($checkbox_value == "true") {
            ?>  
            <input name="left-feature-checkbox" type="checkbox" value="true" checked>
            <?php
        }
        ?>
        <label for="left-feature-checkbox">Left Feature</label><br>
        <?php
        $checkbox_value = get_post_meta($object->ID, "center-feature-checkbox", true);

        if ($checkbox_value == "") {
            ?>
            <input name="center-feature-checkbox" type="checkbox" value="true">
            <?php
        } else if ($checkbox_value == "true") {
            ?>  
            <input name="center-feature-checkbox" type="checkbox" value="true" checked>
            <?php
        }
        ?>
        <label for="center-feature-checkbox">Center Feature</label><br>
        <?php
        $checkbox_value = get_post_meta($object->ID, "right-feature-checkbox", true);

        if ($checkbox_value == "") {
            ?>
            <input name="right-feature-checkbox" type="checkbox" value="true">
            <?php
        } else if ($checkbox_value == "true") {
            ?>  
            <input name="right-feature-checkbox" type="checkbox" value="true" checked>
            <?php
        }
        ?>
        <label for="right-feature-checkbox">Right Feature</label>
    </div>
    <?php
}

function monroviablog_home_meta_box() {
//    add_meta_box("monroviablog-home-meta-box", "Home Featured Sections", "monroviablog_home_meta_box_markup", "post", "side", "low", null);
}

//add_action("add_meta_boxes", "monroviablog_home_meta_box");

function monroviablog_home_save_meta_box($post_id, $post, $update) {
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if (!current_user_can("edit_post", $post_id))
        return $post_id;

    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    $slug = "post";
    if ($slug != $post->post_type)
        return $post_id;

    $main_checkbox_value = "";
    $left_checkbox_value = "";
    $center_checkbox_value = "";
    $right_checkbox_value = "";

    if (isset($_POST["main-feature-checkbox"])) {
        $main_checkbox_value = $_POST["main-feature-checkbox"];
        if ($post->post_status != 'future') {
            $myquery = new WP_Query("post_type=post&meta_key=main-feature-checkbox&meta_value=true");
            foreach ($myquery->posts as $feature_checkbox) {
                delete_metadata("post", $feature_checkbox->ID, "main-feature-checkbox", "true", $delete_all = false);
            }
        }
    }
    update_post_meta($post_id, "main-feature-checkbox", $main_checkbox_value);
    if (isset($_POST["left-feature-checkbox"])) {
        $left_checkbox_value = $_POST["left-feature-checkbox"];
        if ($post->post_status != 'future') {
            $myquery = new WP_Query("post_type=post&meta_key=left-feature-checkbox&meta_value=true");
            foreach ($myquery->posts as $feature_checkbox) {
                delete_metadata("post", $feature_checkbox->ID, "left-feature-checkbox", "true", $delete_all = false);
            }
        }
    }
    update_post_meta($post_id, "left-feature-checkbox", $left_checkbox_value);
    if (isset($_POST["center-feature-checkbox"])) {
        $center_checkbox_value = $_POST["center-feature-checkbox"];
        if ($post->post_status != 'future') {
            $myquery = new WP_Query("post_type=post&meta_key=center-feature-checkbox&meta_value=true");
            foreach ($myquery->posts as $feature_checkbox) {
                delete_metadata("post", $feature_checkbox->ID, "center-feature-checkbox", "true", $delete_all = false);
            }
        }
    }
    update_post_meta($post_id, "center-feature-checkbox", $center_checkbox_value);
    if (isset($_POST["right-feature-checkbox"])) {
        $right_checkbox_value = $_POST["right-feature-checkbox"];
        if ($post->post_status != 'future') {
            $myquery = new WP_Query("post_type=post&meta_key=right-feature-checkbox&meta_value=true");
            foreach ($myquery->posts as $feature_checkbox) {
                delete_metadata("post", $feature_checkbox->ID, "right-feature-checkbox", "true", $delete_all = false);
            }
        }
    }
    update_post_meta($post_id, "right-feature-checkbox", $right_checkbox_value);
}

//add_action("save_post", "monroviablog_home_save_meta_box", 10, 3);

/* Meta box for yellow label */

function monroviablog_yellow_meta_box_markup($object) {
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
    $yellow_value = get_post_meta($object->ID, "yellow_label", true);
    ?>
    <div>
        <input style="width:99%" name="txtyellow" type="text" value="<?php echo $yellow_value; ?>">
    </div>
    <?php
}

function monroviablog_yellow_meta_box() {
    add_meta_box("monroviablog-yellow-meta-box", "Yellow label", "monroviablog_yellow_meta_box_markup", array("post", "page"), "side", "low", null);
}

add_action("add_meta_boxes", "monroviablog_yellow_meta_box");

function monroviablog_yellow_save_meta_box($post_id, $post, $update) {
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if (!current_user_can("edit_post", $post_id))
        return $post_id;

    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    if ("post" != $post->post_type && "page" != $post->post_type)
        return $post_id;

    $yellow_value = "";

    if (isset($_POST["txtyellow"])) {
        $yellow_value = $_POST["txtyellow"];
        update_post_meta($post_id, "yellow_label", $yellow_value);
    }
}

add_action("save_post", "monroviablog_yellow_save_meta_box", 10, 3);

/* Meta box for icon label */

function monroviablog_icon_meta_box_markup($object) {
    global $monroviablog_theme_options;
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
    $icon_name = get_post_meta($object->ID, "icon_name", true);
    $icon_value = get_post_meta($object->ID, "icon_value", true);
    $icons = $monroviablog_theme_options['icon_label'];
    ?>
    <div style="margin-bottom: 10px;">
        <select style="width: 99%" name="slbiconvalue" id="slbiconvalue">
            <option value="" label="">Select an icon</option>
        <?php
        foreach ($icons as $k => $v) {
            $label = "";
            if (!empty($v["label"])) {
                $label = $v["label"];
            }
            if ($icon_value == $k) {
                echo '<option value="' . $k . '" label="'.$label.'" selected>' . $v["image"] . '</option>';
            } else {
                echo '<option value="' . $k . '" label="'.$label.'">' . $v["image"] . '</option>';
            }
        }
        ?>
        </select>
    </div>
    <div style="margin-bottom: 10px;">
        <label for="" style="display: block;">Use default label</label>
        <?php if (!empty($icon_value) && isset($icons[$icon_value])) { ?>
            <div style="float: left; width: 10%; padding-top: 6px;"><img id="icon-value" src="<?php echo $icons[$icon_value]['image'] ?>" width="16" height="16"/></div>
            <input style="float: left;width:89%" id="iconname" type="text" value="<?php echo $icons[$icon_value]['label'] ?>" disabled="disabled">
        <?php } else {?>
            <div style="float: left; width: 10%; padding-top: 6px;"><img id="icon-value" src="" width="16" height="16"/></div>
            <input style="float: left;width:89%" id="iconname" type="text" value="" disabled="disabled">
        <?php } ?>
        <p>(Default label can be edited in Appearance | Theme Options)</p>
    </div>
    <div>
        <label for="txticonname">or custom label</label>
        <input style="width:99%" name="txticonname" id="txticonname" type="text" value="<?php echo $icon_name; ?>">
    </div>
    <script>
        (function ($) {
            function formatState(state) {
                if (!state.id) {
                    return state.text;
                }
                var $state = $(
                        '<span><img style="margin-right: 5px; vertical-align: middle;" src="' + state.text + '" width="16" height="16"/><span>'+$(state.element).attr('label')+'</span></span>'
                        );
                return $state;
            };

            $("#slbiconvalue").select2({
                templateResult: formatState
            }).on('change', function(){
                if (this.value != '') {
                    $('#icon-value').attr('src',this.options[this.selectedIndex].innerHTML);
                } else {
                    $('#icon-value').attr('src','');
                }
                $("#iconname").val($(this.options[this.selectedIndex]).attr('label'));
            });
        })(jQuery);
    </script>
    <?php
}

function monroviablog_icon_meta_box() {
    add_meta_box("monroviablog-icon-meta-box", "Icon label", "monroviablog_icon_meta_box_markup", array("post", "page"), "side", "low", null);
}

add_action("add_meta_boxes", "monroviablog_icon_meta_box");

function monroviablog_icon_save_meta_box($post_id, $post, $update) {
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if (!current_user_can("edit_post", $post_id))
        return $post_id;

    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    if ("post" != $post->post_type && "page" != $post->post_type)
        return $post_id;

    $icon_name = "";
    $icon_value = "";

    if (isset($_POST["txticonname"])) {
        $icon_name = $_POST["txticonname"];
        update_post_meta($post_id, "icon_name", $icon_name);
    }

    if (isset($_POST["slbiconvalue"])) {
        $icon_value = $_POST["slbiconvalue"];
        update_post_meta($post_id, "icon_value", $icon_value);
    }
}

add_action("save_post", "monroviablog_icon_save_meta_box", 10, 3);
