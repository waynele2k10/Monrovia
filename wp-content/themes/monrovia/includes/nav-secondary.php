<?php // Get Current Users info to Print their First Name
if( is_user_logged_in() ){
global $current_user;
get_currentuserinfo();
?>
<span>Welcome, <?php echo $current_user->user_firstname ?>!</span> <a href="<?php echo get_permalink(419); ?>" title="Profile">Your Profile</a>
<a href="<?php echo get_permalink(1133); ?>" title="Profile">Your Favorites</a>
<a href="<?php echo get_permalink(407); ?>" title="Profile">Logout</a>
<?php } else { ?>
<a href="<?php echo get_permalink(406); ?>" title="Sign in / Register">Sign in / Register</a>
<?php  } ?>
<a href="<?php echo get_permalink(343); ?>" title="Retailers and Professionals">Retailers and Professionals</a><br />
<?php get_search_form(); ?>