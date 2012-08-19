<?php get_header(); ?>

<div id="content">
<?php if ( wp_attachment_is_image() ) :
	$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
	foreach ( $attachments as $k => $attachment ) {
		if ( $attachment->ID == $post->ID )
			break;
	}
	$k++;
	if ( count( $attachments ) > 1 ) {
		if ( isset( $attachments[ $k ] ) )
			$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );
		else
			$next_attachment_url = get_attachment_link( $attachments[ 0 ]->ID );
	} else {
		$next_attachment_url = wp_get_attachment_url();
	}
?>
	<p class="attachment">
		<a href="<?php echo $next_attachment_url; ?>" title="<?php echo esc_attr( get_the_title() ); ?>" rel="attachment"><?php
			$attachment_size=apply_filters( 'smartbiz_attachment_size', 520 );
			echo wp_get_attachment_image( $post->ID, array( $attachment_size, 520 ) );
		?></a>
	</p>
	<div class="navigation">
		<div class="alignleft">
			<?php previous_image_link('%link', __( '&laquo; Previous Entries', 'smartbiz' )); ?>
		</div>
		<div class="alignright">
			<?php next_image_link('%link', __( 'Next Entries &raquo;', 'smartbiz' )); ?>
		</div>
	</div>
<?php else : ?>
	<a href="<?php echo wp_get_attachment_url(); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" rel="attachment"><?php echo basename( get_permalink() ); ?></a>
<?php endif; ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>