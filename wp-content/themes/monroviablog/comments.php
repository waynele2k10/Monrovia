<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    echo '<h1>Forbidden</h1>';
    exit();
}
?>

<?php if (comments_open()) : ?>

    <section id="comments" class="well well-sm">

        <?php if (post_password_required()) : ?>
            <p class="nopassword">
                <?php _e('This post is password protected. Enter the password to view any comments.', 'monroviablog'); ?>
            </p>
            <?php
            return;
        endif;
        ?>



        <?php
        // Comment Form
        $aria_req = ( $req ? " aria-required='true'" : '' );
        $fields = array(
            'author' => '<p class="comment-form-author">' . '<label for="author">' . ( $req ? '<span class="required">*</span> ' : '' ) . __('Name', 'monroviablog') . '</label> ',
            '<input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" size="30"' . $aria_req . ' /></p>',
            'email' => '<p class="comment-form-email"><label for="email">' . ( $req ? '<span class="required">*</span> ' : '' ) . __('Email', 'monroviablog') . '</label> ',
            '<input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" size="30"' . $aria_req . ' /></p>'
        );

        $_sigined_link = is_user_logged_in() ? '' : "<a class='comment-signin-link' href='/community/login/'>Login</a>";
        
        if (is_user_logged_in()) {
            $_welcome_user = '<span class="welcome-comment">'.sprintf(__('Hi %1$s', 'monroviablog'), $user_identity).'</span>';
        } else {
            $_welcome_user = '<span class="welcome-comment">'.sprintf(__('<a href="%s">Login/ Register</a>'), wp_login_url(apply_filters('the_permalink', get_permalink($post->ID)))).'</span>';
        }
        $args = array(
            'id_submit' => 'comment-submit',
            'fields' => apply_filters('comment_form_default_fields', $fields),
            'logged_in_as' => '<p class="alert alert-warning logged-in-as">' . sprintf(__('Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'monroviablog'), get_edit_user_link(), $user_identity, wp_logout_url(apply_filters('the_permalink', get_permalink($post->ID)))) . '</p>',
            'title_reply' => __('Leave a Comment'),
            'title_reply_before' => '<div class="row"><div class="col-xs-8"><h3 id="reply-title" class="comment-reply-title">',
            'title_reply_after' => '</h3></div><div class="col-xs-4 comment-user">'.$_welcome_user.'</div></div><p class="comment-note">Monrovia reserves the right to remove comments deemed offensive, vulgar or inappropriate at any time without explanation.</p>',
            'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8"  aria-required="true" required="required"></textarea></p>',
        );

        comment_form($args);
        ?>

        <div class="clearfix"></div>
        <?php
        $ncom = get_comments_number(get_the_ID());
        if ($ncom > 0) :
            echo "<div class='comment-collapse show'> Show $ncom comment(s)</div>";
            if ($ncom >= get_option('comments_per_page') && get_option('page_comments')) :
                ?>
                <nav id="comment-nav-above">
                    <?php paginate_comments_links(); ?>
                </nav>
            <?php endif; ?>

            <div class="commentlist" style="display: none;">
                <?php
                // Comment List
                $args = array(
                    'paged' => true,
                );
                wp_list_comments();
                ?>
            </div>

            <?php if ($ncom >= get_option('comments_per_page') && get_option('page_comments')) : ?>
                <nav id="comment-nav-below">
                    <?php paginate_comments_links(); ?>
                </nav>
            <?php endif; ?>

        <?php endif; ?>

        <div class="clearfix"></div><br />

        <script>
            jQuery('.comment-collapse').click(function () {
                var text = jQuery('.comment-collapse').text();
                if (jQuery('.commentlist').is(":visible")) {
                    jQuery('.comment-collapse').text(text.replace('Hide', 'Show'));
                    jQuery('.commentlist').slideUp(300);
                } else {
                    jQuery('.comment-collapse').text(text.replace('Show', 'Hide'));
                    jQuery('.commentlist').slideDown(300);
                }
            });

        </script>

    </section>

<?php endif; ?>