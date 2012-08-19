<?php
// Do not delete these lines
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if ( post_password_required() ) {
		if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {
			?>
			<p class="nocomments"><?php e_( 'This post is password protected. Enter the password to view comments.', 'smartbiz' ) ?></p>

	<?php
		return;
		}
	} ?>
<?php //WP hacks start?>
<?php the_tags( '', ', ', '' ); ?>
<?php get_avatar('');?>
<?php paginate_comments_links()?>
<?php wp_list_comments(array(),array())?>
<?php function content_width(){} content_width()?>
<?php add_theme_support('automatic-feed-links')?>
<span class="<?php post_class()?> "></span>
<?php //WP hacks end?>

<!-- You can start editing here. -->
<div id="comments" class="post ">
<?php if ( have_comments() ) : ?>
	<h2 class="title"><?php comments_number();?> to &#8220;<?php the_title(); ?>&#8221;</h2>
	<div class="entry">
	<ol class="commentlist">

		<?php wp_list_comments(); ?>

	</ol>
		</div>

 <?php else : // this is displayed if there are no comments so far ?>

	<?php if ('open' == $post->comment_status) : ?>
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments"><?php _e( 'Comments are closed.', 'smartbiz' ) ?></p>

	<?php endif; ?>
<?php endif; ?>
</div>
<!-- End comments -->

<div class="post">
<?php if ('open' == $post->comment_status) : ?>

		<div class="entry">
			<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
			<p><?php _e( 'You must be ') ?><a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>"><?php _e( 'logged in</a> to post a comment.' ) ?></p>
			<?php else : ?>
				<?php comment_form( array(
					'comment_field'=>'<p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4" style="width: 100%;"></textarea></p>',
					'label_submit'=> __( 'Submit Comment', 'smartbiz' )
				), $post->ID ); ?>
			<?php endif; // If registration is required and not logged in ?>
		</div>
<?php endif; ?>
</div>
<!-- End respond -->
