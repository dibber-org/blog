<?php get_header(); ?>

	<div id="content">
	<?php if (have_posts()) : the_post();?>
		<div class="post">
			<h1 class="title"><?php _e( 'Entries of', 'smartbiz') ?> &ldquo;<?php the_author_posts_link(); ?>&rdquo;</h1>
		</div>
		<?php if ( get_the_author_meta( 'description' ) ) : ?>
		<div id="entry-author-info">
			<div id="author-avatar">
				<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'twentyten_author_bio_avatar_size', 60 ) ); ?>
			</div>
			<div id="author-description">
				<h2>About <?php echo get_the_author(); ?></h2>
				<?php the_author_meta( 'description' ); ?>
			</div>
		</div>
		<?php endif;
		rewind_posts();
		while (have_posts()) : the_post(); ?>
		<div id="post-<?php the_ID(); ?>" class="post">
			<h1 class="title"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
				<?php the_title(); ?>
				</a></h1>
                        <p class="meta"> <!--a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'smartbiz' ), the_title_attribute( 'echo=0' ) ); ?>" class="more"><?php _e( 'Read full article', 'smartbiz' )?></a>
                            <b>&nbsp;|&nbsp;</b--><?php printf( __( 'The %1$s in %2$s', 'smartbiz' ), get_the_date( get_option( 'date_format' ) ), get_the_category_list(', ') ) ?>
                            <b>&nbsp;|&nbsp;</b><?php comments_popup_link(); ?>
                        </p>
			<div class="entry">
				<?php the_content(); ?>
			</div>
		</div>
		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php previous_posts_link(__( '&laquo; Previous Entries', 'smartbiz' )) ?></div>
			<div class="alignright"><?php next_posts_link(__( 'Next Entries &raquo;', 'smartbiz' )) ?></div>
		</div>

	<?php else: ?>

		<div class="post">
			<h1 class="title"><?php _e( 'Entries of', 'smartbiz' ) ?></h1>
			<p><?php _e( 'No posts found. Try a different search?', 'smartbiz' ) ?></p>
		</div>

	<?php endif; ?>

</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>