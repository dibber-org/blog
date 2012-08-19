<?php get_header(); ?>
	<div id="content">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<div class="post" id="post-<?php the_ID(); ?>">
			<h2 class="title">
				<a href="<?php echo get_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'smartbiz' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_post_thumbnail( array(25,25) );?><?php the_title(); ?></a>
			</h2>
			<div class="entry">
				<?php the_content('<p class="serif">' . __( 'Read the rest of this entry ' ) . '&raquo;</p>');?>
				<div class="navigation" style="clear:both;">
					<?php wp_link_pages(array('before' => '<p><strong>' . __( 'Pages:' ) . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number'));?>
				</div>
				<br />
			</div>
			<div class="meta">
				<p>
						<?php printf( __( 'This entry was written by <a href="%4$s">%5$s</a> the %1$s on %2$s and is filed under %3$s. You can follow any responses to this entry through the <a href="%6$s">feed</a>.', 'smartbiz'),
                                                        get_the_date( get_option( 'date_format' ) ),
                                                        get_the_time( get_option( 'date_format' ) ),
                                                        get_the_category_list(', '),
                                                        esc_attr__(get_author_posts_url($authordata->ID)),
                                                        get_the_author(),
                                                        esc_attr__(get_post_comments_feed_link('RSS 2.0')))?>

						<?php if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
							// Both Comments and Pings are open ?>
                                                        <?php printf( __( 'You can <a href="#respond">leave a comment</a>, or <a href="%s" rel="trackback">trackback</a> from your own site.', 'smartbiz' ), get_trackback_url(true)) ?>

						<?php } elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
							// Only Pings are Open ?>
                                                        <?php printf( __( 'Responses are currently closed, but you can <a href="%s" rel="trackback">trackback</a> from your own site.', 'smartbiz' ), get_trackback_url(true)) ?>

						<?php } elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
							// Comments are open, Pings are not ?>
                                                        <?php _e( 'You can skip to the end and leave a response. Pinging is currently not allowed.', 'smartbiz' ) ?>

						<?php } elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
							// Neither Comments, nor Pings are open ?>
							<?php _e( 'Both comments and pings are currently closed.', 'smartbiz' ) ?>

						<?php } edit_post_link(__ ( 'Edit this entry.', 'smartbiz' ),'',''); ?>
				</p>
			</div>
                        <div>
				<?php comments_template(); ?>
                        </div>
			<div class="navigation" style="clear:both;">
				<div class="alignleft">
					<?php previous_post_link('%link', __( '&laquo; Previous Entries', 'smartbiz' )); ?>
				</div>
				<div class="alignright">
					<?php next_post_link('%link', __( 'Next Entries &raquo;', 'smartbiz' ));?>
				</div>
			</div>
		</div>


	<?php endwhile; else: ?>

		<p><?php _e( 'Sorry, no posts matched your criteria.' ) ?></p>

	<?php endif; ?>

	</div>
	<!-- End content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
