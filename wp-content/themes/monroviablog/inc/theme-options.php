<?php

/**
 * The theme options page in wp-admin
 */
class monroviablog_theme_options_page {

    public static function init() {

        // Add menu
        add_action('admin_menu', array('monroviablog_theme_options_page', 'add_monroviablog_options_page'));

        // Initialize default options
        $def_theme_options = array();

        // Social buttons
        $def_theme_options['social_facebook'] = '';
        $def_theme_options['social_googleplus'] = '';
        $def_theme_options['social_twitter'] = '';
        $def_theme_options['social_pinterest'] = '';
        $def_theme_options['social_tumblr'] = '';

        //custom
        $def_theme_options['logo_main'] = '';
        $def_theme_options['favicon'] = '';
        $def_theme_options['year_blog_start'] = '';
        
        $def_theme_options['icon_label'] = '';

        //Feature Plant Mapping
        $def_theme_options['icon_mapper'] = '';

        global $monroviablog_theme_options;

        // This part is for theme updates, to ensure options are stored
        // properly in wp-config.
        // If there are options already, check if there are new options or
        // if any option has been deleted in a theme update, updating
        // the options array without loosing the current configuration
        if (!empty($monroviablog_theme_options) && is_array($monroviablog_theme_options) && count($monroviablog_theme_options) > 1) {

            $cur_size = count($monroviablog_theme_options);
            $def_size = count($def_theme_options);
            if ($def_size != $cur_size) {
                // Check for new options
                foreach ($def_theme_options as $def_key => $def_value) :
                    if (!isset($monroviablog_theme_options[$def_key])) {
                        $monroviablog_theme_options[$def_key] = $def_value;
                    }
                endforeach;

                // Check for deleted options
                foreach ($monroviablog_theme_options as $cur_key => $cur_value) :
                    if (!isset($def_theme_options[$cur_key])) {
                        unset($monroviablog_theme_options[$cur_key]);
                    }
                endforeach;

                delete_option('monroviablog_theme_options');
                add_option('monroviablog_theme_options', $monroviablog_theme_options);
            }
        } else {
            // Update options with defaults
            $monroviablog_theme_options = $def_theme_options;
            // Add default options to db
            add_option('monroviablog_theme_options', $def_theme_options);
        }
    }

    public static function add_monroviablog_options_page() {
        add_theme_page(__('Theme Options', 'monroviablog'), __('Theme Options', 'monroviablog'), 'edit_theme_options', 'theme-options-page', array('monroviablog_theme_options_page', 'page'));
    }

