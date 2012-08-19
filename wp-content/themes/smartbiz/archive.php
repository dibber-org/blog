<?php get_header(); ?>

<div id="content">
	<?php if (have_posts()): ?>
	<div>
		<?php $post = $posts[0]; ?>
		<h2 class="pagetitle">
			<?php if (is_category()): ?>
				<?php printf( __( '&#8216;%s&#8217; Posts', 'smartbiz'), single_cat_title('', false)); ?>
			<?php elseif (is_tag()): ?>
				<?php printf( __( 'Posts tagged &#8216;%s&#8217;', 'smartbiz'), single_tag_title('', false)); ?>
			<?php elseif (is_day()): ?>
				<?php printf( __( 'Archive for %s', 'smartbiz'), get_the_time( 'F jS, Y')); ?>
			<?php elseif (is_month()): ?>
				<?php printf( __( 'Archive for %s', 'smartbiz'), get_the_time( 'F, Y')); ?>
			<?php elseif (is_year()): ?>
				<?php printf( __( 'Archive for %s', 'smartbiz'), get_the_time( 'Y')); ?>
			<?php elseif (is_author()):
				$current_author = get_userdata($author);
				printf( __( 'Posts by %s', 'smartbiz'), $current_author->nickname); ?>
			<?php elseif (isset($_GET['paged']) && !empty($_GET['paged'])): ?>
				<?php _e( 'Blog Archives', 'smartbiz' ) ?>
			<?php endif; ?>
		</h2>
		<div class="entry">&nbsp;</div>
	</div>

	<?php while ( have_posts() ): the_post(); ?>
	<div id="post-<?php the_ID(); ?>" class="post">
		<h2 class="title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'smartbiz' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_post_thumbnail( array(25,25) );?><?php the_title(); ?></a></h2>
			<p class="meta"> <!--a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'smartbiz' ), the_title_attribute( 'echo=0' ) ); ?>" class="more"><?php _e( 'Read full article', 'smartbiz' )?></a>
				</<b>&nbsp;|&nbsp;</b--><?php printf( __( 'The %1$s in %2$s', 'smartbiz' ), get_the_date( get_option( 'date_format' ) ), get_the_category_list(', ') ) ?>
                        	</<b>&nbsp;|&nbsp;</b><?php comments_popup_link(); ?>
			</p>
		<div class="entry">
			<?php the_content(); ?>
		</div>
	</div>
	<?php endwhile; ?>

	<div class="navigation">
		<div class="alignleft">
			<?php previous_posts_link(__( '&laquo; Previous Entries', 'smartbiz' )) ?>
		</div>
		<div class="alignright">
			<?php next_posts_link(__( 'Next Entries &raquo;', 'smartbiz' )) ?>
		</div>
	</div>
	<?php else: ?>
	<div class="post">
		<h2 class="title"><?php _e( 'Not Found', 'smartbiz' ) ?></h2>
	</div>
	<?php endif; ?>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
