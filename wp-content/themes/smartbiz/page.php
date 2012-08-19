<?php get_header(); ?>
<div id="content">
	<?php if (have_posts()) :
		while (have_posts()) :
		the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
			<h2 class="title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'smartbiz' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_title(); ?></a></h2>
			<div class="entry">
				<div class="entry-bgtop">
					<div class="entry-bgbtm">
						<?php the_content('<p class="serif">' . __( 'Read the rest of this entry', 'smartbiz' ) . ' &raquo;</p>'); ?><div style="clear:both;"></div>
					</div>
				</div>
				<?php wp_link_pages(array('before' => '<p><strong>' . __( 'Articles:', 'smartbiz' ) . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
			</div>
		</div>
		<br><br>
		<?php comments_template(); ?>
		<?php endwhile;
	endif; ?>
	<div class="navigation">
		<?php edit_post_link(__( 'Edit this entry.', 'smartbiz' ), '<p>', '</p>'); ?>
	</div>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>