    public static function page() {

        // Get options
        global $monroviablog_theme_options;

        // SAVE OPTIONS
        if (isset($_POST['submit'])) :
            // Check referer
            check_admin_referer('monroviablog_theme_options_page');
            //$monroviablog_theme_options['logo_main'] = $_POST['logo_main'];
            //$monroviablog_theme_options['icon_mapper'] = $_POST['icon_mapper'];
            // Obtain all $_POST values and make sure unchecked options are
            // saved with a 0 value.
            foreach ($monroviablog_theme_options as $k => $v) :
                if (!empty($_POST[$k])) :
                    if ($k == 'icon_label') {
                        
                        if (!empty($_POST[$k]['item'])) {
                            $_items = $_POST[$k]['item'];
                            if (!empty($_POST[$k]['delete'])) {
                                foreach ($_POST[$k]['delete'] as $_id) {
                                    unset($_items[$_id]);
                                }
                            }
                            $_items = array_filter($_items);
                            foreach ($_items as $index => $item) {
                                if (empty($item['image'])) {
                                    unset($_items[$index]);
                                }
                            }
                            $monroviablog_theme_options[$k] = $_items;
                        } else {
                            $monroviablog_theme_options[$k] = '';
                        }
                    } else {
                        $monroviablog_theme_options[$k] = wp_kses($_POST[$k], array());
                    }
                else :
                    $monroviablog_theme_options[$k] = '';
                endif;

            endforeach;

            // Update
            $ret = update_option('monroviablog_theme_options', $monroviablog_theme_options);
            if ($ret)
                $updated = 1;

        endif;
        ?>
        <?php
        // jQuery
        wp_enqueue_script('jquery');
        // This will enqueue the Media Uploader script
        wp_enqueue_media();
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $(document ).on('click', '.upload-btn', function (e) {
                    e.preventDefault();
                    var id = $(this).attr('id-input');
                    var image = wp.media({
                        title: 'Upload Image',
                        // mutiple: true if you want to upload multiple files at once
                        multiple: false
                    }).open()
                        .on('select', function (e) {
                            // This will return the selected image from the Media Uploader, the result is an object
                            var uploaded_image = image.state().get('selection').first();
                            // We convert uploaded_image to a JSON object to make accessing it easier
                            // Output to the console uploaded_image
                            var image_url = uploaded_image.toJSON().url;
                            // Let's assign the url value to the input field
                            $('#' + id).val(image_url);
                            if ($('#' + id + '_img').length>0) {
                                $('#' + id + '_img').attr('src',image_url);
                            }
                            
                        });
                });
            });
        </script>
        <div class="wrap">

            <?php screen_icon(); ?>

            <h2>
                <?php printf(__('%s Theme Options', 'monroviablog'), wp_get_theme()); ?>
            </h2>

            <?php settings_errors(); ?>

            <br />

            <form method="post" action="">

                <h3 class="title"><?php _e('General options', 'monroviablog'); ?></h3>

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label for="logo_main"><?php _e('Main Logo', 'monroviablog'); ?></label></th>
                            <td>
                                <input name="logo_main" type="text" id="logo_main" value="<?php if (!empty($monroviablog_theme_options['logo_main'])) echo $monroviablog_theme_options['logo_main']; ?>" class="regular-text">
                                <input type="button" name="upload-btn" id-input="logo_main" class="upload-btn button-secondary" value="Upload Image">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="favicon"><?php _e('Favicon', 'monroviablog'); ?></label></th>
                            <td>
                                <input name="favicon" type="text" id="favicon" value="<?php if (!empty($monroviablog_theme_options['favicon'])) echo $monroviablog_theme_options['favicon']; ?>" class="regular-text">
                                <input type="button" name="upload-btn" id-input="favicon" class="upload-btn button-secondary" value="Upload Image">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="year_blog_start"><?php _e('Year Start', 'monroviablog'); ?></label></th>
                            <td>
                                <input name="year_blog_start" type="text" id="year_blog_start" value="<?php if (!empty($monroviablog_theme_options['year_blog_start'])) echo $monroviablog_theme_options['year_blog_start']; ?>" class="regular-text">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <br />

                <h3 class="title"><?php _e('Social URLs', 'monroviablog'); ?></h3>

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label for="social_facebook">Facebook</label></th>
                            <td><input name="social_facebook" type="text" id="social_facebook" value="<?php if (!empty($monroviablog_theme_options['social_facebook'])) echo $monroviablog_theme_options['social_facebook']; ?>" class="regular-text" placeholder="http://"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="social_googleplus">Google+</label></th>
                            <td><input name="social_googleplus" type="text" id="social_googleplus" value="<?php if (!empty($monroviablog_theme_options['social_googleplus'])) echo $monroviablog_theme_options['social_googleplus']; ?>" class="regular-text" placeholder="http://"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="social_twitter">Twitter</label></th>
                            <td><input name="social_twitter" type="text" id="social_twitter" value="<?php if (!empty($monroviablog_theme_options['social_twitter'])) echo $monroviablog_theme_options['social_twitter']; ?>" class="regular-text" placeholder="http://"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="social_pinterest">Pinterest</label></th>
                            <td><input name="social_pinterest" type="text" id="social_pinterest" value="<?php if (!empty($monroviablog_theme_options['social_pinterest'])) echo $monroviablog_theme_options['social_pinterest']; ?>" class="regular-text" placeholder="http://"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="social_tumblr">Tumblr</label></th>
                            <td><input name="social_tumblr" type="text" id="social_tumblr" value="<?php if (!empty($monroviablog_theme_options['social_tumblr'])) echo $monroviablog_theme_options['social_tumblr']; ?>" class="regular-text" placeholder="http://"></td>
                        </tr>
                    </tbody>
                </table>

                <br />

                <h3 class="title"><?php _e('Feature Plant Mapping', 'monroviablog'); ?></h3>

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label for="icon_mapper">Icon Mapper (required size 61px x 61px)</label></th>
                            <td><input name="icon_mapper" type="text" id="icon_mapper" value="<?php if (!empty($monroviablog_theme_options['icon_mapper'])) echo $monroviablog_theme_options['icon_mapper']; ?>" class="regular-text" >
                                <input type="button" name="upload-btn" id-input="icon_mapper" class="upload-btn button-secondary" value="Upload Image"></td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="title"><?php _e('Icon label', 'monroviablog'); ?></h3>

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label>Icon label (required size 16px x 16px)</label></th>
                            <td>
                                <table id="icon-label-box" style="width: 100%">
                                    <tr>
                                        <th style="width: 50px">Preview</th>
                                        <th>Icon link</th>
                                        <th>Icon label (default)</th>
                                        <th style="width: 100px"><input id="btn-add-new" type="button" class="button-secondary" value="Add new"/></th>
                                    </tr>
                                    <?php
                                        $_index = 1;
                                        if (!empty($monroviablog_theme_options['icon_label'])) {
                                            $_arr_icon_label = $monroviablog_theme_options['icon_label'];
                                            if (is_array($_arr_icon_label)) {
                                                foreach ($_arr_icon_label as $key => $value) {
                                    ?>
                                    <tr>
                                        <td><img id="icon_label_<?php echo $key ?>_img" width="16" height="16" src="<?php echo $value['image'] ?>" /></td>
                                        <td>
                                            <input name="icon_label[item][<?php echo $key ?>][image]" type="hidden" id="icon_label_<?php echo $key ?>" value="<?php echo $value['image'] ?>" class="regular-text">
                                            <a target="_bank" href="<?php echo $value ?>"><?php echo $value['image'] ?></a>
                                        </td>
                                        <td>
                                            <input name="icon_label[item][<?php echo $key ?>][label]" type="text" id="icon_label_label_<?php echo $key ?>" value="<?php echo (isset($value['label'])) ? $value['label'] : ''; ?>" class="regular-text">
                                        </td>
                                        <td>
                                            <input class="delete" type="hidden" value=""/>
                                            <input type="button" id="delete" index="<?php echo $key ?>" class="button-secondary" value="Delete">
                                        </td>
                                    </tr>
                                    <?php
                                                }
                                                $max = max(array_keys($_arr_icon_label));
                                                $_index = $max+1;
                                            }
                                        }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary menu-save" id="submit" name="submit"><i class="icon-save"></i> <?php _e('Save Changes', 'monroviablog'); ?></button>
                </p>

                <?php wp_nonce_field('monroviablog_theme_options_page') ?>

            </form>
            
            <script>
                jQuery( document ).ready(function($) {
                    var template = '<tr>';
                    template += '<td><img id="icon_label_{{index}}_img" width="16" height="16" src="" /></td>';
                    template += '<td>';
                    template += '<input name="icon_label[item][{{index}}][image]" type="hidden" id="icon_label_{{index}}" value="" class="regular-text" >';
                    template += '<input type="button" name="upload-btn" id-input="icon_label_{{index}}" class="upload-btn button-secondary" value="Upload">';
                    template += '</td>';
                    template += '<td>';
                    template += '<input name="icon_label[item][{{index}}][label]" type="text" id="icon_label_label_{{index}}" value="" class="regular-text">';
                    template += '</td>';
                    template += '<td>';
                    template += '<input class="delete" type="hidden" value=""/>';
                    template += '<input type="button" id="delete" index="{{index}}" class="button-secondary" value="Delete">';
                    template += '</td>';
                    template += '</tr>';
                    var index = <?php echo $_index; ?>;
                    function addItem() {
                        var temp = '';
                        temp = template.replace(/{{index}}/g, index);
                        $('#icon-label-box > tbody:last-child').append(temp);
                        index++;
                    }
                    
                    $('#btn-add-new').click(function(){
                        addItem();
                    });
                    
                    $(document).on('click', '#delete', function(){
                        var index = $(this).attr('index');
                        $(this).closest('tr').hide();
                        $(this).closest('td').find('.delete').attr('name','icon_label[delete][]').val(index);
                    });
                });
            </script>

        </div><!-- .wrap -->

        <?php
    }

}

add_action('init', array('monroviablog_theme_options_page', 'init'));
