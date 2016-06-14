<?php
/**
  Template Name: Favorites
  List of plants favorited by the current logged in User
  On this page User has ability to Remove plants, and Add/Edit/Delete
  Specific Plant Notes
 */
// Redirect if not logged in
if (!is_user_logged_in()) {
    header("Location: /community/login/");
}

global $current_user;
get_currentuserinfo();

get_header();
?>

<div class="content_wrapper clear">
    <section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
        <h1><?php the_title(); ?></h1>
        <?php if (have_posts()): while (have_posts()) : the_post(); ?>
                <!-- article -->
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <?php the_content(); ?>
                    <p class="message" style="display:none"></p>
                </article>
                <!-- /article -->
            <?php endwhile;
        endif; ?>

        <!-- Print out Favorites if availiable -->
        <?php
        $plants = getUserWishlist();
        if (!empty($plants)) {
            ?>
            <div class="plants-grid video-related clear">
                <div class="row clear">
    <?php foreach ($plants as $key => $plant) {
        $data = getPlantData($plant['pid'], '');
        ?>
                        <div class="list-plant left">
                            <div class="image-wrap">
                                <a class="delete-fav favorite-action no-print" data-action="remove" data-pid="<?php echo $plant['pid']; ?>">
                                    <i class='fa fa-times-circle fa-2' title="Delete Favorite"></i>
                                </a>
                                <a class="enable-fav favorite-action no-print" data-action="enable" data-value="<?php if($plant['enable_send_mail'] == 1) echo "0"; else echo "1" ?>" data-pid="<?php echo $plant['pid']; ?>">
                                    <i class='fa fa-envelope<?php if($plant['enable_send_mail'] == 0) echo "-o"; ?> fa-2' title="<?php if($plant['enable_send_mail'] == 1) echo "Turn off notifications"; else echo "Turn on notifications" ?>"></i>
                                </a>
                                <a href="<?php echo site_url() . '/plant-catalog/plants/' . $data['pid'] . '/' . $data['seo']; ?>">
                                    <?php if ($data['image-id'] == 'no-image') { ?>
                                        <img src="<?php echo get_template_directory_uri(); ?>/img/no-image.png" />
        <?php } else { ?>
                                        <img src="<?php echo site_url() . '/wp-content/uploads/plants/search_results/' . $data['image-id'] . '.jpg' ?>"  />
        <?php } ?>
                                </a>
                            </div><!-- end image wrap -->
                            <a href="<?php echo site_url() . '/plant-catalog/plants/' . $data['pid'] . '/' . $data['seo']; ?>" title="<?php echo $data['title']; ?>" class='no-print'><?php echo $data['title']; ?></a>
                            <div class='no-print'><?php echo $data['botanical']; ?></div>
                            <span class='no-print'>#<?php echo $data['item']; ?></span>
                            <a class="fav-note no-print"><?php if ($plant['notes'] != '') {
            echo $plant['notes'];
        } else {
            echo 'Click to add notes!';
        } ?></a>
                            <div class="edit-note no-print">
                                <textarea name="Favorite_note" value=""></textarea>
                                <a href="javascript:void(0);" title="Cancel" class="cancel-note">Cancel</a>
                                <a href="javascript:void(0);" title="Save" class="save-note favorite-action green-btn right" data-action="update" data-pid="<?php echo $plant['pid']; ?>">Save</a>
                            </div><!-- end edit note -->
                            <div class='print-this'>
                                <strong><?php echo $data['title']; ?></strong><br />
                                <em><?php echo $data['botanical']; ?></em><br />
                                <span>Monrovia Item #: </span><?php echo $data['item']; ?><br />
                                <span>Cold Zones: </span><?php echo $data['zone-low'] . " - " . $data['zone-high']; ?><br />
                                <!--<span>Light Exposure: </span><?php //echo $data['light']; ?><br /> -->
                                <span>Flower Color: </span><?php echo $data['color']; ?><br />
                                <span>Bloom Time: </span><?php echo $data['time']; ?><br />
        <?php if ($plant['notes'] != '') { ?><span>Your Notes: </span><?php echo $plant['notes'];
        } ?>
                            </div><!-- end print-this -->
                        </div><!-- end list plant -->
        <?php if ((($key + 1) % 4 == 0) && ($key + 1) != count($plants)) echo "</div><div class='row clear'>"; ?> 
        <?php if (($key + 1) == count($plants)) echo "</div>"; ?>

                <?php } // End foreach ?>
                </div><!-- end plants grid -->
                <!-- Favorite List Actions -->
                <div class="fav-actions no-print">
                    <a href="<?php echo get_permalink(); ?>?screen=print" title="Print" class="green-btn left" target="_blank">Print List</a>
                    <a href="#" onclick="wish_list_email();" title="Email" class="green-btn small left">Email List</a>
                    <a href="<?php echo site_url(); ?>/list-export.php" title="Download as Excel" class="green-btn left" target="_blank">Download</a>
                </div>
<?php } else { ?>
                <p>You currently have no Favorites.  <a href="<?php echo get_permalink(34); ?>" title="Search">Search</a> plants now!</p>
<?php } ?>
            <!-- End Favorites -->
    </section>
