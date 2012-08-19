<?php get_header(); ?>

<div id="content">
	<?php if (have_posts()) : ?>
	<?php while (have_posts()):the_post(); ?>
	<div id="post-<?php the_ID(); ?>" class="post">
		<h2 class="title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'smartbiz' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_post_thumbnail( array(25,25) );?><?php the_title(); ?></a></h2>
                    <p class="meta"> <!--a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'smartbiz' ), the_title_attribute( 'echo=0' ) ); ?>" class="more"><?php _e( 'Read full article', 'smartbiz' )?></a>
                        <b>&nbsp;|&nbsp;</b--><?php printf( __( 'The %1$s in %2$s', 'smartbiz' ), get_the_date( get_option( 'date_format' ) ), get_the_category_list(', ') ) ?>
                        <b>&nbsp;|&nbsp;</b><?php comments_popup_link(); ?>
                    </p>
		<div class="entry">
			<?php the_content(); ?>
		</div>
	</div>
	<?php endwhile; ?>
	<?php wp_link_pages(array('before' => '<p><strong>' . __( 'Pages:', 'smartbiz' ) . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
	<div class="navigation">
		<div class="alignleft">
			<?php previous_posts_link(__( '&laquo; Previous Entries', 'smartbiz' )) ?>
		</div>
		<div class="alignright">
			<?php next_posts_link(__( 'Next Entries &raquo;', 'smartbiz' )) ?>
		</div>
	</div>
	<?php else : ?>
	<h2 class="center"><?php _e( 'Not Found', 'smartbiz' ) ?></h2>
	<p class="center"><?php _e( "Sorry, but you are looking for something that isn't here.", 'smartbiz' ) ?></p>
	<?php get_search_form(); ?>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