<?php get_sidebar('right'); ?>
</div><!-- end content_wrapper -->

<script>
    jQuery(document).ready(function ($) {

        //Bind click of note show/edit control
        $('.fav-note').on('click', function () {
            $(this).hide().siblings('.edit-note').show();
            if ($(this).html() != 'Click to add notes!') {
                $(this).siblings('.edit-note').find('textarea').val($(this).html());
            }
            //Add Focus to text area
            $(this).siblings('.edit-note').find('textarea').focus();
        });

        $('.cancel-note').on('click', function () {
            var favNote = $(this).parents('.list-plant').find('.fav-note');
            var note = favNote.html();
            if (note != 'Click to add notes!') {
                $(this).siblings('textarea').val(note);
            } else {
                $(this).siblings('textarea').val('');
            }
            $(this).parents('.edit-note').hide();
            $(this).parents('.list-plant').find('.fav-note').show();
        });

        // Make the AJAX call Remove or Add notes to the Favorite
        $('.favorite-action').on('click', function () {

            // Set up the variables
            var confirmation = false;
            var process = $(this).attr('data-action');
            var notes = $(this).siblings('textarea').val();
            var value = $(this).attr('data-value');
            //Prevent Accidental deletions
            if (process == 'remove') {
                confirmation = confirm("Are you sure you want to delete this");
            } else if (process == 'update') {
                // If savinf note, only Submit if notes are not empty
                if (notes != '') {
                    confirmation = true;
                }
            }
            else if(process == 'enable'){
                if(value == 1)
                    confirmation = confirm("Are you sure you want to turn on notification");
                else
                    confirmation = confirm("Are you sure you want to turn off notification");
            }

            var plantID = $(this).attr('data-pid');
            var parent = $(this).parents('.list-plant');
            var that = $(this);

            if (confirmation === true) {
                $.post(ajaxurl, {action: 'updateFavorites', ax: process, pid: plantID, note: notes, value: value}, function (data) {
                    data = jQuery.parseJSON(data);
                    var result = data.result;
                    //Swap the button text and data-action values
                    if (data.result == 'Updated') {
                        that.parents('.edit-note').hide();
                        that.parents('.list-plant').find('.fav-note').html(notes).show();
                    } 
                    else if(data.result == 'Enabled'){
                        var enable_send_mail = data.enable_send_mail;
                        
                        if(enable_send_mail == 0){
                            that.attr("data-value", 1);
                            that.find(".fa").attr("title", "Turn on notifications");  
                            that.find(".fa").removeClass("fa-envelope");
                            that.find(".fa").addClass("fa-envelope-o");
                        }
                        else{
                            that.attr("data-value", 0);
                            that.find(".fa").attr("title", "Turn off notifications");
                            that.find(".fa").removeClass("fa-envelope-o");
                            that.find(".fa").addClass("fa-envelope");                        
                        }
                    }
                    else {
                        parent.animate({opacity: 0.0}, 250, function () {
                            $(this).remove()
                        });
                    }
                });
            } else {
                return false;
            }
        });

    });

    function wish_list_email() {
        jQuery.ajax({
            url: '/wish-list-email.php',
            type: 'POST',
            success: function (data) {
                var success = data;
                var message = success == 'success' ? 'We\'re sending your wish list to <b><?php echo $current_user->user_email; ?></b>. Please check your email in a few minutes.<br />Note: Please add <b>website@monrovia.com</b> to your "safe" list to be sure it doesn\'t end up in your junk mail folder.' : 'An error occurred and we were unable to send you your wish list. Please try again later.<br />Note: You can also download your wish list in the form of an <a href="<?php echo site_url(); ?>/list-export.php" title="Download as Excel" target="_blank">Excel spreadsheet</a>.';
                jQuery('.message').css('display', 'block').html(message);

            }
        });
    }
</script>

<?php get_footer(); ?>